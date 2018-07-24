<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * MeterChannel is the class for the table "meter_channel".
 * @property Meter $relationMeter
 * @property int $id
 * @property int $meter_id
 * @property int $current_multiplier
 * @property int $voltage_multiplier
 * @property int $created_at [int(11)]
 * @property int $modified_at [int(11)]
 * @property int $created_by [int(11)]
 * @property int $modified_by [int(11)]
 * @property string $old_id [varchar(255)]
 * @property int $channel
 * @property int $is_main
 * @property bool $status [tinyint(1)]
 */
class MeterChannel extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;


    public static function tableName() {
        return 'meter_channel';
    }


    public function rules() {
        return [
            [['meter_id', 'channel'], 'required'],
            [['meter_id', 'channel'], 'integer'],
            [['current_multiplier', 'voltage_multiplier'], 'default', 'value' => 0],
            [['current_multiplier', 'voltage_multiplier'], 'number', 'min' => 0],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
            ['is_main', 'safe'],
        ];
    }


    public function attributeLabels() {
        return [
            'id' => Yii::t('common.meter', 'ID'),
            'meter_id' => Yii::t('common.meter', 'Meter ID'),
            'channel' => Yii::t('common.meter', 'Channel'),
            'current_multiplier' => Yii::t('common.meter', 'Current multiplier'),
            'voltage_multiplier' => Yii::t('common.meter', 'Voltage multiplier'),
            'old_id' => Yii::t('common.meter', 'Old ID'),
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
        ];
    }


    public function getRelationMeter() {
        return $this->hasOne(Meter::className(), ['id' => 'meter_id']);
    }


    public function getRelationMeterChannelMultipliers() {
        return $this->hasMany(MeterChannelMultiplier::className(), ['id' => 'channel_id']);
    }


    public function getRelationMeterSubchannels() {
        return $this->hasMany(MeterSubchannel::className(), ['channel_id' => 'id']);
    }


    public function getRelationMeterRawDatas() {
        return $this->hasMany(ElectricityMeterRawData::className(), ['channel_id' => 'id']);
    }


    public function getRelationSiteMeterChannel() {
        return $this->hasOne(SiteMeterChannel::className(), ['channel_id' => 'id']);
    }


    public function getRelationRuleSingleChannels() {
        return $this->hasMany(RuleSingleChannel::className(), ['channel_id' => 'id']);
    }


    public function getRelationRuleGroupLoadsAsMeterChannel() {
        return $this->hasMany(RuleGroupLoad::className(), ['channel_id' => 'id']);
    }


    public function getRelationRuleGroupLoadsAsMeterChannelGroup() {
        return RuleGroupLoad::find()
                            ->innerJoin(MeterChannelGroup::tableName() . ' group',
                                        'group.id = ' . RuleGroupLoad::tableName() . '.channel_group_id')
                            ->innerJoin(MeterChannelGroupItem::tableName() . ' group_item',
                                        'group_item.group_id = group.id AND group_item.channel_id = :channel_id', [
                                            'channel_id' => $this->id,
                                        ])->all();
    }


    public function getRelationSiteMeterTree() {
        return $this->hasOne(SiteMeterTree::className(), ['meter_channel_id' => 'id']);
    }


    public function getRelationSiteMeterTreeChildrens() {
        return $this->hasMany(SiteMeterTree::className(), ['parent_meter_channel_id' => 'id']);
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


    public function getChannelName() {
        $subchannels = ArrayHelper::map($this->relationMeterSubchannels, 'id', 'channel');
        if(count($subchannels) > 1) {
            return Yii::t('common.meter', '{channel} ({subchannels})', [
                'channel' => $this->channel,
                'subchannels' => implode(', ', $subchannels),
            ]);
        }
        else {
            return $this->channel;
        }
    }


    public static function isDividedBy1000($channels_ids = []) {
        $is_divided_by_1000 = (new Query())
            ->select('type.is_divide_by_1000')
            ->from(Meter::tableName() . ' t')
            ->innerJoin(MeterType::tableName() . ' type', 'type.id = t.type_id')
            ->innerJoin(MeterChannel::tableName() . ' channel', 'channel.meter_id = t.id')
            ->andWhere(['in', 'channel.id', $channels_ids])
            ->scalar();
        return (bool)((int)$is_divided_by_1000);
    }


    private static function getActiveByIdsQuery($ids = []) {
        $query = (new Query())
            ->select('
			t.id,
			meter.name as meter_id,
		')
            ->from(MeterChannel::tableName() . ' t')
            ->innerJoin(Meter::tableName() . ' meter', 'meter.id = t.meter_id')
            ->andWhere(['t.status' => MeterChannel::STATUS_ACTIVE])
            ->andWhere(['in', 't.id', $ids])
            ->orderBy(['t.channel' => SORT_ASC]);
        return $query;
    }


    /**
     * @param array $ids
     * @return array
     */
    public static function getActiveByIds($ids = []) {
        $query = self::getActiveByIdsQuery($ids);
        return Yii::$app->db->cache(function ($db) use ($query) {
            return $query->createCommand($db)->queryAll();
        }, static::CACHE_DURATION);
    }
}