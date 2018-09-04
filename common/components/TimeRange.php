<?php

namespace common\components;

use Carbon\Carbon;
use common\models\TenantIrregularHours;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 21.07.2017
 * Time: 16:38
 */
class TimeRange
{



    /**
     * @return Carbon
     */
    public static function midnight() {
        return Carbon::today()->startOfDay();
    }


    /**
     * @return Carbon
     */
    public static function endOfDay() {
        return Carbon::today()->endOfDay();
    }


    public function isOverlappingMidnight() {
        $is_overlapping_midnight =
            $this->getStartTime() > $this->getEndTime() && $this->getEndTime() !== self::midnight();
        return $is_overlapping_midnight;
    }


    public function isStartingFromMidnight() {
        $is_start_on_midnight = $this->getStartTime()->eq(self::midnight());
        return $is_start_on_midnight;
    }


    public function isEndingOnMidnight() {
        $is_end_on_midnight = $this->getEndTime()->eq(self::endOfDay());
        return $is_end_on_midnight;
    }


    public function getInverted() {
        return new TimeRange($this->end_time, $this->start_time, $this->getDayNumber());
    }


    /**
     * @return Carbon
     */
    public function getStartTime(): Carbon {
        return Carbon::today()->setTimeFromTimeString($this->start_time)->minute(0)->second(0);
    }


    /**
     * @return Carbon
     */
    public function getEndTime(): Carbon {
        $end_time = Carbon::today()->setTimeFromTimeString($this->end_time);
        if(!$end_time->equalTo(Carbon::today()->endOfDay()) &&
           !$end_time->copy()->second(59)->equalTo(Carbon::today()->endOfDay())) {
            $end_time->minute(0)->second(0);
        }
        else {
            $end_time->endOfDay();
        }
        return $end_time;
    }

    public function getDay()
    {
        $number_to_day = TenantIrregularHours::numberToDayName();

        if (!is_null($this->day_number) && array_key_exists($this->day_number, $number_to_day)) {
            return $number_to_day[$this->day_number];
        }

        return false;
    }

    public function getDayNumber()
    {
        return $this->day_number;
    }

    public function getHours(): array
    {
        $start_hour = Carbon::today()->setTimeFromTimeString($this->start_time);
        $end_hour = Carbon::today()->setTimeFromTimeString($this->end_time);
        $result = [];
        for ($i = $start_hour->hour; $i <= $end_hour->hour; $i++) {
            $result[] = $i;
        }
        return $result;
    }

    private $start_time = '00:00:00';
    private $end_time = '23:59:59';
    private $day_number = null;


    /**
     * TimeRange constructor.
     * @param string $start_time
     * @param string $end_time
     * @param integer $day_number
     */
    public function __construct($start_time = null, $end_time = null, int $day_number = null) {
        if($start_time instanceof Carbon) {
            $this->start_time = $start_time->format('H:i:s');
        }
        else {
            if(is_string($start_time)) {
                $this->start_time = $start_time;
            }
        }
        if($end_time instanceof Carbon) {
            $this->end_time = $end_time->format('H:i:s');
        }
        else {
            if(is_string($start_time)) {
                $this->end_time = $end_time;
            }
        }
        $this->day_number = $day_number;
    }


}