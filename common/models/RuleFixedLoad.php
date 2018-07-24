<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * RuleFixedLoad is the class for the table "rule_fixed_load".
 */
class RuleFixedLoad extends ActiveRecord
{
	const USE_TYPE_MONEY = 1;
	const USE_TYPE_KWH_TAOZ = 2;
	const USE_TYPE_KWH_FIXED = 5;
	const USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT = 3;
	const USE_TYPE_FLAT_ADDITION_TOTAL_USAGE = 4;

	const USE_FREQUENCY_ONE_TIME = 1;
	const USE_FREQUENCY_ONGOING = 2;

	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	public static function tableName()
	{
		return 'rule_fixed_load';
	}

	public function rules()
	{
		return [
			[['name', 'description'], 'filter', 'filter' => 'strip_tags'],
			[['name', 'description'], 'filter', 'filter' => 'trim'],
			[['name', 'tenant_id'], 'required'],
			[['tenant_id', 'rate_type_id'], 'integer'],
			[['value', 'shefel', 'geva', 'pisga'], 'number'],
			[['name'], 'string', 'max' => 255],
			[['description'], 'string'],
			['use_type', 'default', 'value' => self::USE_TYPE_MONEY],
			['use_type', 'in', 'range' => array_keys(self::getListUseTypes()), 'skipOnEmpty' => true],
			['use_frequency', 'default', 'value' => self::USE_FREQUENCY_ONE_TIME],
			['use_frequency', 'in', 'range' => array_keys(self::getListUseFrequencies()), 'skipOnEmpty' => true],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('common.rule', 'Name'),
			'tenant_id' => Yii::t('common.rule', 'Tenant'),
			'rate_type_id' => Yii::t('common.rule', 'Rate type'),
			'use_type' => Yii::t('common.rule', 'Usage type'),
			'use_frequency' => Yii::t('common.rule', 'Usage frequency'),
			'value' => Yii::t('common.rule', 'Value'),
			'shefel' => Yii::t('common.rule', 'Shefel'),
			'geva' => Yii::t('common.rule', 'Geva'),
			'pisga' => Yii::t('common.rule', 'Pisga'),
			'description' => Yii::t('common.rule', 'Description'),
			'status' => Yii::t('common.rule', 'Status'),
			'created_at' => Yii::t('common.rule', 'Created at'),
			'modified_at' => Yii::t('common.rule', 'Modified at'),
			'created_by' => Yii::t('common.rule', 'Created by'),
			'modified_by' => Yii::t('common.rule', 'Modified by'),
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

	public function getRelationTenant()
	{
		return $this->hasOne(Tenant::className(), ['id' => 'tenant_id']);
	}

	public function getRelationRateType()
	{
		return $this->hasOne(RateType::className(), ['id' => 'rate_type_id']);
	}

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.rule', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.rule', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}

	public static function getListUseTypes()
	{
		return [
			self::USE_TYPE_MONEY => Yii::t('common.rule', 'Add flat amount.'),
            self::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT => Yii::t('common.rule', 'Add % of the total bill amount.'),
            self::USE_TYPE_KWH_FIXED => Yii::t('common.rule', 'Add flat amount of Kwh.'),
            self::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE => Yii::t('common.rule', 'Add % of total Kwh of the bill.'),
            /*self::USE_TYPE_KWH_TAOZ => Yii::t('common.rule', 'Kwh (TAOZ)'),
			self::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT => Yii::t('common.rule', 'Flat XX% addition from the total bill amount.'),*/
		];
	}

	public function getAliasUseType()
	{
		$list = self::getListUseTypes();
		return (isset($list[$this->use_type])) ? $list[$this->use_type] : $this->use_type;
	}

	public static function getListUseFrequencies()
	{
		return [
			self::USE_FREQUENCY_ONE_TIME => Yii::t('common.rule', 'One time'),
			self::USE_FREQUENCY_ONGOING => Yii::t('common.rule', 'Ongoing'),
		];
	}

	public function getAliasUseFrequency()
	{
		$list = self::getListUseFrequencies();
		return (isset($list[$this->use_frequency])) ? $list[$this->use_frequency] : $this->use_frequency;
	}
}
