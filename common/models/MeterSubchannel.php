<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * MeterSubchannel is the class for the table "meter_subchannel".
 */
class MeterSubchannel extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	public static function tableName()
	{
		return 'meter_subchannel';
	}

	public function rules()
	{
		return [
			[['meter_id', 'channel_id', 'channel'], 'required'],
			[['meter_id', 'channel_id', 'channel'], 'integer'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'id' => Yii::t('common.meter', 'ID'),
			'meter_id' => Yii::t('common.meter', 'Meter ID'),
			'channel_id' => Yii::t('common.meter', 'Channel ID'),
			'channel' => Yii::t('common.meter', 'Channel'),
			'status' => Yii::t('common.meter', 'Status'),
			'created_at' => Yii::t('common.meter', 'Created at'),
			'modified_at' => Yii::t('common.meter', 'Modified at'),
			'created_by' => Yii::t('common.meter', 'Created by'),
			'modified_by' => Yii::t('common.meter', 'Modified by'),
		];
	}

	public function behaviors()
	{
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

	public function getRelationMeter()
	{
		return $this->hasOne(Meter::className(), ['id' => 'meter_id']);
	}

	public function getRelationMeterChannel()
	{
		return $this->hasOne(MeterChannel::className(), ['id' => 'channel_id']);
	}

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.meter', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.meter', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}
}
