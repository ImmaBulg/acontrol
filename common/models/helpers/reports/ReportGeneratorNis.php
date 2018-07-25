<?php

namespace common\models\helpers\reports;

use common\components\calculators\CopCalculator;
use common\components\calculators\data\SiteMainMetersData;
use common\components\calculators\RateCalculator;
use common\models\AirRates;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\Report;
use common\models\RuleFixedLoad;
use common\models\RuleSingleChannel;
use common\models\Site;
use Carbon\Carbon;
use common\models\Tenant;
use yii\helpers\VarDumper;
use yii\rbac\Rule;

/**
 * Class ReportGeneratorNis
 * @package common\models\helpers\reports
 */
class ReportGeneratorNis extends ReportGenerator implements IReportGenerator
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
     * @var bool
     */
    private $is_vat_included = false;

    /**
     * @var bool
     */
    private $column_fixed_payment = false;

    /**
     * @var bool
     */
    private $column_total_pay_single_channel_rules = false;

    /**
     * @var bool
     */
    private $column_total_pay_group_load_rules = false;

    /**
     * @var bool
     */
    private $column_total_pay_fixed_load_rules = false;

    /**
     * @var int
     */
    private $report_calculation_type = Report::TENANT_BILL_REPORT_BY_MAIN_METERS;

    /**
     * @var null
     */
    private $first_rule = null;

    /**
     * @var int
     */
    private $electric_company_price = 0;

    /**
     * ReportGeneratorNis constructor.
     * @param Carbon $from_date
     * @param Carbon $to_date
     * @param Site $site
     * @param array $tenants
     * @param array $params
     */
    public function __construct(Carbon $from_date, Carbon $to_date, Site $site, array $tenants, array $params)
    {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->site = $site;
        $this->tenants = $tenants;
        foreach ($params as $name => $param) {
            if (property_exists($this, $name)) {
                $this->{$name} = $param;
                $this->data['params'][$name] = $param;
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
        $this->data['site_fixed_payment'] = 0;
        $this->data['site_total'] = 0;

        foreach ($this->tenants as $tenant) {
            $this->setTenantData($tenant);
            $this->calculateTenant($tenant);
        }

        $this->data['site_total'] += $this->data['site_fixed_payment'];
        $this->data['site_total_vat'] = $this->calculateVAT($this->data['site_total']);
        $this->data['site_total_vat_incl'] = $this->data['site_total_vat'] + $this->data['site_total'];

        $this->calculateDiff();
        $this->calculateDiffPercent();

        return $this->data;
    }

    public function calculateDiff()
    {
        $this->data['site_electrical_company_price'] = $this->electric_company_price;
        $this->data['diff'] = $this->data['site_total_vat_incl'] - $this->electric_company_price;
    }

    public function calculateDiffPercent()
    {
        if ($this->data['site_total_vat_incl'] > 0) {
            $this->data['diff_percent'] = ($this->data['diff']/$this->data['site_total_vat_incl'])*100;
        } else{
            $this->data['diff_percent'] = 0;
        }
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
        $this->data['tenants'][$tenant->id]['meter_id'] = '';
        $this->data['tenants'][$tenant->id]['total_single_rules'] = 0;
        $this->data['tenants'][$tenant->id]['total_group_rules'] = 0;
        $this->data['tenants'][$tenant->id]['total_fixed_rules'] = 0;
        $this->data['tenants'][$tenant->id]['vat'] = 0;
        $this->data['tenants'][$tenant->id]['total'] = 0;
        $this->data['tenants'][$tenant->id]['total_vat_incl'] = 0;
    }

    /**
     * @param Tenant $tenant
     */
    public function calculateTenant(Tenant $tenant)
    {
        $fixed_payment = $tenant->getFixedPayment();

        if ($this->column_total_pay_single_channel_rules) {
            $single_rules = $tenant->getSingleRules($this->from_date)->all();

            foreach ($single_rules ? $single_rules : [] as $key => $rule) {
                if (!$key) {
                    $this->first_rule = $rule;
                }
                $this->calculateAirRates($rule, $tenant);
            }
        }
        if ($this->column_total_pay_fixed_load_rules) {
            $fixed_rules = $tenant->getFixedRules()->all();

            foreach ($fixed_rules ? $fixed_rules: [] as $key => $rule) {
                $this->calculateFixedRules($rule, $tenant);
            }
        }

        //VarDumper::dump($fixed_rules, 100, true);
        //VarDumper::dump((intval($this->data['tenants'][$tenant->id]['total_single_rules']) * (intval($fixed_rules[0]['value']) / 100 + 1)), 100, true);
        $this->data['site_fixed_payment'] += $fixed_payment;
        $this->data['tenants'][$tenant->id]['fixed_payment'] = $fixed_payment;
        $this->data['tenants'][$tenant->id]['total'] += $fixed_payment;
        $this->data['tenants'][$tenant->id]['vat'] = $this->calculateVAT($this->data['tenants'][$tenant->id]['total']);
        $this->data['tenants'][$tenant->id]['total_vat_incl'] += $this->data['tenants'][$tenant->id]['vat'] + $this->data['tenants'][$tenant->id]['total'];
        $this->data['tenants'][$tenant->id]['meter_id'] = $this->getMeterData($tenant);
    }

    public function calculateFixedRules(RuleFixedLoad $rule, Tenant $tenant)
    {
        //VarDumper::dump($rule, 100, true);
        switch($rule['use_type'])
        {
            case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
                $this->data['tenants'][$tenant->id]['total_fixed_rules'] = ($this->data['tenants'][$tenant->id]['total_single_rules'] * ((int)$rule['value'] / 100 + 1));
                $this->data['tenants'][$tenant->id]['total_pay'] += ($this->data['tenants'][$tenant->id]['total']['total_pay'] * ((int)$rule['value'] / 100));
                break;
            case RuleFixedLoad::USE_TYPE_MONEY:
                $this->data['tenants'][$tenant->id]['total_fixed_rules'] = ($this->data['tenants'][$tenant->id]['total_single_rules'] + (int)$rule['value']);
                $this->data['tenants'][$tenant->id]['total_pay'] += (int)$rule['value'];
                break;
            case RuleFixedLoad::USE_TYPE_KWH_FIXED:
                $rate = AirRates::getActiveWithinRangeByTypeId(
                    $this->from_date,
                    $this->to_date,
                    $rule['rate_type_id']
                )->one();
                $summ = $this->data['tenants'][$tenant->id]['total_Geva'] + $this->data['tenants'][$tenant->id]['total_Pisga'] + $this->data['tenants'][$tenant->id]['shefel_reading'];
                $pisga_cof = $this->data['tenants'][$tenant->id]['total_Pisga'] / $summ;
                $geva_cof = $this->data['tenants'][$tenant->id]['total_Geva'] / $summ;
                $shefel_cof = $this->data['tenants'][$tenant->id]['total_Shefel'] / $summ;
                $pisga_value = $rule['value'] * $pisga_cof * $rate['fixed_payment'];
                $geva_value = $rule['value'] * $geva_cof * $rate['fixed_payment'];
                $shefel_value = $rule['value'] * $shefel_cof * $rate['fixed_payment'];
                $this->data['tenants'][$tenant->id]['total_fixed_rules'] = $this->data['tenants'][$tenant->id]['total_pay'] + $pisga_value + $geva_value + $shefel_value;
                $this->data['tenants'][$tenant->id]['total_pay'] += (int)$this->data['tenants'][$tenant->id]['total_fixed_rules'];
                break;
            case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
                $rate = AirRates::getActiveWithinRangeByTypeId(
                    $this->from_date,
                    $this->to_date,
                    $rule['rate_type_id']
                )->one();
                $reading_summ = $this->data['tenants'][$tenant->id]['total_Pisga'] + $this->data['tenants'][$tenant->id]['total']['total_Geva'] + $this->data['tenants'][$tenant->id]['total_Shefel'];
                $this->data['tenants'][$tenant->id]['total_fixed_rules'] = $reading_summ * ($rule['value']/100 + 1);
                $this->data['tenants'][$tenant->id]['total_pay'] += (int)$this->data['tenants'][$tenant->id]['total_fixed_rules'];
                break;
        }
    }

    /**
     * @param RuleSingleChannel $rule
     * @param Tenant $tenant
     */
    public function calculateAirRates(RuleSingleChannel $rule, Tenant $tenant)
    {
        $weighted_channels = $tenant->getWeightedChannels($rule);

        $this->setMeterData($rule->relationMeterChannel, $tenant);
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

                $regular_data = $rate_calculator->calculate($tenant->getRegularTimeRanges(), $cop);

                $irregular_data = $rate_calculator->calculate($tenant->getIrregularHoursTimeRanges(), $cop);

                foreach ($this->data_types as $data_type) {
                    foreach ($this->reading_types as $reading_type) {
                        $this->data['tenants'][$tenant->id]['total_single_rules'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Pay'}();
                        $this->data['tenants'][$tenant->id]['total_'.ucfirst($reading_type)] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}();
                        $this->data['tenants'][$tenant->id]['total'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Pay'}();
                        $this->data['site_total'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Pay'}();
                    }
                }
            }
        }
    }

    /**
     * @param MeterChannel $meter_channel
     * @param Tenant $tenant
     */
    public function setMeterData(MeterChannel $meter_channel, Tenant $tenant)
    {
        $this->data['tenants'][$tenant->id]['meter_id'] .= $meter_channel->channel . ' - ' . $meter_channel->relationMeter->name . ', ';
    }

    /**
     * @param Tenant $tenant
     * @return mixed
     */
    public function getMeterData(Tenant $tenant)
    {
        return rtrim(trim($this->data['tenants'][$tenant->id]['meter_id']), ',');
    }

    public function calculateVAT(float $value)
    {
        return $this->is_vat_included ? $value * self::VAT : 0;
    }

    public static function generate($report_from_date, $report_to_date, Site $site, $tenants = [], array $params = [])
    {
        // TODO: Implement generate() method.
    }
}