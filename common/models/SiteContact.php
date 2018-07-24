<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * SiteContact is the class for the table "site_contact".
 */
class SiteContact extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;
	
	public static function tableName()
	{
		return 'site_contact';
	}

	public function rules()
	{
		return [
			[['name', 'address', 'job', 'comment'], 'filter', 'filter' => 'strip_tags'],
			[['name', 'email', 'address', 'job', 'comment'], 'filter', 'filter' => 'trim'],
			[['site_id'], 'required'],
			[['site_id', 'user_id'], 'integer'],
			[['name', 'job', 'phone', 'cell_phone', 'fax'], 'string', 'max' => 255],
			[['address', 'comment'], 'string'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],	
		];
	}

	public function attributeLabels()
	{
		return [
			'site_id' => Yii::t('common.site', 'Site'),
			'user_id' => Yii::t('common.site', 'User'),
			'name' => Yii::t('common.site', 'Contact name'),
			'email' => Yii::t('common.site', 'Email'),
			'address' => Yii::t('common.site', 'Address'),
			'job' => Yii::t('common.site', 'Job'),
			'phone' => Yii::t('common.site', 'Phone'),
			'cell_phone' => Yii::t('common.site', 'Cell phone'),
			'fax' => Yii::t('common.site', 'Fax'),
			'comment' => Yii::t('common.site', 'Comment'),
			'status' => Yii::t('common.site', 'Status'),
			'created_at' => Yii::t('common.site', 'Created at'),
			'modified_at' => Yii::t('common.site', 'Modified at'),
			'created_by' => Yii::t('common.site', 'Created by'),
			'modified_by' => Yii::t('common.site', 'Modified by'),

			'site_name' => Yii::t('common.site', 'Site name'),
			'user_name' => Yii::t('common.site', 'User'),
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

	public function getRelationUser()
	{
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.site', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.site', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}
}
