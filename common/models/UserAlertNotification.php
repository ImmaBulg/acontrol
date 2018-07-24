<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

use common\components\rbac\Role;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * UserAlertNotification is the class for the table "user_alert_notification".
 */
class UserAlertNotification extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	public static function tableName()
	{
		return 'user_alert_notification';
	}

	public function rules()
	{
		return [
			[['user_id', 'site_id'], 'required'],
			[['user_id', 'site_id'], 'integer'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],			
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('common.user', 'User'),
			'site_id' => Yii::t('common.user', 'Site'),
			'status' => Yii::t('common.user', 'Status'),
			'created_at' => Yii::t('common.user', 'Created at'),
			'modified_at' => Yii::t('common.user', 'Modified at'),
			'created_by' => Yii::t('common.user', 'Created by'),
			'modified_by' => Yii::t('common.user', 'Modified by'),

			'site_owner_name' => Yii::t('common.user', 'Client name'),
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

	public function getRelationSiteOwner()
	{
		return $this->hasOne(User::className(), ['id' => 'site_owner_id']);
	}

	public function getRelationSite()
	{
		return $this->hasOne(Site::className(), ['id' => 'site_id']);
	}

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.user', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.user', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}
}
