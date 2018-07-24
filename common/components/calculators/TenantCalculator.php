<?php

namespace common\components\calculators;

use Carbon\Carbon;
use common\components\calculators\data\SiteMainMetersData;
use common\components\calculators\data\TenantData;
use common\exceptions\FormReportValidationContinueException;
use common\helpers\TimeManipulator;
use common\models\AirRates;
use common\models\helpers\reports\ReportGenerator;
use common\models\Meter;
use common\models\Rate;
use common\models\RuleSingleChannel;
use common\models\Tenant;
use Yii;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 24.07.2017
 * Time: 15:05
 */
class TenantCalculator {
  /**
   * @var Tenant
   */
  private $tenant = null;

  private $from_date = null;
  private $to_date = null;


  /**
   * TenantCalculator constructor.
   *
   * @param Tenant $tenant
   * @param Carbon $from_date
   * @param Carbon $to_date
   */
  public function __construct( Tenant $tenant, Carbon $from_date, Carbon $to_date ) {
    $this->tenant    = $tenant;
    $this->from_date = clone $from_date;
    $this->to_date   = clone $to_date;
    $this->normalizeReportRange();
  }


  public function normalizeReportRange() {
    $entrance_date = $this->tenant->entrance_date;
    $exit_date     = $this->tenant->exit_date;
    if ( $entrance_date ) {
      $entrance_date = TimeManipulator::getStartOfDay( $entrance_date );
      if ( $entrance_date > $this->to_date ) {
        throw new FormReportValidationContinueException( 'Tenant will enter later than last report day!' );
      } else {
        if ( $entrance_date > $this->from_date ) {
          $this->from_date = $entrance_date;
        }
      }
    }
    if ( $exit_date ) {
      $exit_date = TimeManipulator::getEndOfDay( $exit_date );
      if ( $exit_date < $this->from_date ) {
        throw new FormReportValidationContinueException( 'Tenant will leave us earlier than first report day!' );
      } else {
        if ( $exit_date < $this->to_date ) {
          $this->to_date = $exit_date;
        }
      }
    }
  }


  public function calculate($report_type) {
    $tenant_data = new TenantData( $this->from_date, $this->to_date, $this->tenant );
    $rules       = $this->getRules();
    foreach ( $rules as $rule ) {
      $cop_calculator = new CopTenantCalculatorHourly( $this->from_date, $this->to_date );
      $rule_calculator = new RuleCalculator( $rule, $this->from_date, $this->to_date, $rules[0]);
      $rule_data = $rule_calculator->calculate( $this->tenant, $report_type);
      $tenant_data->add( $rule_data );

      $tenant_data->setHourlyCop( $cop_calculator->calculate( $this->tenant ), $rule_data->reading_data );
      $tenant_data->setYearly( YearlyCalculator::instance( $this->from_date, $this->to_date, $this->tenant )
                                               ->calculate() );
    }
    return $tenant_data;
  }


  /**
   * @return RuleSingleChannel[]
   */
  private function getRules() {
    $rules_query = $this->tenant->getSingleRules( $this->from_date );
    $rules       = Yii::$app->db->cache( function () use ( $rules_query ) {
      return $rules_query->all();
    }, ReportGenerator::CACHE_DURATION );

    return $rules;
  }
}