<?php

namespace common\components\calculators\data;

use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 24.07.2017
 * Time: 16:14
 */
class ChannelMultipliers
{
    /**
     * @return float|int
     */
    public function getVoltageMultiplier() {
        return $this->voltage_multiplier;
    }


    /**
     * @return float|int
     */
    public function getCurrentMultiplier() {
        return $this->current_multiplier;
    }


    /**
     * @return Carbon
     */
    public function getStartDate() {
        return $this->start_date;
    }


    /**
     * @return Carbon
     */
    public function getEndDate() {
        return $this->end_date;
    }


    private $voltage_multiplier = 1;
    private $current_multiplier = 1;
    private $start_date = null;
    private $end_date = null;


    /**
     * ChannelMultipliers constructor.
     * @param float|int $voltage_multiplier
     * @param float|int $current_multiplier
     * @param Carbon|null|string $start_date
     * @param Carbon|null|string $end_date
     */
    public function __construct(float $voltage_multiplier, float $current_multiplier, string $start_date = null, string $end_date = null) {
        $this->voltage_multiplier = $voltage_multiplier;
        $this->current_multiplier = $current_multiplier;
        if($start_date !== null) {
            $this->start_date = Carbon::createFromFormat('Y-m-d', $start_date)->startOfDay();
        }
        if($end_date !== null) {
            $this->end_date = Carbon::createFromFormat('Y-m-d', $end_date)->endOfDay();
        }
    }

}