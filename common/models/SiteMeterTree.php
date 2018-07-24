<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * SiteMeterTree is the class for the table "site_meter_tree".
 */
class SiteMeterTree extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	const LEVEL_START = 1;
	
	public static function tableName()
	{
		return 'site_meter_tree';
	}

	public function rules()
	{
		return [
			[['site_id', 'meter_id', 'meter_channel_id'], 'required'],
			[['site_id', 'meter_id', 'meter_channel_id', 'parent_meter_id', 'parent_meter_channel_id'], 'integer'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],	
		];
	}

	public function attributeLabels()
	{
		return [
			'site_id' => Yii::t('common.meter', 'Site ID'),
			'meter_id' => Yii::t('common.meter', 'Meter ID'),
			'meter_channel_id' => Yii::t('common.meter', 'Meter channel ID'),
			'parent_meter_id' => Yii::t('common.meter', 'Parent meter ID'),
			'parent_meter_channel_id' => Yii::t('common.meter', 'Parent meter channel ID'),
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

	public function getRelationSite()
	{
		return $this->hasOne(Site::className(), ['id' => 'site_id']);
	}

	public function getRelationMeter()
	{
		return $this->hasOne(Meter::className(), ['id' => 'meter_id']);
	}

	public function getRelationParentMeter()
	{
		return $this->hasOne(Meter::className(), ['id' => 'parent_meter_id']);
	}

	public function getRelationMeterChannel()
	{
		return $this->hasOne(MeterChannel::className(), ['id' => 'meter_channel_id']);
	}

	public function getRelationParentMeterChannel()
	{
		return $this->hasOne(MeterChannel::className(), ['id' => 'parent_meter_channel_id']);
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
