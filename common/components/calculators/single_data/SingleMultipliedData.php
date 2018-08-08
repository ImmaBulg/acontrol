<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 06.08.2018
 * Time: 9:49
 */

namespace common\components\calculators\single_data;

use yii\helpers\VarDumper;

class SingleMultipliedData extends SingleData
{
    private $subchannels_data = [];
    private $pay;
    private $air_consumption = 0;
    public $reading_data = array();

    public function __construct($start_date, $end_date, $cop = 0) {
        parent::__construct($start_date, $end_date);
        $this->cop = $cop;
    }

    public function getSubchannelsData() : array  {
        return $this->subchannels_data;
    }

    public function getPay() : float {
        return $this->pay / 100;
    }

    public function applyPrice($price) {
        $this->pay = $this->consumtion * $this->cop * $price;
    }

    public function getAirConsumption() : float {
        return $this->air_consumption;
    }

    public function add(SingleSubchannelData $subchannel_data, $reading_data = null) {
        $this->subchannels_data[] = $subchannel_data;
        $this->consumtion = $subchannel_data->getConsumption();
        $this->reading = $subchannel_data->getReading();
        $this->reading_from = $subchannel_data->getReadingFrom();
        $this->reading_to = $subchannel_data->getReadingTo();
        $this->air_consumption = $this->consumtion * $this->cop;
        $this->reading_data = $reading_data;
    }
}