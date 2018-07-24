<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * ApiKey is the class for the table "api_key".
 */
class ApiKey extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	public static function tableName()
	{
		return 'api_key';
	}

	public function rules()
	{
		return [
			[['api_key'], 'filter', 'filter' => 'strip_tags'],
			[['api_key'], 'filter', 'filter' => 'trim'],
			[['api_key'], 'required'],
			[['api_key'], 'string'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'id' => Yii::t('common.api', 'ID'),
			'api_key' => Yii::t('common.api', 'API key'),
			'status' => Yii::t('common.api', 'Status'),
			'created_at' => Yii::t('common.api', 'Created at'),
			'modified_at' => Yii::t('common.api', 'Modified at'),
			'created_by' => Yii::t('common.api', 'Created by'),
			'modified_by' => Yii::t('common.api', 'Modified by'),
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

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.api', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.api', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}
}
