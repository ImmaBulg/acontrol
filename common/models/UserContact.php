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
 * UserContact is the class for the table "user_contact".
 */
class UserContact extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	public static function tableName()
	{
		return 'user_contact';
	}

	public function rules()
	{
		return [
			[['name', 'address', 'job', 'comment'], 'filter', 'filter' => 'strip_tags'],
			[['name', 'email', 'address', 'job', 'comment'], 'filter', 'filter' => 'trim'],
			[['user_id'], 'required'],
			[['user_id'], 'integer'],
			[['name', 'job', 'phone', 'cell_phone', 'fax'], 'string', 'max' => 255],
			[['address', 'comment'], 'string'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],			
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('common.user', 'Client'),
			'name' => Yii::t('common.user', 'Contact name'),
			'email' => Yii::t('common.user', 'Email'),
			'address' => Yii::t('common.user', 'Address'),
			'job' => Yii::t('common.user', 'Job'),
			'phone' => Yii::t('common.user', 'Phone'),
			'cell_phone' => Yii::t('common.user', 'Cell phone'),
			'fax' => Yii::t('common.user', 'Fax'),
			'comment' => Yii::t('common.user', 'Comment'),
			'status' => Yii::t('common.user', 'Status'),
			'created_at' => Yii::t('common.user', 'Created at'),
			'modified_at' => Yii::t('common.user', 'Modified at'),
			'created_by' => Yii::t('common.user', 'Created by'),
			'modified_by' => Yii::t('common.user', 'Modified by'),

			'user_name' => Yii::t('common.user', 'Client name'),
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
