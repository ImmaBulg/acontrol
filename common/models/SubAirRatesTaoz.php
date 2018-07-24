<?php

namespace common\models;

use Carbon\Carbon;
use common\components\TimeRange;
use common\constants\DataCategories;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "sub_air_rates_taoz".
 *
 * @property integer $id
 * @property string $type
 * @property string $week_part
 * @property string $hours_from
 * @property string $hours_to
 *
 * @property SubAirRates $id0
 */
class SubAirRatesTaoz extends ASubAirRates
{
    public $category = parent::TAOZ;


    const SUNDAY_THURSDAY = 'sunday-thursday';
    const FRIDAY_HOLIDAY = 'friday-holiday';
    const SATURDAY_HOLIDAY = 'saturday-holiday';


    public static function types() {
        return [
            DataCategories::GEVA => 'Geva',
            DataCategories::PISGA => 'Pisga',
            DataCategories::SHEFEL => 'Shefel',
        ];
    }


    /**
     * @return Carbon
     */
    public function getStartTime(): Carbon {
        return Carbon::today()->setTimeFromTimeString($this->hours_from)->minute(0)->second(0);
    }


    /**
     * @return TimeRange[]
     */
    public function getTimeRanges() {
        if($this->getEndTime() < $this->getStartTime()) {
            return [
                new TimeRange($this->getStartTime(), TimeRange::endOfDay()),
                new TimeRange(TimeRange::midnight(), $this->getEndTime()),
            ];
        }
        else {
            return [new TimeRange($this->getStartTime(), $this->getEndTime())];
        }
    }


    /**
     * @return Carbon
     */
    public function getEndTime(): Carbon {
        return Carbon::today()->setTimeFromTimeString($this->hours_to);
    }


    public static function daysByWeekParts() {
        return [
            self::SUNDAY_THURSDAY => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
            self::FRIDAY_HOLIDAY => ['Friday'],
            self::SATURDAY_HOLIDAY => ['Saturday'],
        ];
    }


    public function getDaysByWeekParts() {
        $week_parts = self::daysByWeekParts();
        if(isset($week_parts[$this->week_part])) {
            return $week_parts[$this->week_part];
        }
        else return null;
    }


    public static function week_parts() {
        return [
            self::SUNDAY_THURSDAY => 'Sunday to Thursday',
            self::FRIDAY_HOLIDAY => 'Fridays and holiday evenings',
            self::SATURDAY_HOLIDAY => 'Saturday and holiday',
        ];
    }


    public function rules() {
        return ArrayHelper::merge(parent::rules(), [
            ['id', 'integer'],
            [['hours_from', 'hours_to'], 'required'],
            [['type', 'week_part'], 'string'],
        ]);
    }


    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'sub_air_rates_taoz';
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'week_part' => 'Week Part',
            'hours_from' => 'Hours From',
            'hours_to' => 'Hours To',
        ];
    }


}
