<?php

namespace common\components\calculators;

use Carbon\Carbon;
use common\components\calculators\data\ChannelMultipliers;
use common\components\calculators\data\MultipliedData;
use common\components\calculators\data\SubchannelData;
use common\components\calculators\data\WeightedChannel;
use common\components\calculators\exceptions\InvalidMultiplierDateException;
use common\components\calculators\single_data\SingleMultipliedData;
use common\components\calculators\TaozDataCalculator;
use common\components\calculators\TaozDataQueryGenerator;
use common\components\calculators\SingleDataQueryGenerator;
use common\helpers\TimeManipulator;
use common\models\AirMeterRawData;
use common\models\AirRates;
use common\models\ElectricityMeterRawData;
use common\models\RateType;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 24.07.2017
 * Time: 16:11
 */
class MultipliersCalculator
{
    /**
     * @var Carbon
     */
    private $from_date = null;
    /**
     * @var Carbon
     */
    private $to_date = null;
    /**
     * @var WeightedChannel
     */
    private $weighted_channel = null;
    /** @var ChannelMultipliers */
    private $channel_multiplier = null;

    private $subchannels = [];


    /**
     * MultipliersCalculator constructor.
     * @param Carbon $from_date
     * @param Carbon $to_date
     * @param WeightedChannel $weighted_channel
     * @param ChannelMultipliers $channel_multiplier
     * @internal param array $subchannels
     */
    public function __construct(Carbon $from_date, Carbon $to_date, WeightedChannel $weighted_channel, ChannelMultipliers $channel_multiplier) {
        $this->from_date = clone $from_date;
        $this->to_date = clone $to_date;
        $this->weighted_channel = $weighted_channel;
        $this->channel_multiplier = $channel_multiplier;
        $this->subchannels = $weighted_channel->getSubchannels();
        $this->normalizeDateRange();
    }


    private function normalizeDateRange() {
        if($this->channel_multiplier->getStartDate() instanceof Carbon) {
            if($this->channel_multiplier->getStartDate() > $this->to_date) {
                throw new InvalidMultiplierDateException();
            }
            else {
                if($this->channel_multiplier->getStartDate() > $this->from_date) {
                    $this->from_date = $this->channel_multiplier->getStartDate();
                }
            }
        }
        if($this->channel_multiplier->getEndDate() instanceof Carbon) {
            if($this->channel_multiplier->getEndDate() < $this->from_date) {
                throw new InvalidMultiplierDateException();
            }
            else {
                if($this->channel_multiplier->getEndDate() < $this->to_date) {
                    $this->to_date = $this->channel_multiplier->getEndDate();
                }
            }
        }
    }


    /**
     * @param AirRates $rate
     * @param array $time_ranges
     * @param float|int $cop
     * @return MultipliedData
     */
    public function calculate(AirRates $rate, array $time_ranges = [], $cop = 0) {
        if ($rate->is_taoz) {
            $multiplied_data = new MultipliedData($this->from_date, $this->to_date, $cop);
        }
        else {
            $multiplied_data = new SingleMultipliedData($this->from_date, $this->to_date, $cop);
        }
        if(!empty($time_ranges)) {
            foreach($this->subchannels as $subchannel) {
                $air_reading_base_query = (new Query())->select('kilowatt_hour')->from(AirMeterRawData::tableName())
                                                       ->where(['meter_id' => $this->weighted_channel->getChannel()->relationMeter->name])
                                                       ->andWhere(['channel_id' => $subchannel]);
                if ($rate->is_taoz) {
                    //VarDumper::dump('before query generator');

                    /*VarDumper::dump('three');
                    VarDumper::dump($time_ranges, 100, true);*/
                        $queries_generator =
                            new TaozDataQueryGenerator($this->from_date, $this->to_date, $rate->subAirRatesTaoz,
                                                       $time_ranges);
                        $taoz_queries = $queries_generator->generate('datetime', $air_reading_base_query);
                        $taoz_consumption_calculator =
                            new TaozDataCalculator($this->from_date, $this->to_date, $taoz_queries);
                        $data =
                            $taoz_consumption_calculator->calculate($this->channel_multiplier->getVoltageMultiplier(),
                                                                    $this->channel_multiplier->getCurrentMultiplier(),
                                                                    $this->weighted_channel->getPercent())
                                                        ->getData();

                        //print '<pre>';
                        //print_r($data);
                        //print '</pre>';
                        //VarDumper::dump($data, 100, true);
                        //VarDumper::dump($rate->subAirRatesTaoz, 100, true);
                        $multiplied_data->add($data, $taoz_consumption_calculator->data_by_time);
                } else {
                    $queries_generator = new SingleDataQueryGenerator($this->from_date, $this->to_date, $time_ranges);
                    $queries = $queries_generator->generate('datetime', $air_reading_base_query);
                    $consumption_calculator = new SingleDataCalculator($this->from_date, $this->to_date, $queries);
                    $data = $consumption_calculator->calculate($this->channel_multiplier->getVoltageMultiplier(),
                        $this->channel_multiplier->getCurrentMultiplier(),
                        $this->weighted_channel->getPercent())
                        ->getData();
                    $multiplied_data->add($data, $consumption_calculator->data_by_time);
                }
            }
        }
        return $multiplied_data;
    }
}