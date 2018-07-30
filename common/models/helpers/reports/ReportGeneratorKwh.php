<?php

namespace common\models\helpers\reports;

use common\components\calculators\CopCalculator;
use common\components\calculators\data\SiteMainMetersData;
use common\components\calculators\RateCalculator;
use common\models\AirRates;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\Report;
use common\models\RuleSingleChannel;
use common\models\Site;
use Carbon\Carbon;
use common\models\Tenant;
use yii\helpers\VarDumper;

/**
 * Class ReportGeneratorKwh
 * @package common\models\helpers\reports
 */
class ReportGeneratorKwh extends ReportGenerator implements IReportGenerator
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
     * @var int
     */
    private $report_calculation_type = Report::TENANT_BILL_REPORT_BY_MAIN_METERS;

    /**
     * @var null
     */
    private $first_rule = null;

    /**
     * ReportGeneratorKwh constructor.
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
        $this->tenants = $tenants;
        $this->site = $site;
        foreach ($params as $name => $param) {
            if (property_exists($this, $name)) {
                $this->{$name} = $param;
            }
        }
    }

    /**
     * @return array
     */
    public function calculate()
    {

        $this->setSiteData();

        $this->setElectricalCompanyData();

        foreach ($this->tenants as $tenant) {
            $this->setTenantData($tenant);
            $this->calculateTenant($tenant);
            $this->data['tenants'][$tenant->id]['meter_id'] = $this->getMeterData($tenant);
        }

        $this->calculateDiff();
        $this->calculateDiffPercent();
        return $this->data;
    }

    public function calculateDiff()
    {
        foreach ($this->reading_types as $reading_type) {
            $this->data['diff'][$reading_type.'_consumption'] = $this->data['site_total'][$reading_type.'_consumption'] - $this->data['electrical_company'][$reading_type.'_consumption'];
        }
        $this->data['diff']['consumption_total'] = $this->data['site_total']['consumption_total'] - $this->data['electrical_company']['consumption_total'];
    }

    public function calculateDiffPercent()
    {
        foreach ($this->reading_types as  $reading_type) {
            if ($this->data['electrical_company'][$reading_type.'_consumption'] > 0) {
                $this->data['diff_percent'][$reading_type] = ($this->data['diff'][$reading_type.'_consumption']/$this->data['electrical_company'][$reading_type.'_consumption']) * 100;
            } else {
                $this->data['diff_percent'][$reading_type] = 0;
            }
        }

        if ($this->data['electrical_company']['consumption_total'] > 0) {
            $this->data['diff_percent']['consumption_total'] = ($this->data['diff']['consumption_total']/$this->data['electrical_company']['consumption_total'])*100;
        } else {
            $this->data['diff_percent']['consumption_total'] = 0;
        }
    }

    public function setElectricalCompanyData()
    {
        foreach ($this->reading_types as $reading_type) {
            $this->data['electrical_company'][$reading_type.'_consumption'] = $this->{'electric_company_'.$reading_type} ? $this->{'electric_company_'.$reading_type} : 0;
            $this->data['electrical_company']['consumption_total'] += $this->data['electrical_company'][$reading_type.'_consumption'];
        }
    }

    public function setSiteData()
    {
        $this->data['site'] = $this->site->name;
        $this->data['site_owner'] = $this->site->relationUser->name;
        $this->data['site_total']['geva_consumption'] = 0;
        $this->data['site_total']['pisga_consumption'] = 0;
        $this->data['site_total']['shefel_consumption'] = 0;
        $this->data['site_total']['consumption_total'] = 0;
        $this->data['site_total']['geva_reading'] = 0;
        $this->data['site_total']['shefel_reading'] = 0;
        $this->data['site_total']['pisga_reading'] = 0;
        $this->data['site_total']['reading_total'] = 0;
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
            $this->calculateAirRates($rule, $tenant);
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
        $this->data['tenants'][$tenant->id]['meter_id'] = '';
        $this->data['tenants'][$tenant->id]['geva_reading'] = 0;
        $this->data['tenants'][$tenant->id]['geva_consumption'] = 0;
        $this->data['tenants'][$tenant->id]['pisga_reading'] = 0;
        $this->data['tenants'][$tenant->id]['pisga_consumption'] = 0;
        $this->data['tenants'][$tenant->id]['shefel_reading'] = 0;
        $this->data['tenants'][$tenant->id]['shefel_consumption'] = 0;
        $this->data['tenants'][$tenant->id]['consumption_total'] = 0;
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
                //VarDumper::dump($air_rule_meter_data, 100, true);

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
            $channel = $weighted_channel->getChannel();
            foreach($rates as $rate) {
                $rate_calculator = new RateCalculator($rate, $weighted_channel, $this->from_date, $this->to_date);

                $regular_data = $rate_calculator->calculate($tenant->getRegularTimeRanges(), $cop);

                $irregular_data = $rate_calculator->calculate($tenant->getIrregularHoursTimeRanges(), $cop);

                foreach ($this->data_types as $data_type) {
                    foreach ($this->reading_types as $reading_type) {
                        $this->data['tenants'][$tenant->id][$reading_type.'_reading'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}();
                        $this->data['tenants'][$tenant->id][$reading_type.'_consumption'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}() * $cop;
                        $this->data['tenants'][$tenant->id]['consumption_total'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}() * $cop;

                        $this->data['site_total'][$reading_type.'_consumption'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}() * $cop;
                        $this->data['site_total']['consumption_total'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}() * $cop;
                        if ($channel->is_main) {
                            $this->data['site_total'][$reading_type.'_reading'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}();
                            $this->data['site_total']['reading_total'] += ${$data_type.'_data'}->{'get'.ucfirst($reading_type).'Reading'}();
                        }
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


    public static function generate($report_from_date, $report_to_date, Site $site, $tenants = [], array $params = [])
    {
        // TODO: Implement generate() method.
    }
}