<?php
namespace common\models;

use dezmont765\yii2bundle\models\ASubActiveRecord;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.04.2017
 * Time: 16:47
 * @property SubAirRates $subAirRate
 * @property integer $id
 */
abstract class ASubAirRates extends ASubActiveRecord
{

    const TAOZ = 'taoz';
    const FIXED = 'fixed';

    public function rules() {
        return [
            [['rate_id', 'rate'],'required'],
            ['rate','number'],
            ['identifier','string']
        ];
    }

    public static function rateTypesToCategories() {
        return [
            RateType::TYPE_FIXED => self::FIXED,
            RateType::TYPE_TAOZ => self::TAOZ
        ];
    }


    public static function getCategoryByRateTypeId($rate_type_id) {
        $rate_type = RateType::findOne($rate_type_id);
        if(!$rate_type instanceof RateType)
            return null;
        $rate_types = self::rateTypesToCategories();
        if(isset($rate_types[$rate_type->type])) {
            return $rate_types[$rate_type->type];
        }
        else return null;
    }


    public $category = null;
    public $rate_id = null;
    public $rate = null;
    public $identifier = null;


    public function getMainModelClass() {
        return SubAirRates::className();
    }


    public static function getMainModelAttribute() {
        return 'subAirRate';
    }


    public function getSubAirRate() {
        return $this->hasOne(SubAirRates::className(), ['id' => 'id']);
    }


    public function setSubAirRate($sub_air_rate) {
        $this->subAirRate = $sub_air_rate;
    }

}