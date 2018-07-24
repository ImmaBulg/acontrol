<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * MeterChannelGroupItem is the class for the table "meter_channel_group_item".
 */
class MeterChannelGroupItem extends ActiveRecord
{
	public static function tableName()
	{
		return 'meter_channel_group_item';
	}

	public function rules()
	{
		return [
			[['group_id', 'channel_id'], 'required'],
			[['group_id', 'channel_id'], 'integer'],
		];
	}

	public function attributeLabels()
	{
		return [
			'id' => Yii::t('common.meter', 'ID'),
			'group_id' => Yii::t('common.meter', 'Channel group'),
			'channel_id' => Yii::t('common.meter', 'Channel ID'),
			'created_at' => Yii::t('common.meter', 'Created at'),
			'modified_at' => Yii::t('common.meter', 'Modified at'),
			'created_by' => Yii::t('common.meter', 'Created by'),
			'modified_by' => Yii::t('common.meter', 'Modified by'),

			'channel' => Yii::t('common.meter', 'Channel'),
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

	public function getRelationMeterChannelGroup()
	{
		return $this->hasOne(MeterChannelGroup::className(), ['id' => 'group_id']);
	}

	public function getRelationMeterChannel()
	{
		return $this->hasOne(MeterChannel::className(), ['id' => 'channel_id']);
	}
}
