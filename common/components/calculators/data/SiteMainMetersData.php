<?php

namespace common\components\calculators\data;
use yii\db\Query;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 09.08.2017
 * Time: 15:08
 */
class SiteMainMetersData
{
    private $air_main_channels = [];
    private $electrical_main_channels = [];


    /**
     * SiteMainMetersData constructor.
     * @param MainMetersData[] $air_main_channels
     * @param MainMetersData[] $electrical_main_channels
     */
    public function __construct(array $air_main_channels, array $electrical_main_channels) {
        $this->air_main_channels = $air_main_channels;
        $this->electrical_main_channels = $electrical_main_channels;
    }


    /**
     * @return array|MainMetersData[]
     */
    public function getAirMainChannels() {
        return $this->air_main_channels;
    }



    /**
     * @return array|MainMetersData[]
     */
    public function getElectricalMainChannels() {
        return $this->electrical_main_channels;
    }




}