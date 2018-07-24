<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 09.08.2017
 * Time: 16:10
 */

namespace common\components\calculators;

use Carbon\Carbon;
use common\components\calculators\data\ChannelMultipliers;
use common\components\calculators\data\SiteMainMetersData;
use common\components\calculators\data\WeightedChannel;
use common\components\TimeRange;
use common\models\AirMeterRawData;
use common\models\AirRates;
use common\models\ElectricityMeterRawData;
use common\models\MeterChannelMultiplier;
use common\models\RateType;
use common\models\Site;
use common\models\Tenant;
use yii\db\Query;

class CopCalculator
{
    const GENERAL_COP = 1;
    const CONSUMPTION_COP = 2;

    private $calculation_type;

    /**
     * @var SiteMainMetersData
     */
    private $site_main_meters_data;
    private $from_date;
    private $to_date;

    private $tenant = null;
    private $site = null;

    private $cn_shefel = 0;
    private $cn_pisga = 0;
    private $cn_geva = 0;

    private $electrical_cn_shefel = 1;
    private $electrical_cn_pisga = 1;
    private $electrical_cn_geva = 1;

    /**
     * CopCalculator constructor.
     * @param SiteMainMetersData $site_main_meters_data
     * @param Carbon $from_date
     * @param Carbon $to_date
     * @param int $calculation_type
     * @param Tenant $tenant
     */
    public function __construct(SiteMainMetersData $site_main_meters_data, Carbon $from_date, Carbon $to_date,  int $calculation_type = self::GENERAL_COP, Tenant $tenant = null) {
        $this->from_date = clone $from_date;
        $this->to_date = clone $to_date;
        $this->site_main_meters_data = $site_main_meters_data;
        $this->calculation_type = $calculation_type;
        $this->tenant = $tenant;
        if ($tenant) {
            /** @var Site site */
            $this->site = $tenant->relationSite;
        }
        $this->normalizeDates();
    }


    public function normalizeDates() {
        if($this->to_date->hour == 23 && $this->to_date->minute == 59 && $this->to_date->second == 59) {
            $this->to_date->addDay()->startOfDay();
        }
        else {
            $this->to_date->minute(0)->second(0);
        }
    }


    public function calculate() {
        switch ($this->calculation_type) {
            case self::CONSUMPTION_COP:
                $cop = $this->getCops();
//                echo '<pre>', print_r([$cop], true), '</pre>';die();
                break;
            default:
                $electrical_sum = $this->calculateElectricalSum();
                $air_sum = $this->calculateAirSum();
                $cop = $electrical_sum / $air_sum;
                break;
        }

        return $cop;
    }


    private function calculateAirSum() {
        $base_query =
            (new Query())->select(['kilowatt_hour'])
                         ->from(AirMeterRawData::tableName());
        $date_range_query_pair = new DateRangeQueryPair(
            (clone $base_query)->andWhere(['datetime' => $this->from_date->format('Y-m-d H:i:s')]),
            (clone $base_query)->andWhere(['datetime' => $this->to_date->format('Y-m-d H:i:s')])
        );
        $sum = 0;
        foreach($this->site_main_meters_data->getAirMainChannels() as $air_meter_channel) {
            $consumption = $air_meter_channel->getConsumption($date_range_query_pair);
            $sum += $consumption;
        }
        return $sum;
    }


    public function getDateRangeQueryPair(Query $base_query, $attribute): DateRangeQueryPair {
        return new DateRangeQueryPair(
            (clone $base_query)->andWhere([$attribute => $this->from_date->getTimestamp()]),
            (clone $base_query)->andWhere([$attribute => $this->to_date->getTimestamp()])
        );
    }


    private function calculateElectricalSum() {
        $base_query =
            (new Query())->select(['(reading_shefel + reading_geva + reading_pisga) as cop'])
                         ->from(ElectricityMeterRawData::tableName());
        $date_range_query_pair = new DateRangeQueryPair(
            (clone $base_query)->andWhere(['date' => $this->from_date->getTimestamp()]),
            (clone $base_query)->andWhere(['date' => $this->to_date->getTimestamp()])
        );
        $sum = 0;
        $channels = $this->site_main_meters_data->getElectricalMainChannels();
        foreach($channels as $electrical_main_channel) {
            $consumption = $electrical_main_channel->getConsumption($date_range_query_pair);
            $sum += $consumption;
        }

        if (count($channels) > 0) {
            return $sum/count($channels);
        }

        return $sum;
    }

    private function getCops()
    {
        $this->calculateAirConsumptions();
        $this->calculateElectricalConsumptions();
        return (object)[
            'shefel' => ($this->electrical_cn_shefel/$this->cn_shefel)*100,
            'pisga' => $this->electrical_cn_pisga/$this->cn_pisga,
            'geva' => $this->electrical_cn_geva/$this->cn_geva,
        ];
    }

    private function calculateElectricalConsumptions()
    {
        $electrical_cn = new ElectricalConsumptionCalculator($this->site_main_meters_data, $this->from_date, $this->to_date);

        $this->electrical_cn_geva = $electrical_cn->getGevaConsumption();
        $this->electrical_cn_pisga = $electrical_cn->getPisgaConsumption();
        $this->electrical_cn_shefel = $electrical_cn->getShefelConsumption();
    }

    private function calculateAirConsumptions()
    {
        $channels = $this->site_main_meters_data->getAirMainChannels();

        $rates = AirRates::getActiveWithinRangeByTypeId($this->from_date, $this->to_date,
            $this->site->relationSiteBillingSetting->rate_type_id)
            ->all();
        foreach ($channels as $channel) {
            $air_reading_base_query = (new Query())->select('kilowatt_hour')->from(AirMeterRawData::tableName())
                ->where(['meter_id' => $channel->getMeterName()])
                ->andWhere(['channel_id' => $channel->getChannel()]);
            foreach ($rates as $rate) {
                switch ($rate->rateType->type) {
                    case RateType::TYPE_FIXED:
                    case RateType::TYPE_TAOZ:
                        $queries_generator = new TaozDataQueryGenerator($this->from_date, $this->to_date, $rate->subAirRatesTaoz, $this->tenant->getRegularTimeRanges());
                        $taoz_queries = $queries_generator->generate('datetime', $air_reading_base_query);
                        $taoz_consumption = (new TaozDataCalculator($this->from_date, $this->to_date, $taoz_queries))->calculate(1, 1, 100)->getData();
                        $this->cn_geva += $taoz_consumption->getGevaReading();
                        $this->cn_pisga += $taoz_consumption->getPisgaReading();
                        $this->cn_shefel += $taoz_consumption->getShefelReading();
                        break;
                }
            }
        }
    }

}