<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * RuleGroupLoad is the class for the table "rule_group_load".
 * @property $tenant_id
 * @property $name
 * @property $channel_id
 * @property $channel_group_id
 * @property $tennant_group_id
 * @property $usage_tenant_group_id
 * @property $percent
 * @property $flat_percent
 */
class RuleGroupLoad extends ActiveRecord
{
	const USE_TYPE_SINGLE_METER_GROUP_LOAD = 1;
	const USE_TYPE_SINGLE_METER_LOAD = 2;
	const USE_TYPE_SINGLE_TENANT_GROUP_LOAD = 3;

	const USE_PERCENT_FOOTAGE = 1;
	const USE_PERCENT_USAGE = 2;
	const USE_PERCENT_FLAT = 3;

	const TOTAL_BILL_ACTION_PLUS = 1;
	const TOTAL_BILL_ACTION_MINUS = 2;

	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	public static function tableName()
	{
		return 'rule_group_load';
	}

	public function rules()
	{
		return [
			[['name'], 'required'],
			[['name'], 'string', 'max' => 255],
			[['channel_id', 'channel_group_id', 'tenant_group_id', 'usage_tenant_group_id'], 'integer'],
			[['percent'], 'number', 'min' => 0, 'max' => 100],
			['use_type', 'default', 'value' => self::USE_TYPE_SINGLE_METER_GROUP_LOAD],
			['use_type', 'in', 'range' => array_keys(self::getListUseTypes()), 'skipOnEmpty' => true],
			['use_percent', 'default', 'value' => self::USE_PERCENT_FOOTAGE],
			['use_percent', 'in', 'range' => array_keys(self::getListUsePercents()), 'skipOnEmpty' => true],
			['total_bill_action', 'default', 'value' => self::TOTAL_BILL_ACTION_PLUS],
			['total_bill_action', 'in', 'range' => array_keys(self::getListTotalBillActions()), 'skipOnEmpty' => true],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('common.rule', 'Name'),
			'total_bill_action' => Yii::t('common.rule', 'Action'),
			'use_type' => Yii::t('common.rule', 'Usage type'),
			'use_percent' => Yii::t('common.rule', 'Usage percentage'),
			'percent' => Yii::t('common.rule', 'Percentage'),
			'usage_tenant_group_id' => Yii::t('common.rule', 'Usage tenant group ID'),
			'channel_id' => Yii::t('common.rule', 'Channel'),
			'channel_group_id' => Yii::t('common.rule', 'Channel group'),
			'tenant_group_id' => Yii::t('common.rule', 'Tenant group'),
			'status' => Yii::t('common.rule', 'Status'),
			'created_at' => Yii::t('common.rule', 'Created at'),
			'modified_at' => Yii::t('common.rule', 'Modified at'),
			'created_by' => Yii::t('common.rule', 'Created by'),
			'modified_by' => Yii::t('common.rule', 'Modified by'),

			'group_name' => Yii::t('common.rule', 'Group'),
			'meter_name' => Yii::t('common.rule', 'Meter ID'),
			'channel_name' => Yii::t('common.rule', 'Channel'),
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

	public function getRelationMeterChannel()
	{
		return $this->hasOne(MeterChannel::className(), ['id' => 'channel_id']);
	}

	public function getRelationMeterChannelGroup()
	{
		return $this->hasOne(MeterChannelGroup::className(), ['id' => 'channel_group_id']);
	}

	public function getRelationTenantGroup()
	{
		return $this->hasOne(TenantGroup::className(), ['id' => 'tenant_group_id']);
	}

	public function getRelationTenantGroupUsage()
	{
		return $this->hasOne(TenantGroup::className(), ['id' => 'usage_tenant_group_id']);
	}

	public static function getListTotalBillActions()
	{
		return [
			self::TOTAL_BILL_ACTION_PLUS => '+',
			self::TOTAL_BILL_ACTION_MINUS => '-',
		];
	}

	public function getAliasTotalBillAction()
	{
		$list = self::getListTotalBillActions();
		return (isset($list[$this->total_bill_action])) ? $list[$this->total_bill_action] : $this->total_bill_action;
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
			self::USE_TYPE_SINGLE_METER_LOAD => Yii::t('common.rule', 'Single meter load'),
			self::USE_TYPE_SINGLE_METER_GROUP_LOAD => Yii::t('common.rule', 'Single meter group load'),
			self::USE_TYPE_SINGLE_TENANT_GROUP_LOAD => Yii::t('common.rule', 'Single tenant group load'),
		];
	}

	public function getAliasUseType()
	{
		$list = self::getListUseTypes();
		return (isset($list[$this->use_type])) ? $list[$this->use_type] : $this->use_type;
	}

	public static function getListUsePercents()
	{
		return [
			self::USE_PERCENT_FOOTAGE => Yii::t('common.rule', "Relative to tenant's footage"),
			self::USE_PERCENT_USAGE => Yii::t('common.rule', "Relative to tenant's usage"),
            self::USE_PERCENT_FLAT => Yii::t('common.rule', 'Flat percent'),
 		];
	}

	public function getAliasUsePercent()
	{
		$list = self::getListUsePercents();
		return (isset($list[$this->use_percent])) ? $list[$this->use_percent] : $this->use_percent;
	}

	public static function getListMeterChannels($meter_id, $channel_id = null)
	{
		$list = [];
		$models = MeterChannel::find()->where([
			'meter_id' => $meter_id,
			'status' => MeterChannel::STATUS_ACTIVE,
		])->all();

		if ($models != null) {
			foreach ($models as $model) {
				if (!is_null($channel_id)) {
					$rule->andWhere(self::tableName(). '.channel_id != :channel_id', ['channel_id' => $channel_id]);
				}

				$list[$model->id] = $model->getChannelName();
			}
		}

		return $list;
	}

	public static function getListChannelGroups($site_id)
	{
		$list = [];
		$models = MeterChannelGroup::find()->where([
			'site_id' => $site_id,
			'status' => MeterChannelGroup::STATUS_ACTIVE,
		])->all();

		if ($models != null) {
			foreach ($models as $model) {
				$list[$model->id] = $model->name;
			}
		}

		return $list;
	}

	public static function getListTenantGroups($site_id)
	{
		$list = [];
		$models = TenantGroup::find()->where([
			'site_id' => $site_id,
			'status' => TenantGroup::STATUS_ACTIVE,
		])->all();

		if ($models != null) {
			foreach ($models as $model) {
				$list[$model->id] = $model->name;
			}
		}

		return $list;
	}
}
