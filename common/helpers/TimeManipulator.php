<?php

namespace common\helpers;

use Carbon\Carbon;
use common\components\i18n\Formatter;
use DateTime;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 21.07.2017
 * Time: 18:06
 */
class TimeManipulator
{
    const SITE_DATE_FORMAT = 'd-m-Y';


    /**
     * Takes mixed value. Depending on type of the value behaves differently :
     * 1) If values is Carbon instance - simply return it
     * 2) If value is formatted string - create from formatted string
     * In case of anything bad tries to create from timestamp
     * @param $value
     * @return Carbon
     */
    public static function getDateInstance($value) {
        if($value instanceof Carbon) {
            return $value;
        }
        try {
            $date = Carbon::createFromFormat(Formatter::SITE_DATE_FORMAT, $value);
        }
        catch(\Throwable $e) {
            $date = Carbon::createFromTimestamp($value);
        }
        return $date;
    }


    /**
     * Returns midnight of the given date
     * @param integer|string|DateTime $value
     * @return null|Carbon
     */
    public static function getStartOfDay($value) {
        /**
         * @var Formatter $formatter
         */
        $date = self::getDateInstance($value);
        return $date->startOfDay();
    }


    /**
     * Returns the timestamp of last second of the given date
     * @param integer|string|DateTime $value
     * @return null|Carbon
     */
    public static function getEndOfDay($value) {
        /**
         * @var Formatter $formatter
         */
        $date = self::getDateInstance($value);
        return $date->endOfDay();
    }


    /**
     * Returns the beginning of the day before given date
     *
     * @param integer|string|DateTime $value
     * @return null| Carbon
     */
    public static function getDayBefore($value) {
        $date = self::getDateInstance($value);
        return $date->subDay(1)->startOfDay();
    }
}