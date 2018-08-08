<?php

namespace common\models;

use dezmont765\yii2bundle\models\AParentActiveRecord;

/**
 * This is the model class for table "sub_air_rates".
 *
 * @property integer $id
 * @property integer $rate_id
 * @property string $category
 * @property double $rate
 * @property string $identifier
 *
 * @property AirRates $air_rate
 * @property SubAirRatesTaoz[] $subAirRatesTaoz
 */
class SubAirRates extends AParentActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'sub_air_rates';
    }


    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['rate_id'], 'integer'],
            [['rate'], 'number'],
            [['category', 'identifier'], 'string', 'max' => 255],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'rate_id' => 'Rate ID',
            'category' => 'Category',
            'rate' => 'Rate in Agorot',
            'identifier' => 'Note',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAirRate() {
        return $this->hasOne(AirRates::className(), ['id' => 'rate_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubAirRatesTaoz() {
        return $this->hasMany(SubAirRatesTaoz::className(), ['id' => 'id']);
    }


    public function getSubTableParentClass() {
        return ASubAirRates::className();
    }


    public static function basicSubTableClass() {
        return SubAirRatesBase::className();
    }


    public static function basicSubTableView() {
        return 'sub-air-rates-form';
    }


    public static function subTableViews() {
        return [
            ASubAirRates::TAOZ => 'sub-air-rates-taoz-form',
        ];
    }


    public static function subTablesClasses() {
        return [
            ASubAirRates::TAOZ => SubAirRatesTaoz::className(),
        ];
    }


    public static function subTablesRelationFields() {
        return [
            ASubAirRates::TAOZ => 'subAirRatesTaoz',
        ];
    }


    public static function subTableBaseView() {
        return 'sub-air-rates-base-view';
    }
}
