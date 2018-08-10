<?php

namespace common\components\calculators\data;

use Carbon\Carbon;
use common\models\AirRates;
use common\models\RuleFixedLoad;
use common\models\Tenant;
use yii\helpers\VarDumper;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 21.07.2017
 * Time: 16:08
 */
class TenantData extends TaozRawData {
  const VAT_PERCENT = 17;
  private $yearly = null;
  private $fixed_price = 0;
  private $hourly_cop = null;

  private $money_addition = 0;


  /**
   * @return int
   */
  public function getFixedPrice(): float {
    return $this->fixed_price;
  }


  public function __construct( Carbon $start_date, Carbon $end_date, Tenant $tenant ) {
    parent::__construct( $start_date, $end_date );
    $this->tenant = $tenant;
  }


  /**
   * @return Tenant
   */
  public function getTenant(): Tenant {
    return $this->tenant;
  }


  /**
   * @var RuleData[]
   */
  private $rule_data = [];


  /**
   * @return RuleData[]
   */
  public function getRuleData(): array {
    return $this->rule_data;
  }


  /**
   * @var Tenant
   */
  private $tenant = null;


  /**
   * @return float
   */
  public function getPisgaPay(): float {
    return $this->pisga_pay;
  }


  /**
   * @return float
   */
  public function getShefelPay(): float {
    return $this->shefel_pay;
  }


  /**
   * @return float
   */
  public function getGevaPay(): float {
    return $this->geva_pay;
  }


  /**
   * @var float
   */
  private $pisga_pay = 0;
  /**
   * @var float
   */
  private $geva_pay = 0;
  /**
   * @var float
   */
  private $shefel_pay = 0;

  private $reading_data = array();

  protected $cop = 1;

  public function add( RuleData $data ) {
    $this->rule_data[]        = $data;
    $this->pisga_reading += $data->getPisgaReading();
    $this->pisga_consumption  += $data->getPisgaConsumption();
    $this->geva_reading  += $data->getGevaReading();
    $this->geva_consumption   += $data->getGevaConsumption();
    $this->shefel_reading += $data->getShefelReading();
    $this->shefel_consumption += $data->getShefelConsumption();
    $this->pisga_pay          += $data->getPisgaPay();
    $this->geva_pay           += $data->getGevaPay();
    $this->shefel_pay         += $data->getShefelPay();
    $this->fixed_price        = $data->getFixedPrice();
    $this->cop = $data->getCop();
    $this->reading_data = $data->reading_data;
  }

  public function getAirPisgaConsumption()
  {
    return $this->pisga_consumption * $this->cop;
  }

  public function getAirGevaConsumption()
  {
    return $this->geva_consumption * $this->cop;
  }

  public function getAirShefelConsumption()
  {
    return $this->shefel_consumption * $this->cop;
  }

  public function getTotalConsumption(): float {
    return $this->pisga_consumption + $this->geva_consumption + $this->shefel_consumption;
  }


  public function getTotalPay(): float {
    return $this->pisga_pay + $this->geva_pay + $this->shefel_pay;
  }


  public function getTotalPayWithFixed() {
    return $this->getTotalPay() + $this->fixed_price;
  }


  public function getVat() {
    return $this->getTotalPayWithFixed() * self::VAT_PERCENT / 100;
  }


  public function getTotalPayWithVat() {
    return $this->getTotalPayWithFixed() + $this->getVat();
  }


  /**
   * @return YearlyData
   */
  public function getYearly() {
    return $this->yearly;
  }


  public function setYearly( YearlyData $yearly_data ) {
    $this->yearly = $yearly_data;
  }

  public function setHourlyCop( array $hourly_cop, array $reading_data) {
    $this->hourly_cop = $hourly_cop;
    /*$shefel = 0;
    $geva = 0;
    $pisga = 0;
    foreach ($reading_data as $type => $data) {
        foreach ($reading_data[$type] as $time => $type_data) {
            if (isset(${$type})) {
                if (array_key_exists($time, $this->hourly_cop)) {
                    ${$type} += $type_data * ($this->hourly_cop[$time]/100);
                }
            }
        }
    }*/
//    echo '<pre>', print_r(['shefel' => $shefel, 'geva' => $geva, 'pisga' => $pisga, 'general' =>$reading_data ], true), '</pre>';
  }

  public function calculateFixedRules() {
      $fixed_rules = $this->tenant->getFixedRules()->all();
      $tenant = $this->tenant;
      foreach ($this->rule_data as &$rule_data) {
          $money_addition = 0;
          foreach ($rule_data->getData() as $type => &$data) {
              $single_rule = $data[0]->getPisgaPay() + $data[0]->getShefelPay() + $data[0]->getGevaPay();
              $reading_summ = $data[0]->getPisgaConsumption() * $this->cop + $data[0]->getGevaConsumption() * $this->cop + $data[0]->getShefelConsumption() * $this->cop;
              if ($reading_summ != 0)
                  foreach ($fixed_rules as $rule) {
                      switch($rule['use_type'])
                      {
                          case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
                              $fixed_rule = ($single_rule * ((int)$rule['value'] / 100 + 1));
                              $data[0]->setPisgaFixedRule($data[0]->getPisgaPay() * ((int)$rule['value'] / 100));
                              $data[0]->setGevaFixedRule($data[0]->getGevaPay() * ((int)$rule['value'] / 100));
                              $data[0]->setShefelFixedRule($data[0]->getShefelPay() * ((int)$rule['value'] / 100));
                              $data[0]->setFixedRule($single_rule * ((int)$rule['value'] / 100));
                              $money_addition = $fixed_rule;
                              break;
                          case RuleFixedLoad::USE_TYPE_MONEY:
                              $data[0]->setFixedRule((int)$rule['value']);
                              $money_addition = (int)$rule['value'];
                              break;
                          case RuleFixedLoad::USE_TYPE_KWH_FIXED:
                              if ($rule['rate_type_id'] == 12) {
                                  $rate = AirRates::getActiveWithinRangeByTypeId(
                                      $this->start_date,
                                      $this->end_date,
                                      $tenant->getRateType()
                                  )->one();
                              }
                              else {
                                  $rate = AirRates::getActiveWithinRangeByTypeId(
                                      $this->start_date,
                                      $this->end_date,
                                      $rule['rate_type_id']
                                  )->one();
                              }
                              $pisga_cof = $data[0]->getPisgaConsumption() * $this->cop / $reading_summ;
                              $geva_cof = $data[0]->getGevaConsumption() * $this->cop / $reading_summ;
                              $shefel_cof = $data[0]->getShefelConsumption() * $this->cop / $reading_summ;
                              $pisga_value = $rule['value'] * $pisga_cof * $rate['fixed_payment'];
                              $geva_value = $rule['value'] * $geva_cof * $rate['fixed_payment'];
                              $shefel_value = $rule['value'] * $shefel_cof * $rate['fixed_payment'];
                              $data[0]->setPisgaFixedRule($pisga_value);
                              $data[0]->setShefelFixedRule($shefel_value);
                              $data[0]->setGevaFixedRule($geva_value);
                              $data[0]->setFixedRule($pisga_value + $geva_value + $shefel_value);
                              $money_addition = $pisga_value + $geva_value + $shefel_value;
                              break;
                          case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
                              $data[0]->setPisgaFixedRule($data[0]->getPisgaConsumption() * $this->cop * ($rule['value']/100) * $data[0]->getPisgaPrice() / 100);
                              $data[0]->setGevaFixedRule($data[0]->getGevaConsumption() * $this->cop * ($rule['value']/100) * $data[0]->getGevaPrice() / 100);
                              $data[0]->setShefelFixedRule($data[0]->getShefelConsumption() * $this->cop * ($rule['value']/100) * $data[0]->getShefelPrice() / 100);
                              $data[0]->setFixedRule($data[0]->getPisgaFixedRule() + $data[0]->getGevaFixedRule() + $data[0]->getShefelFixedRule());
                              $money_addition = $reading_summ * ($rule['value']/100);
                              break;
                      }
                  }
          }
          if (!is_nan($money_addition)) {
              $this->money_addition += $money_addition;
          }

      }
  }

    public function setMoneyAddition($money_addition) : float {
        $this->money_addition = $money_addition;
    }

    public function getMoneyAddition() : float {
        return $this->money_addition;
    }

}