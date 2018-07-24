<?php

namespace common\components\calculators\data;

use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 24.07.2017
 * Time: 20:43
 */
class MultipliedData extends TaozRawData
{
    private $subchannels_data = [];


    public function __construct(Carbon $start_date, Carbon $end_date, float $cop = 0) {
        parent::__construct($start_date, $end_date);
        $this->cop = $cop;
    }


    /**
     * @return array
     */
    public function getSubchannelsData(): array {
        return $this->subchannels_data;
    }


    /**
     * @return float
     */
    public function getPisgaPay(): float {
        return $this->pisga_pay/100;
    }


    /**
     * @return float
     */
    public function getGevaPay(): float {
        return $this->geva_pay/100;
    }


    /**
     * @return float
     */
    public function getShefelPay(): float {
        return $this->shefel_pay/100;
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


    public function applyPrices(float $pisga_price, float $geva_price, float $shefel_price) {
        $this->pisga_pay = $this->pisga_consumption * $this->cop * $pisga_price;
        $this->geva_pay = $this->geva_consumption * $this->cop * $geva_price;
        $this->shefel_pay = $this->shefel_consumption * $this->cop * $shefel_price;
    }


    /**
     * @return float
     */
    public function getAirShefelConsumption(): float {
        return $this->air_shefel_consumption;
    }


    /**
     * @return float
     */
    public function getAirGevaConsumption(): float {
        return $this->air_geva_consumption;
    }


    /**
     * @return float
     */
    public function getAirPisgaConsumption(): float {
        return $this->air_pisga_consumption;
    }


    private $air_shefel_consumption = 0;
    private $air_geva_consumption = 0;
    private $air_pisga_consumption = 0;
    public $reading_data = array();


    public function add(SubchannelData $subchannel_data, $reading_data = null) {
        $this->subchannels_data[] = $subchannel_data;
        $this->shefel_consumption += $subchannel_data->getShefelConsumption();
        $this->geva_consumption += $subchannel_data->getGevaConsumption();
        $this->pisga_consumption += $subchannel_data->getPisgaConsumption();
      $this->pisga_reading += $subchannel_data->getPisgaReading();
      $this->geva_reading += $subchannel_data->getGevaReading();
      $this->shefel_reading += $subchannel_data->getShefelReading();
        $this->reading_from += $subchannel_data->getReadingFrom();
        $this->reading_to += $subchannel_data->getReadingTo();
        $this->air_shefel_consumption = $this->shefel_consumption * $this->cop;
        $this->air_geva_consumption = $this->geva_consumption * $this->cop;
        $this->air_pisga_consumption = $this->pisga_consumption * $this->cop;
        $this->reading_data = $reading_data;
    }
}