<?php

namespace common\components\calculators;

use Carbon\Carbon;
use common\components\calculators\data\RuleData;
use common\components\calculators\data\SiteMainMetersData;
use common\components\calculators\data\TenantData;
use common\exceptions\FormReportValidationContinueException;
use common\helpers\TimeManipulator;
use common\models\AirRates;
use common\models\helpers\reports\ReportGenerator;
use common\models\Meter;
use common\models\Rate;
use common\models\Report;
use common\models\RuleSingleChannel;
use common\models\Site;
use common\models\Tenant;
use Yii;
use yii\helpers\VarDumper;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 24.07.2017
 * Time: 15:05
 */
class RuleCalculator
{
    /**
     * @var RuleSingleChannel
     */
    private $rule = null;

    private $from_date = null;
    private $to_date = null;

    public $first_rule = null;

    /**
     * TenantCalculator constructor.
     * @param RuleSingleChannel $rule
     * @param Carbon $from_date
     * @param Carbon $to_date
     * @param RuleSingleChannel $first_rule
     */
    public function __construct(RuleSingleChannel $rule, Carbon $from_date, Carbon $to_date, RuleSingleChannel $first_rule) {
        $this->rule = $rule;
        $this->from_date = clone $from_date;
        $this->to_date = clone $to_date;
        $this->first_rule = $first_rule;
    }


    public function calculate(Tenant $tenant, $report_type, $cops = null) {
        $rule_data = new RuleData($this->from_date, $this->to_date, $this->rule, $tenant->getRegularTimeString(),
                                  $tenant->getIrregularTimeString());
        $electricity_main_sub_channels = $tenant->relationSite->getMainSubChannels(Meter::TYPE_ELECTRICITY);

        if ($report_type == 2) {
            $air_main_sub_channels = $tenant->relationSite->getRuleSubChannelsNoMeter($this->first_rule);
        }
        else {
            $air_main_sub_channels = $tenant->relationSite->getMainSubChannels(Meter::TYPE_AIR);
        }


        $site_main_meters_data = new SiteMainMetersData($air_main_sub_channels, $electricity_main_sub_channels);

        /**
         * @var AirRates[] $rates
         */
        $rate_type_id = ($tenant->relationTenantBillingSetting->rate_type_id != null) ? $tenant->relationTenantBillingSetting->rate_type_id : $tenant->relationSite->relationSiteBillingSetting->rate_type_id;
        $rates = AirRates::getActiveWithinRangeByTypeId($this->from_date, $this->to_date,
            $rate_type_id)
            ->all();
        switch ($report_type) {
            case Report::TENANT_BILL_REPORT_BY_MANUAL_COP:
                $cop = $tenant->relationSite->manual_cop;
                $consumption_cop = (object) [
                    'shefel' => $tenant->relationSite->manual_cop_shefel,
                    'pisga' => $tenant->relationSite->manual_cop_pisga,
                    'geva' => $tenant->relationSite->manual_cop_geva
                ];
                break;
            case Report::TENANT_BILL_REPORT_BY_FIRST_RULE:
                $air_rule_meter_data = new SiteMainMetersData($tenant->relationSite->getRuleSubChannels($this->first_rule), $electricity_main_sub_channels);
                $cop = (new CopCalculator($air_rule_meter_data, $this->from_date, $this->to_date))->calculate();
                $consumption_cop = (new CopCalculator($site_main_meters_data, $this->from_date, $this->to_date, CopCalculator::CONSUMPTION_COP, $tenant))->calculate();
                break;
            default:
                $cop = (new CopCalculator($site_main_meters_data, $this->from_date, $this->to_date))->calculate();
                $consumption_cop = (new CopCalculator($site_main_meters_data, $this->from_date, $this->to_date, CopCalculator::CONSUMPTION_COP, $tenant))->calculate();
                break;
        }


        $rule_data->setCoops($consumption_cop);
        $rule_data->setCop($cop);

        $weighted_channels = $tenant->getWeightedChannels($this->rule);

        foreach($weighted_channels as $weighted_channel) {

            foreach($rates as $rate) {
                $rate_calculator = new RateCalculator($rate, $weighted_channel, $this->from_date, $this->to_date);
                $rule_data->addRegularData($rate_calculator->calculate($tenant->getRegularTimeRanges(),$rule_data->getCop()));
//                $rule_data->addIrregularData($rate_calculator->calculate($tenant->getIrregularTimeRanges(),$rule_data->getCop()));
                $rule_data->addIrregularData($rate_calculator->calculate($tenant->getIrregularHoursTimeRanges(),$rule_data->getCop()));
                if ($tenant->getFixedPayment()) {
                    $rule_data->setFixedPrice($tenant->getFixedPayment());
                } else if ($tenant->relationSite->relationSiteBillingSetting->fixed_payment) {
                    $rule_data->setFixedPrice($tenant->relationSite->relationSiteBillingSetting->fixed_payment);
                } else {
                    $rule_data->setFixedPrice($rate->fixed_payment);
                }
            }
        }
        return $rule_data;
    }


}