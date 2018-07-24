<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * MeterChannelGroup is the class for the table "meter_channel_group".
 */
class MeterChannelGroup extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	public static function tableName()
	{
		return 'meter_channel_group';
	}

	public function rules()
	{
		return [
			[['name'], 'filter', 'filter' => 'strip_tags'],
			[['name'], 'filter', 'filter' => 'trim'],
			[['name', 'meter_id', 'user_id', 'site_id'], 'required'],
			[['meter_id', 'user_id', 'site_id'], 'integer'],
			[['name'], 'string', 'max' => 255],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'id' => Yii::t('common.meter-group', 'ID'),
			'user_id' => Yii::t('common.meter-group', 'Client'),
			'site_id' => Yii::t('common.meter-group', 'Site'),
			'meter_id' => Yii::t('common.meter-group', 'Meter ID'),
			'name' => Yii::t('common.meter-group', 'Name'),
			'status' => Yii::t('common.meter-group', 'Status'),
			'created_at' => Yii::t('common.meter-group', 'Created at'),
			'modified_at' => Yii::t('common.meter-group', 'Modified at'),
			'created_by' => Yii::t('common.meter-group', 'Created by'),
			'modified_by' => Yii::t('common.meter-group', 'Modified by'),

			'user_name' => Yii::t('common.meter-group', 'Client name'),
			'site_name' => Yii::t('common.meter-group', 'Site name'),
			'meter_name' => Yii::t('common.meter-group', 'Meter ID'),
			'group_channels' => Yii::t('common.meter-group', 'Channels in group'),
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

	public function getRelationUser()
	{
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}

	public function getRelationSite()
	{
		return $this->hasOne(Site::className(), ['id' => 'site_id']);
	}

	public function getRelationMeter()
	{
		return $this->hasOne(Meter::className(), ['id' => 'meter_id']);
	}

	public function getRelationMeterChannelGroupItems()
	{
		return $this->hasMany(MeterChannelGroupItem::className(), ['group_id' => 'id']);
	}

	public function getRelationRuleGroupLoad()
	{
		return $this->hasOne(RuleGroupLoad::className(), ['channel_group_id' => 'id']);
	}

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.meter-group', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.meter-group', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}
}
