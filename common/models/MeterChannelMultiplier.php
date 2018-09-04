<?php

namespace common\models;

use common\components\calculators\data\ChannelMultipliers;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;
use common\components\behaviors\ToTimestampBehavior;

/**
 * MeterChannelMultiplier is the class for the table "meter_channel_multiplier".
 */
class MeterChannelMultiplier extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    const DEFAULT_METER_MULTIPLIER = 1;


    public static function tableName() {
        return 'meter_channel_multiplier';
    }


    public function rules() {
        return [
            [['meter_id', 'channel_id'], 'required'],
            [['meter_id', 'channel_id'], 'integer'],
            ['meter_multiplier', 'default', 'value' => 0],
            ['meter_multiplier', 'number', 'min' => 0],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
        ];
    }


    public function attributeLabels() {
        return [
            'id' => Yii::t('common.meter', 'ID'),
            'meter_id' => Yii::t('common.meter', 'Meter ID'),
            'channel_id' => Yii::t('common.meter', 'Channel ID'),
            'meter_multiplier' => Yii::t('common.meter', 'Meter multiplier'),
            'start_date' => Yii::t('common.meter', 'Start date'),
            'end_date' => Yii::t('common.meter', 'End date'),
            'status' => Yii::t('common.meter', 'Status'),
            'created_at' => Yii::t('common.meter', 'Created at'),
            'modified_at' => Yii::t('common.meter', 'Modified at'),
            'created_by' => Yii::t('common.meter', 'Created by'),
            'modified_by' => Yii::t('common.meter', 'Modified by'),
        ];
    }


    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'modified_at',
            ],
            [
                'class' => UserIdBehavior::className(),
                'createdByAttribute' => 'created_by',
                'modifiedByAttribute' => 'modified_by',
            ],
            [
                'class' => ToTimestampBehavior::className(),
                'attributes' => [
                    'start_date',
                    'end_date',
                ],
            ],
        ];
    }


    public function getRelationMeter() {
        return $this->hasOne(Meter::className(), ['id' => 'meter_id']);
    }


    public function getRelationMeterChannel() {
        return $this->hasOne(MeterChannel::className(), ['id' => 'channel_id']);
    }


    public static function getListStatuses() {
        return [
            self::STATUS_INACTIVE => Yii::t('common.meter', 'Inactive'),
            self::STATUS_ACTIVE => Yii::t('common.meter', 'Active'),
        ];
    }


    public function getAliasStatus() {
        $list = self::getListStatuses();
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }


    /**
     * @param $channel_id
     * @param $start_date
     * @param $end_date
     * @param bool $is_single
     * @return array|ChannelMultipliers[]
     */
    public static function getMultipliers($channel_id, $start_date, $end_date, $is_single = false) {
        $query = (new Query())
            ->select(['t.meter_multiplier', 'start_date', 'end_date'])
            ->from(static::tableName() . ' t')
            ->andWhere(['t.channel_id' => $channel_id])
            ->andWhere([
                           'or',
                           [
                               'and',
                               't.start_date IS NULL',
                               't.end_date > :start_date',
                           ],
                           [
                               'and',
                               't.start_date < :end_date',
                               't.end_date > :start_date',
                           ],
                       ])
            ->addParams([
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                        ])
            ->orderBy(['t.end_date' => SORT_ASC]);
        $multipliers = Yii::$app->db->cache(function ($db) use ($query, $is_single) {
            if(!$is_single) {
                $multipliers = $query->all();
                foreach($multipliers as &$multiplier) {
                    $multiplier = new ChannelMultipliers($multiplier['meter_multiplier'],
                                                         $multiplier['start_date'],
                                                         $multiplier['end_date']);
                }
                return $multipliers;
            }
            else {
                $multiplier = $query->one();
                $multiplier = new ChannelMultipliers($multiplier['meter_multiplier'],
                                                     $multiplier['start_date'],
                                                     $multiplier['end_date']);
                return $multiplier;
            }
        }, static::CACHE_DURATION);
        if($multipliers == null) {
            if(!$is_single) {
                $multipliers = [];
                $multipliers[] = self::getMeterMultiplier($channel_id);
            }
            else {
                $multipliers = self::getMeterMultiplier($channel_id);
            }
        }
        return $multipliers;
    }


    public static function getMeterMultiplier($channel_id) {
        $query = (new Query())
            ->select(['t.meter_multiplier'])
            ->from(MeterChannel::tableName() . ' t')
            ->andWhere(['t.id' => $channel_id]);
        $current_multiplier = Yii::$app->db->cache(function ($db) use ($query) {
            return $query->createCommand($db)->queryOne();
        }, static::CACHE_DURATION);
        $multiplier =
            new ChannelMultipliers($current_multiplier['meter_multiplier']);
        return $multiplier;
    }
}
