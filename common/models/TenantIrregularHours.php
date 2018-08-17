<?php

namespace common\models;

use common\components\db\ActiveRecord;
use Yii;

/**
 * TenantIrregularHours is the class for the table "site_irregular_hours".
 * @property $id
 * @property $day_number
 * @property $tenant_id
 * @property $hours_from
 * @property $hours_to
 */

class TenantIrregularHours extends ActiveRecord
{

    const SUNDAY = 1;
    const MONDAY = 2;
    const TUESDAY = 3;
    const WEDNESDAY = 4;
    const THURSDAY = 5;
    const FRIDAY = 6;
    const SATURDAY = 7;

    const USAGE_TYPE = [
        ['title' => 'Only normal hours', 'value' => 'normal'],
        ['title' => 'Only irregular hours', 'value' => 'irregular'],
        ['title' => 'Both normal and irregular hours (with penalty)', 'value' => 'with_penalty'],
        ['title' => 'Both normal and irregular hours (without penalty)', 'value' => 'without_penalty'],
    ];

    public static function tableName()
    {
        return 'tenant_irregular_hours';
    }


    public function rules()
    {
        return [
            [['day_number'], 'in', 'range' => self::getDaysConst()],
            [['tenant_id'], 'integer'],
            [['hours_from', 'hours_to'], 'validateTime']
        ];
    }

    public function validateTime($attr)
    {
        if (!preg_match('/\d{2}:\d{2}:\d{2}/', $this->{$attr})) {
            $this->addError($attr, Yii::t('common.site', 'Wrong time format'));
        }
    }

    public static function dayNameToNumber()
    {
        return [
            'Sunday' => self::SUNDAY,
            'Monday' => self::MONDAY,
            'Tuesday' => self::TUESDAY,
            'Wednesday' => self::WEDNESDAY,
            'Thursday' => self::THURSDAY,
            'Friday' => self::FRIDAY,
            'Saturday' => self::SATURDAY
        ];
    }

    public static function numberToDayName()
    {
        return array_flip(self::dayNameToNumber());
    }

    public static function getDays()
    {
        return [
            self::SUNDAY => Yii::t('common.site', 'Sunday'),
            self::MONDAY => Yii::t('common.site','Monday'),
            self::TUESDAY => Yii::t('common.site', 'Tuesday'),
            self::WEDNESDAY => Yii::t('common.site', 'Wednesday'),
            self::THURSDAY => Yii::t('common.site', 'Thursday'),
            self::FRIDAY => Yii::t('common.site', 'Friday'),
            self::SATURDAY => Yii::t('common.site', 'Saturday')
        ];
    }

    public static function getDaysConst()
    {
        return [
            self::SUNDAY,
            self::MONDAY,
            self::TUESDAY,
            self::WEDNESDAY,
            self::THURSDAY,
            self::FRIDAY,
            self::SATURDAY
        ];
    }

}