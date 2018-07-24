<?php namespace common\models;

/**
 * Interface IMeterRawData
 * @package common\models
 */
interface IMeterRawData
{
     public static function getSeason($date);
     public static function getListSeasonAvgConstants();
     public static function getRulePeriodRange($rule, $from_date, $to_date);
     public static function getAvgData($meter_id, $channel_id, $period_from, $period_to, $from_date, $to_date, $with_season = false);
     public static function getReadings($meter_name, $meter_channel_name, $date, $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT);
     public static function getPowerFactor($meter_name, array $meter_channel_names, $from_date, $to_date, $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT);
     public static function getPowerFactorAdditionalPercent($power_factor, $rate_level = RateType::LEVEL_LOW);

}