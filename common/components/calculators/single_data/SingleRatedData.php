<?php

namespace common\components\calculators\single_data;


use common\models\AirRates;


/**
 * Created by PhpStorm.
 * User: admin
 * Date: 06.08.2018
 * Time: 9:31
 */

class SingleRatedData extends SingleData
{
    private $rate;
    private $price;
    private $pay = 0;
    private $fixed_rule = 0;
    private $money_addition;
    private $multiplied_data = [];
    public $reading_data;

    public function __construct($start_date, $end_date, $rate) {
        parent::__construct($start_date, $end_date);
        $this->rate = $rate;
        $this->price = $this->rate->getPrice();
    }

    public function getRate() : AirRates {
        return $this->rate;
    }

    public function getPay() : float {
        return $this->pay;
    }

    public function getPrice() : float {
        return $this->price;
    }

    public function getMultipliedData() : array {
        return $this->multiplied_data;
    }

    public function setFixedRule($rule) {
        $this->fixed_rule = $rule;
    }

    public function getFixedRule() : float {
        return $this->fixed_rule;
    }

    public function add(SingleMultipliedData $data) {
        $this->multiplied_data[] = $data;
        $this->consumtion += $data->getConsumption();
        $this->reading += $data->getReading();
        $data->applyPrice($this->price);
        $this->pay += $data->getPay();
        $this->reading_data = $data;
    }

    public function getMoneyAddition() : float {
        return $this->money_addition;
    }

    public function setMoneyAddition($money_addition) {
        $this->money_addition = $money_addition;
    }
}