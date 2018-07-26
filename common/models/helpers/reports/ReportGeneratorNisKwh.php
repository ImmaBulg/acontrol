<?php

namespace common\models\helpers\reports;

use Carbon\Carbon;
use common\components\calculators\CopCalculator;
use common\components\calculators\CopTenantCalculatorHourly;
use common\components\calculators\data\SiteMainMetersData;
use common\components\calculators\ElectricalConsumptionCalculator;
use common\components\calculators\RateCalculator;
use common\helpers\TimeManipulator;
use common\models\AirRates;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\Report;
use common\models\RuleFixedLoad;
use common\models\RuleSingleChannel;
use common\models\Site;
use common\models\Tenant;
use yii\helpers\VarDumper;

/**
 * Class ReportGeneratorNisKwh
 * @package common\models\helpers\reports
 */
class ReportGeneratorNisKwh extends ReportGenerator implements IReportGenerator
{

    /**
     * @const string
     */
    const REPORT_DATE_FORMAT = 'd-m-Y';

    /**
     * @const float
     */
    const VAT = 0.17;

    /**
     * @var Carbon
     */
    private $from_date = null;

    /**
     * @var Carbon
     */
    private $to_date = null;

    /**
     * @var Site
     */
    private $site = null;

    /**
     * @var Tenant[]
     */
    private $tenants = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $cop_hourly = [];

    /**
     * @var array
     */
    private $reading_types = [
        'geva',
        'pisga',
        'shefel',
    ];

    /**
     * @var array
     */
    private $data_types = [
        'regular',
        'irregular'
    ];

    /**
     * @var array
     */
    private $site_total = [
        'total_air_geva' => 0,
        'total_air_pisga' => 0,
        'total_air_shefel' => 0,
        'total_pay_geva' => 0,
        'total_pay_pisga' => 0,
        'total_pay_shefel' => 0,
        'total_pay_cn' => 0,
        'total_electricity_geva' => 0,
        'total_electricity_pisga' => 0,
        'total_electricity_shefel' => 0,
        'total_electricity_consumption' => 0,
        'fixed_payment' => 0,
        'total_payment_without_tax' => 0,
        'tax' => 0,
        'total_to_pay' => 0
    ];

    /**
     * @var int
     */
    private $report_calculation_type = Report::TENANT_BILL_REPORT_BY_MANUAL_COP;

    /**
     * @var null
     */
    private $first_rule = null;

    /**
     * @var int
     */
    private $electric_company_price = 0;

    /**
     * @var int
     */
    private $electric_company_shefel = 0;

    /**
     * @var int
     */
    private $electric_company_geva = 0;

    /**
     * @var int
     */
    private $electric_company_pisga = 0;

    /**
     * ReportGeneratorNisKwh constructor.
     * @param Carbon $from_date
     * @param Carbon $to_date
     * @param Site $site
     * @param array $tenants
     * @param array $params
     */
    public function __construct(Carbon $from_date, Carbon $to_date, Site $site, array $tenants, array $params)
    {
        $this->from_date = TimeManipulator::getStartOfDay($from_date);
        $this->to_date = TimeManipulator::getEndOfDay($to_date);
        $this->site = $site;
        $this->tenants = $tenants;
        foreach ($params as $name => $param) {
            if (property_exists($this, $name)) {
                $this->{$name} = $param;
                $this->data[$name] = $param;
            }
        }
    }

    /**
     * @return array
     */
    public function calculate()
    {
        $this->data['site'] = $this->site->name;
        $this->data['site_owner'] = $this->site->relationUser->name;
        $this->data['electric_company_id'] = $this->site->electric_company_id;
        $this->data['report_from'] = $this->from_date->format(self::REPORT_DATE_FORMAT);
        $this->data['report_to'] = $this->to_date->format(self::REPORT_DATE_FORMAT);
        $this->calculateElectricity();
        foreach ($this->tenants as $tenant) {
            $this->cop_hourly[$tenant->id] = (new CopTenantCalculatorHourly(
                $this->from_date,
                $this->to_date
            ))->calculate($tenant);
            $this->setTenantData($tenant);
            $this->calculateTenant($tenant);
        }

        $this->data['site_total'] = $this->getSiteTotal();
        $this->calculateDiff();
        $this->calculateDiffPercent();

        return $this->data;
    }

    public function calculateDiff()
    {
        foreach ($this->reading_types as $reading_type) {
            $this->data['diff'][$reading_type] = $this->data['site_total']['total_air_'.$reading_type] - $this->{'electric_company_'.$reading_type};
        }

        $this->data['diff']['price'] = $this->data['site_total']['total_to_pay'] - $this->electric_company_price;
    }
    
    public function calculateDiffPercent()
    {
        foreach ($this->reading_types as $reading_type) {
            if ($this->data['site_total']['total_air_'.$reading_type] > 0) {
                $this->data['diff_percent'][$reading_type] = ($this->data['diff'][$reading_type]/$this->data['site_total']['total_air_'.$reading_type])*100;
            } else {
                $this->data['diff_percent'][$reading_type] = 0;
            }
        }

        if ($this->data['site_total']['total_to_pay'] > 0) {
            $this->data['diff_percent']['price'] = ($this->data['diff']['price']/$this->data['site_total']['total_to_pay'])*100;
        } else {
            $this->data['diff_percent']['price'] = 0;
        }
    }
    
    /**
     * @return array
     */
    public function getSiteTotal()
    {
        $this->site_total['total_payment_without_tax'] = $this->site_total['total_pay_cn'] + $this->site_total['fixed_payment'];
        $this->site_total['tax'] = $this->site_total['total_payment_without_tax'] * self::VAT;
        $this->site_total['total_to_pay'] = $this->site_total['total_payment_without_tax'] + $this->site_total['tax'];
        return $this->site_total;
    }

    /**
     * @param Tenant $tenant
     */
    public function setTenantData(Tenant $tenant)
    {
        $this->data['tenants'][$tenant->id]['tenant_name'] = $tenant->name;
        $this->data['tenants'][$tenant->id]['tenant_id'] = $tenant->id;
        $this->data['tenants'][$tenant->id]['site_name'] = $this->site->name;
        $this->data['tenants'][$tenant->id]['entrance_date'] = $tenant->getEntranceDateReport($this->from_date)->format(self::REPORT_DATE_FORMAT);
        $this->data['tenants'][$tenant->id]['exit_date'] = $tenant->getExitDateReport($this->to_date)->format(self::REPORT_DATE_FORMAT);
        $this->data['tenants'][$tenant->id]['start_date'] = $this->from_date->format(self::REPORT_DATE_FORMAT);
        $this->data['tenants'][$tenant->id]['end_date'] = $this->to_date->format(self::REPORT_DATE_FORMAT);
        $this->data['tenants'][$tenant->id]['fixed_payment'] = $tenant->getFixedPayment();
        $this->data['tenants'][$tenant->id]['rules_counter'] = 0;
        $this->site_total['fixed_payment'] += $this->data['tenants'][$tenant->id]['fixed_payment'];
    }

    /**
     * @param Tenant $tenant
     */
    public function calculateTenant(Tenant $tenant)
    {
        $single_rules = $tenant->getSingleRules($this->from_date)->all();

        foreach ($single_rules ? $single_rules : [] as $key => $rule) {
            if (!$key) {
                $this->first_rule = $rule;
            }
            $this->data['tenants'][$tenant->id]['rules'][$rule->id] = $this->calculateAirRates($rule, $tenant);
        }

        $this->data['tenants'][$tenant->id]['total'] = $this->calculateTenantTotal($this->data['tenants'][$tenant->id]['rules']);

        $fixed_rules = $tenant->getFixedRules()->all();

        foreach ($fixed_rules ? $fixed_rules: [] as $key => $rule) {
            $this->calculateFixedRules($rule, $tenant);
        }
    }

    public function calculateFixedRules(RuleFixedLoad $rule, Tenant $tenant)
    {
        foreach ($this->data['tenants'][$tenant->id]['rules'] as $index => &$r)
        {
            foreach ($r as $rule_type => &$rul) {
                if ($rule_type == 'regular' || $rule_type == 'irregular') {
                    switch($rule['use_type'])
                    {
                        case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
                            $rul['fixed_rules'] = ($rul['total_pay'] * ((int)$rule['value'] / 100 + 1));
                            break;
                        case RuleFixedLoad::USE_TYPE_MONEY:
                            $rul['fixed_rules'] = ($rul['total_pay'] + (int)$rule['value']);
                            break;
                        case RuleFixedLoad::USE_TYPE_KWH_FIXED:
                            $rate = AirRates::getActiveWithinRangeByTypeId(
                                $this->from_date,
                                $this->to_date,
                                $rule['rate_type_id']
                            )->one();
                            $summ = $rul['geva_reading'] + $rul['pisga_reading'] + $rul['shefel_reading'];
                            $pisga_cof = $rul['pisga_reading'] / $summ;
                            $geva_cof = $rul['geva_reading'] / $summ;
                            $shefel_cof = $rul['shefel_reading'] / $summ;
                            $pisga_value = $rule['value'] * $pisga_cof * $rate['fixed_payment'];
                            $geva_value = $rule['value'] * $geva_cof * $rate['fixed_payment'];
                            $shefel_value = $rule['value'] * $shefel_cof * $rate['fixed_payment'];
                            $rul['fixed_rules'] =  $rul['total_pay'] + $pisga_value + $geva_value + $shefel_value;
                            break;
                        case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
                            $rate = AirRates::getActiveWithinRangeByTypeId(
                                $this->from_date,
                                $this->to_date,
                                $rule['rate_type_id']
                            )->one();
                            $reading_summ = $rul['pisga_reading'] + $rul['geva_reading'] + $rul['shefel_reading'];
                            $rul['fixed_rules'] = $reading_summ * ($rule['value']/100 + 1);
                            break;
                    }
                }
            }
        }
        switch($rule['use_type'])
        {
            case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
                $this->data['tenants'][$tenant->id]['total']['total_fixed_rules'] = ($this->data['tenants'][$tenant->id]['total']['total_pay'] * ((int)$rule['value'] / 100 + 1));
                $this->data['tenants'][$tenant->id]['total']['total_pay'] += ($this->data['tenants'][$tenant->id]['total']['total_pay'] * ((int)$rule['value'] / 100));
                break;
            case RuleFixedLoad::USE_TYPE_MONEY:
                $this->data['tenants'][$tenant->id]['total']['total_fixed_rules'] = (int)$rule['value'];
                $this->data['tenants'][$tenant->id]['total']['total_pay'] += (int)$rule['value'];
                break;
            case RuleFixedLoad::USE_TYPE_KWH_FIXED:
                $rate = AirRates::getActiveWithinRangeByTypeId(
                    $this->from_date,
                    $this->to_date,
                    $rule['rate_type_id']
                )->one();
                $summ = $this->data['tenants'][$tenant->id]['total']['geva_reading'] + $this->data['tenants'][$tenant->id]['total']['pisga_reading'] + $this->data['tenants'][$tenant->id]['total']['shefel_reading'];
                $pisga_cof = $this->data['tenants'][$tenant->id]['total']['pisga_reading'] / $summ;
                $geva_cof = $this->data['tenants'][$tenant->id]['total']['geva_reading'] / $summ;
                $shefel_cof = $this->data['tenants'][$tenant->id]['total']['shefel_reading'] / $summ;
                $pisga_value = $rule['value'] * $pisga_cof * $rate['fixed_payment'];
                $geva_value = $rule['value'] * $geva_cof * $rate['fixed_payment'];
                $shefel_value = $rule['value'] * $shefel_cof * $rate['fixed_payment'];
                $this->data['tenants'][$tenant->id]['total']['total_fixed_rules'] =  $pisga_value + $geva_value + $shefel_value;
                $this->data['tenants'][$tenant->id]['total']['total_pay'] += ($this->data['tenants'][$tenant->id]['total']['total_pay'] + (int)$this->data['tenants'][$tenant->id]['total']['total_fixed_rules']);
                break;
            case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
                $rate = AirRates::getActiveWithinRangeByTypeId(
                    $this->from_date,
                    $this->to_date,
                    $rule['rate_type_id']
                )->one();
                $reading_summ = $this->data['tenants'][$tenant->id]['total']['pisga_reading'] + $this->data['tenants'][$tenant->id]['total']['geva_reading'] + $this->data['tenants'][$tenant->id]['total']['shefel_reading'];
                $this->data['tenants'][$tenant->id]['total']['total_fixed_rules'] = $reading_summ * ($rule['value']/100) + $reading_summ;
                $this->data['tenants'][$tenant->id]['total']['total_pay'] += (int)$this->data['tenants'][$tenant->id]['total']['total_fixed_rules'];
                break;
        }

    }

    /**
     * @param array $rules
     * @return array
     */
    public function calculateTenantTotal(array $rules = [])
    {
        $result = [
            'total_pay' => 0,
            'geva_consumption' => 0,
            'pisga_consumption' => 0,
            'shefel_consumption' => 0,
            'geva_reading' => 0,
            'pisga_reading' => 0,
            'shefel_reading' => 0,
            'geva_pay' => 0,
            'pisga_pay' => 0,
            'shefel_pay' => 0,
        ];

        foreach ($rules as $rule) {
            foreach ($this->data_types as $data_type) {
                if (array_key_exists($data_type, $rule)) {
                    foreach (array_keys($result) as $type) {
                        if (array_key_exists($type, $rule[$data_type])) {
                            $result[$type] += $rule[$data_type][$type];
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param RuleSingleChannel $rule
     * @param Tenant $tenant
     * @return array
     */
    public function calculateAirRates(RuleSingleChannel $rule, Tenant $tenant)
    {
        $result = $this->getRuleDataStructure($rule->relationMeterChannel);

        $weighted_channels = $tenant->getWeightedChannels($rule);
        $rate_type_id = ($tenant->relationTenantBillingSetting->rate_type_id != null) ? $tenant->relationTenantBillingSetting->rate_type_id : $tenant->relationSite->relationSiteBillingSetting->rate_type_id;

        $rates = AirRates::getActiveWithinRangeByTypeId(
            $this->from_date,
            $this->to_date,
            $rate_type_id
        )->all();

        $electricity_main_sub_channels = $tenant->relationSite->getMainSubChannels(Meter::TYPE_ELECTRICITY);

        $air_main_sub_channels = $tenant->relationSite->getMainSubChannels(Meter::TYPE_AIR);

        $site_main_meters_data = new SiteMainMetersData($air_main_sub_channels, $electricity_main_sub_channels);

        switch ($this->report_calculation_type) {
            case Report::TENANT_BILL_REPORT_BY_MANUAL_COP:
                $cop = $tenant->relationSite->manual_cop;
                break;
            case Report::TENANT_BILL_REPORT_BY_FIRST_RULE:
                $air_rule_meter_data = new SiteMainMetersData($tenant->relationSite->getRuleSubChannelsNoMeter($this->first_rule), $electricity_main_sub_channels);
                $cop = (new CopCalculator($air_rule_meter_data, $this->from_date, $this->to_date))->calculate();
                break;
            default:
                $cop = (new CopCalculator($site_main_meters_data, $this->from_date, $this->to_date))->calculate();
                break;
        }

        foreach($weighted_channels as $weighted_channel) {
            /**
             * @var AirRates[] $rates
             */
            foreach($rates as $rate) {
                $rate_calculator = new RateCalculator($rate, $weighted_channel, $this->from_date, $this->to_date);

                $result['fixed_payment'] += $rate->fixed_payment;

                $regular_data = $rate_calculator->calculate($tenant->getRegularTimeRanges(), $cop);

                $irregular_data = $rate_calculator->calculate($tenant->getIrregularHoursTimeRanges(), $cop);

                foreach ($this->data_types as $data_type) {
                    foreach ($this->reading_types as $reading_type) {
                        $result[$data_type][$reading_type.'_reading'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}();
                        $result[$data_type][$reading_type.'_consumption'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}() * $cop;
                        $result[$data_type][$reading_type.'_pay'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Pay'}();
                        $this->site_total['total_air_'.$reading_type] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}() * $cop;
                        $this->site_total['total_pay_'.$reading_type] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Pay'}();
                    }
                }
            }
        }

        foreach ($this->data_types as $data_type) {
            $result[$data_type]['total_pay'] = $this->calculateRuleTotals($result[$data_type]);
            $this->site_total['total_pay_cn'] += $result[$data_type]['total_pay'];
        }

        $this->data['tenants'][$tenant->id]['rules_counter']++;

        return $this->checkIrregularData($result, $tenant);
    }

    /**
     * @param array $data
     * @return float
     */
    public function calculateRuleTotals(array $data)
    {
        return $data['geva_pay'] + $data['pisga_pay'] + $data['shefel_pay'];
    }

    public function calculateElectricity()
    {
        $this->data['electricity_cn'] = [];

        $site_main_meters_data = new SiteMainMetersData(
            $this->site->getMainSubChannels(Meter::TYPE_AIR),
            $this->site->getMainSubChannels(Meter::TYPE_ELECTRICITY)
        );

        $electrical_cn_calculator = new ElectricalConsumptionCalculator(
            $site_main_meters_data,
            $this->from_date,
            $this->to_date
        );

        $electrical_cn_calculator->calculate();

        foreach ($electrical_cn_calculator->getDataTypes() as $type) {
            $this->data['electricity_cn'][$type] = $electrical_cn_calculator->{'get'.ucfirst($type).'Consumption'}();
            $this->site_total['total_electricity_'.$type] = $electrical_cn_calculator->{'get'.ucfirst($type).'Consumption'}();
            $this->site_total['total_electricity_consumption'] += $electrical_cn_calculator->{'get'.ucfirst($type).'Consumption'}();
        }
    }

    /**
     * @param $reading_data
     * @param Tenant $tenant
     * @return array
     */
    public function calculateAirConsumptions($reading_data, Tenant $tenant)
    {
        $result = [
            'geva_reading' => 0,
            'pisga_reading' => 0,
            'shefel_reading' => 0
        ];

        if (array_key_exists($tenant->id, $this->cop_hourly)) {
            foreach ($reading_data as $type => $data) {
                if (array_key_exists($type, $result)) {
                    foreach ($data as $time => $value) {
                        if (array_key_exists($time, $this->cop_hourly[$tenant->id])) {
                            $result[$type] += $value * ($this->cop_hourly[$tenant->id][$time]/100);
                        }
                    }
                }
            }
        }

        return $result;
    }


    /**
     * @param MeterChannel $meter_channel
     * @return array
     */
    public function getRuleDataStructure(MeterChannel $meter_channel)
    {
        return [
            'regular' => [
                'geva_consumption' => 0,
                'pisga_consumption' => 0,
                'shefel_consumption' => 0,
                'geva_reading' => 0,
                'pisga_reading' => 0,
                'shefel_reading' => 0,
                'geva_pay' => 0,
                'pisga_pay' => 0,
                'shefel_pay' => 0,
                'total_pay' => 0,
                'fixed_rules' => 0,
            ],
            'irregular' => [
                'geva_consumption' => 0,
                'pisga_consumption' => 0,
                'shefel_consumption' => 0,
                'geva_reading' => 0,
                'pisga_reading' => 0,
                'shefel_reading' => 0,
                'geva_pay' => 0,
                'pisga_pay' => 0,
                'shefel_pay' => 0,
                'total_pay' => 0,
                'fixed_rules' => 0,
            ],
            'fixed_payment' => 0,
            'meter' => $meter_channel->relationMeter->name,
            'channel' => $meter_channel->channel
        ];
    }

    /**
     * @param $result
     * @param Tenant $tenant
     * @return mixed
     */
    public function checkIrregularData($result, Tenant $tenant)
    {
        $errors = true;

        foreach ($result['irregular'] as $key => $reading) {
            if ($reading > 0) {
                $errors = false;
                break;
            }
        }

        if ($errors) {
            unset($result['irregular']);
        } else {
            $this->data['tenants'][$tenant->id]['rules_counter']++;
        }

        return $result;
    }

    public static function generate($report_from_date, $report_to_date, Site $site, $tenants = [], array $params = [])
    {
        // TODO: Implement generate() method.
    }
}