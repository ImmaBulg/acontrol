<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * SiteBillingSetting is the class for the table "site_billing_setting".
 *
 * @property $include_vat
 * @property $billing_day
 * @property $fixed_payment
 * @property $rate_type_id
 * @property $site_id
 * @property $comment
 * @property $fixed_addition_type
 * @property $fixed_addition_comment
 * @property $fixed_addition_value
 * @property $fixed_addition_load
 * @property RateType $relationRateType
 * @property string $irregular_hours_from
 * @property string $irregular_hours_to
 * @property float $irregular_additional_percent
 */
class SiteBillingSetting extends ActiveRecord
{
	const FIXED_ADDITION_TYPE_MONEY = 1;
	const FIXED_ADDITION_TYPE_KWH = 2;

	const FIXED_ADDITION_LOAD_FLAT = 1;
	const FIXED_ADDITION_LOAD_PERCENTAGE = 2;

	public static function tableName()
	{
		return 'site_billing_setting';
	}

    public static function primaryKey()
    {
        return ['site_id'];
    }

	public function rules()
	{
		return [
			[['comment', 'fixed_addition_comment'], 'filter', 'filter' => 'strip_tags'],
			[['comment', 'fixed_addition_comment'], 'filter', 'filter' => 'trim'],
			[['site_id'], 'required'],
			[['site_id', 'rate_type_id', 'billing_day'], 'integer'],
			[['fixed_payment'], 'number', 'min' => 0],
			[['fixed_payment'], 'compare', 'compareValue' => 0, 'operator' => '>='],
			[['fixed_addition_value'], 'number'],
			[['include_vat'], 'default', 'value' => self::NO],
			[['include_vat'], 'boolean'],
			[['comment', 'fixed_addition_comment'], 'string'],
			['fixed_addition_type', 'in', 'range' => array_keys(self::getListFixedAdditionTypes()), 'skipOnEmpty' => true],
			['fixed_addition_load', 'in', 'range' => array_keys(self::getListFixedAdditionLoads()), 'skipOnEmpty' => true],
            ['irregular_additional_percent', 'number'],
            [['irregular_hours_from', 'irregular_hours_to'], 'string'],
		];
	}

	public function attributeLabels()
	{
		return [
			'site_id' => Yii::t('common.site', 'Site ID'),
			'rate_type_id' => Yii::t('common.site', 'Rate type'),
			'fixed_payment' => Yii::t('common.site', 'Fixed payment'),
			'billing_day' => Yii::t('common.site', 'Day of billing'),
			'include_vat' => Yii::t('common.site', 'Include VAT'),
			'comment' => Yii::t('common.site', 'Comment'),
			'fixed_addition_type' => Yii::t('common.site', 'Fixed addition of'),
			'fixed_addition_load' => Yii::t('common.site', 'Load as'),
			'fixed_addition_value' => Yii::t('common.site', 'Value (money, kwh or percentage)'),
			'fixed_addition_comment' => Yii::t('common.site', 'Comment for fixed addition'),
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

	public static function getListFixedAdditionTypes()
	{
		return [
			self::FIXED_ADDITION_TYPE_MONEY => Yii::t('common.site', 'Money'),
			self::FIXED_ADDITION_TYPE_KWH => Yii::t('common.site', 'Kwh'),
		];
	}

	public function getAliasFixedAdditionType()
	{
		$list = self::getListFixedAdditionTypes();
		return (isset($list[$this->fixed_addition_type])) ? $list[$this->fixed_addition_type] : null;
	}

	public static function getListFixedAdditionLoads()
	{
		return [
			self::FIXED_ADDITION_LOAD_FLAT => Yii::t('common.site', 'Flat amount'),
			self::FIXED_ADDITION_LOAD_PERCENTAGE => Yii::t('common.site', 'Percentage'),
		];
	}

	public function getAliasFixedAdditionLoad()
	{
		$list = self::getListFixedAdditionLoads();
		return (isset($list[$this->fixed_addition_load])) ? $list[$this->fixed_addition_load] : null;
	}

	public function getRelationSite()
	{
		return $this->hasOne(Site::className(), ['id' => 'site_id']);
	}

	public function getRelationRateType()
	{
		return $this->hasOne(RateName::className(), ['id' => 'rate_type_id']);
	}

	public function getAliasRateType()
	{
		if (($rate_type = $this->relationRateType) != null) {
			return $rate_type->name;
		}
	}

	public static function getListBillingDays()
	{
		for ($i = 1; $i <= 31; $i++) { 
			$result[$i] = $i;
		}

		return $result;
	}

	public function getAliasBillingDay()
	{
		$list = self::getListBillingDays();
		return (isset($list[$this->billing_day])) ? $list[$this->billing_day] : $this->billing_day;
	}

    public function getIrregularHoursFrom() {
        return $this->irregular_hours_from;

    }


    public function getIrregularHoursTo() {
        return $this->irregular_hours_to;
    }
}
