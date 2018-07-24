<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * SiteIpAddress is the class for the table "site_ip_address".
 */
class SiteIpAddress extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;
	
	public static function tableName()
	{
		return 'site_ip_address';
	}

	public function rules()
	{
		return [
			[['site_id', 'ip_address'], 'required'],
			['site_id', 'integer'],
			['ip_address', 'ip'],
			['ip_address', 'string', 'max' => 255],
			[['ip_address'], 'unique', 'filter' => function($query) {
				return $query->andWhere(['site_id' => $this->site_id]);
			}],
			['is_main', 'default', 'value' => self::NO],
			['is_main', 'boolean'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],	
		];
	}

	public function attributeLabels()
	{
		return [
			'site_id' => Yii::t('common.site', 'Site'),
			'ip_address' => Yii::t('common.site', 'IP'),
			'is_main' => Yii::t('common.site', 'Is default'),
			'status' => Yii::t('common.site', 'Status'),
			'created_at' => Yii::t('common.site', 'Created at'),
			'modified_at' => Yii::t('common.site', 'Modified at'),
			'created_by' => Yii::t('common.site', 'Created by'),
			'modified_by' => Yii::t('common.site', 'Modified by'),
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
