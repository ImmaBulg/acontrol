<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * TenantContact is the class for the table "tenant_contact".
 */
class TenantContact extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;
	
	public static function tableName()
	{
		return 'tenant_contact';
	}

	public function rules()
	{
		return [
			[['name', 'address', 'job', 'comment'], 'filter', 'filter' => 'strip_tags'],
			[['name', 'email', 'address', 'job', 'comment'], 'filter', 'filter' => 'trim'],
			[['tenant_id'], 'required'],
			[['tenant_id', 'user_id'], 'integer'],
			[['name', 'job', 'phone', 'cell_phone', 'fax'], 'string', 'max' => 255],
			[['address', 'comment'], 'string'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],	
		];
	}

	public function attributeLabels()
	{
		return [
			'tenant_id' => Yii::t('common.tenant', 'Tenant'),
			'user_id' => Yii::t('common.tenant', 'User'),
			'name' => Yii::t('common.tenant', 'Contact name'),
			'email' => Yii::t('common.tenant', 'Email'),
			'address' => Yii::t('common.tenant', 'Address'),
			'job' => Yii::t('common.tenant', 'Job'),
			'phone' => Yii::t('common.tenant', 'Phone'),
			'cell_phone' => Yii::t('common.tenant', 'Cell phone'),
			'fax' => Yii::t('common.tenant', 'Fax'),
			'comment' => Yii::t('common.tenant', 'Comment'),
			'status' => Yii::t('common.tenant', 'Status'),
			'created_at' => Yii::t('common.tenant', 'Created at'),
			'modified_at' => Yii::t('common.tenant', 'Modified at'),
			'created_by' => Yii::t('common.tenant', 'Created by'),
			'modified_by' => Yii::t('common.tenant', 'Modified by'),

			'site_name' => Yii::t('common.tenant', 'Site name'),
			'tenant_name' => Yii::t('common.tenant', 'Tenant name'),
			'user_name' => Yii::t('common.tenant', 'User'),
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
			self::STATUS_INACTIVE => Yii::t('common.tenant', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.tenant', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}

	public function getRelationTenant()
	{
		return $this->hasOne(Tenant::className(), ['id' => 'tenant_id']);
	}

	public function getRelationUser()
	{
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}
}
