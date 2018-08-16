<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 13.08.2018
 * Time: 10:01
 */

namespace common\models;

use common\components\db\ActiveRecord;
use Yii;



class SiteIrregularHours extends ActiveRecord
{
    const SUNDAY = 1;
    const MONDAY = 2;
    const TUESDAY = 3;
    const WEDNESDAY = 4;
    const THURSDAY = 5;
    const FRIDAY = 6;
    const SATURDAY = 7;

    public static function tableName()
    {
        return 'site_irregular_hours';
    }

    public function rules()
    {
        return [
            [['day_number'], 'in', 'range' => self::getDaysConst()],
            [['site_id'], 'string'],
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