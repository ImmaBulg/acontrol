<?php
namespace common\models;

use common\models\ASubAirRates;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.04.2017
 * Time: 17:09
 */
class SubAirRatesBase extends ASubAirRates
{

    public $category = parent::ALL;


    public static function tableName() {
        return 'sub_air_rates';
    }
}