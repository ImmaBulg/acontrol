<?php

namespace common\components\calculators;

use common\components\calculators\data\MainMetersData;
use yii\db\Query;
use Carbon\Carbon;
use common\models\ElectricityMeterRawData;
use common\components\calculators\data\SiteMainMetersData;

class ElectricalConsumptionCalculator
{

    const TYPE_GEVA = 'geva';
    const TYPE_PISGA = 'pisga';
    const TYPE_SHEFEL = 'shefel';

    private $pisga_consumption = 0;
    private $geva_consumption = 0;
    private $shefel_consumption = 0;
    private $to_date;
    private $from_date;
    private $site_main_meter_data;
    private $number_of_channels = 1;

    public static function getDataTypes()
    {
        return [
            self::TYPE_GEVA,
            self::TYPE_PISGA,
            self::TYPE_SHEFEL
        ];

    }

    public function __construct(SiteMainMetersData $site_main_meters_data, Carbon $from_date, Carbon $to_date)
    {
        $this->site_main_meter_data = $site_main_meters_data;
        $this->from_date = clone $from_date;
        $this->to_date = clone $to_date;
        $this->normalizeDates();
        $this->calculate();
    }

    public function getGevaConsumption()
    {
        return $this->geva_consumption/$this->number_of_channels;
    }

    public function getPisgaConsumption()
    {
        return $this->pisga_consumption/$this->number_of_channels;
    }

    public function getShefelConsumption()
    {
        return $this->shefel_consumption/$this->number_of_channels;
    }

    public function normalizeDates()
    {
        if ($this->to_date->hour == 23 && $this->to_date->minute == 59 && $this->to_date->second == 59) {
            $this->to_date->addDay()->startOfDay();
        } else {
            $this->to_date->minute(0)->second(0);
        }
    }

    public function calculate()
    {
        $channels = $this->site_main_meter_data->getElectricalMainChannels();

        foreach ($channels as $channel) {
            foreach (self::getDataTypes() as $type) {
                $this->getDataByType($type, $channel);
            }
        }

        $this->number_of_channels = count($channels) > 0 ? count($channels) : $this->number_of_channels;
    }

    private function getDataByType(string $type, $channel)
    {

        $query = (new Query())->select($type . ' as ' . $type)->from(ElectricityMeterRawData::tableName());

        $override_data_end = (clone $query)
                ->andWhere(['date' => $this->to_date->getTimestamp()])
                ->andWhere(['meter_id' => $channel->getMeterName()])
                ->andWhere(['channel_id' => $channel->getChannel()])
                ->scalar();

        $override_data_start = (clone $query)
                ->andWhere(['date' => $this->from_date->getTimestamp()])
                ->andWhere([ 'meter_id' => $channel->getMeterName()])
                ->andWhere([ 'channel_id' => $channel->getChannel()])
                ->scalar();

        if ($override_data_end && false) {
            $this->{$type . '_consumption'} += ($override_data_end - $override_data_start)*16;
        } else {
            $query = (new Query())->select(['reading_' . $type . ' as ' . $type ])
                ->from(ElectricityMeterRawData::tableName());

            $date_range_query_pair = new DateRangeQueryPair(
                (clone $query)->andWhere(['date' => $this->from_date->getTimestamp()]),
                (clone $query)->andWhere(['date' => $this->to_date->getTimestamp()])
            );

            $this->{$type . '_consumption'} += $channel->getConsumption($date_range_query_pair);
        }

    }
}
