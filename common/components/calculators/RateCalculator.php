<?php

namespace common\components\calculators;

use Carbon\Carbon;
use common\components\calculators\data\RatedData;
use common\components\calculators\data\WeightedChannel;
use common\components\calculators\exceptions\InvalidRateDateException;
use common\helpers\TimeManipulator;
use common\models\AirRates;
use common\models\MeterChannelMultiplier;
use yii\helpers\VarDumper;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 24.07.2017
 * Time: 15:25
 */
class RateCalculator
{
    /**
     * @var Carbon|null
     */
    private $from_date = null;
    /**
     * @var Carbon|null
     */
    private $to_date = null;
    /**
     * @var AirRates
     */
    private $rate = null;
    /**
     * @var WeightedChannel
     */
    private $weighted_channel = null;


    /**
     * TenantCalculator constructor.
     * @param AirRates|null $rate
     * @param WeightedChannel $weighted_channel
     * @param Carbon|null $from_date
     * @param Carbon|null $to_date
     * @internal param int $channel_id
     */
    public function __construct(AirRates $rate, WeightedChannel $weighted_channel, Carbon $from_date, Carbon $to_date) {
        $this->rate = $rate;
        $this->weighted_channel = $weighted_channel;
        $this->from_date = clone $from_date;
        $this->to_date = clone $to_date;
        $this->normalizeDateRange();
    }


    private function normalizeDateRange() {
        $rate_from_date = Carbon::createFromFormat('Y-m-d', $this->rate->start_date)->startOfDay();
        $rate_to_date = Carbon::createFromFormat('Y-m-d', $this->rate->end_date)->endOfDay();
        if($rate_from_date > $this->to_date) {
            throw new InvalidRateDateException();
        }
        else {
            if($rate_from_date > $this->from_date) {
                $this->from_date = $rate_from_date;
            }
        }
        if($rate_to_date < $this->from_date) {
            throw new InvalidRateDateException();
        }
        else {
            if($rate_to_date < $this->to_date) {
                $this->to_date = $rate_to_date;
            }
        }
    }


    /**
     * @param array $time_ranges
     * @param float $cop
     * @return RatedData
     */
    public function calculate(array $time_ranges = [], $cop = 0) {
        $multipliers = MeterChannelMultiplier::getMultipliers($this->weighted_channel->getChannelId(), $this->from_date,
                                                              $this->to_date);
        $rated_data = new RatedData($this->from_date, $this->to_date, $this->rate);
        foreach($multipliers as $multiplier) {
            $multiplier_calculator =
                new MultipliersCalculator($this->from_date, $this->to_date, $this->weighted_channel, $multiplier);
            $data = $multiplier_calculator->calculate($this->rate, $time_ranges, $cop);
            $rated_data->add($data);
        }
        //VarDumper::dump($rated_data, 100, true);
        return $rated_data;
    }


}