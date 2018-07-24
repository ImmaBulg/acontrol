<?php

namespace common\components\calculators\data;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 24.07.2017
 * Time: 20:44
 */
interface ITaozRawData
{
//    /**
//     * @param float $pisga_consumption
//     */
//    public function setPisgaConsumption(float $pisga_consumption);
//
//
//    /**
//     * @param float $geva_consumption
//     */
//    public function setGevaConsumption(float $geva_consumption);
//
//
//    /**
//     * @param float $shefel_consumption
//     */
//    public function setShefelConsumption(float $shefel_consumption);
    /**
     * @return float
     */
    public function getPisgaConsumption(): float;


    /**
     * @return float
     */
    public function getGevaConsumption(): float;


    /**
     * @return float
     */
    public function getShefelConsumption(): float;
}