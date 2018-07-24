<?php

namespace common\components\calculators\data;

use Carbon\Carbon;
use common\models\AirRates;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 24.07.2017
 * Time: 21:01
 */
class RatedData extends TaozRawData
{
    /**
     * @var AirRates
     */
    private $rate = null;


    public function __construct(Carbon $start_date, Carbon $end_date, AirRates $rate) {
        parent::__construct($start_date, $end_date);
        $this->rate = $rate;
        $this->pisga_price = $this->rate->getPisgaPrice();
        $this->geva_price = $this->rate->getGevaPrice();
        $this->shefel_price = $this->rate->getShefelPrice();
    }


    private $multiplied_data = [];


    /**
     * @return MultipliedData[]
     */
    public function getMultipliedData(): array {
        return $this->multiplied_data;
    }


    /**
     * @return float
     */
    public function getPisgaPrice(): float {
        return $this->pisga_price;
    }


    /**
     * @return float
     */
    public function getGevaPrice(): float {
        return $this->geva_price;
    }


    /**
     * @return float
     */
    public function getShefelPrice(): float {
        return $this->shefel_price;
    }


    /**
     * @return AirRates
     */
    public function getRate(): AirRates {
        return $this->rate;
    }


    /**
     * @var float
     */
    private $pisga_pay = 0;
    /**
     * @var float
     */
    private $geva_pay = 0;
    /**
     * @var float
     */
    private $shefel_pay = 0;


    /**
     * @var float
     */
    private $pisga_price = 0;
    /**
     * @var float
     */
    private $geva_price = 0;


    /**
     * @return float
     */
    public function getPisgaPay(): float {
        return $this->pisga_pay;
    }


    /**
     * @return float
     */
    public function getGevaPay(): float {
        return $this->geva_pay;
    }


    /**
     * @return float
     */
    public function getShefelPay(): float {
        return $this->shefel_pay;
    }


    /**
     * @var float
     */
    private $shefel_price = 0;

    public function isEmpty() {

    }


    public $reading_data;

    public function add(MultipliedData $data) {
        $this->multiplied_data[] = $data;
        $this->pisga_consumption += $data->getPisgaConsumption();
        $this->geva_consumption += $data->getGevaConsumption();
        $this->shefel_consumption += $data->getShefelConsumption();
      $this->pisga_reading += $data->getPisgaReading();
      $this->geva_reading += $data->getGevaReading();
      $this->shefel_reading += $data->getShefelReading();
        $data->applyPrices($this->pisga_price, $this->geva_price, $this->shefel_price);
        $this->pisga_pay += $data->getPisgaPay();
        $this->geva_pay += $data->getGevaPay();
        $this->shefel_pay += $data->getShefelPay();
        $this->reading_data = $data->reading_data;
    }
}