<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * TenantGroup is the class for the table "tenant_group".
 */
class TenantGroup extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	public static function tableName()
	{
		return 'tenant_group';
	}

	public function rules()
	{
		return [
			[['name'], 'filter', 'filter' => 'strip_tags'],
			[['name'], 'filter', 'filter' => 'trim'],
			[['name', 'user_id', 'site_id'], 'required'],
			[['user_id', 'site_id'], 'integer'],
			[['name'], 'string', 'max' => 255],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('common.tenant', 'Client'),
			'site_id' => Yii::t('common.tenant', 'Site'),
			'name' => Yii::t('common.tenant', 'Name'),
			'status' => Yii::t('common.tenant', 'Status'),
			'created_at' => Yii::t('common.tenant', 'Created at'),
			'modified_at' => Yii::t('common.tenant', 'Modified at'),
			'created_by' => Yii::t('common.tenant', 'Created by'),
			'modified_by' => Yii::t('common.tenant', 'Modified by'),

			'user_name' => Yii::t('common.tenant', 'Client name'),
			'site_name' => Yii::t('common.tenant', 'Site name'),
			'group_tenants' => Yii::t('common.tenant', 'Tenants in group'),
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

	public function getRelationSite()
	{
		return $this->hasOne(Site::className(), ['id' => 'site_id']);
	}

	public function getRelationTenantGroupItems()
	{
		return $this->hasMany(TenantGroupItem::className(), ['group_id' => 'id']);
	}

	public function getRelationRuleGroupLoad()
	{
		return $this->hasOne(RuleGroupLoad::className(), ['tenant_group_id' => 'id']);
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

	public static function getListSites($user_id = null, $site_id = null)
	{
		$query = (new Query())->select('t.id, t.name')->from(Site::tableName(). ' t')
		->innerJoin(Tenant::tableName(). ' tenant', 'tenant.site_id = t.id')
		->andWhere([
			't.status' => Site::STATUS_ACTIVE,
		]);

		if (!is_null($site_id)) {
			$query->andWhere(['t.id' => $site_id]);
		}
		if (!is_null($user_id)) {
			$query->andWhere(['t.user_id' => $user_id]);
		}

		$rows = $query->orderBy(['t.name' => SORT_ASC])->groupBy(['t.id'])->all();
		return ArrayHelper::map($rows, 'id', 'name');
	}

	public static function getListByTenantId($id)
	{
		return ArrayHelper::map((new Query())->select('t.id, t.name')->from(self::tableName(). ' t')
		->innerJoin(TenantGroupItem::tableName(). ' item', 'item.group_id = t.id')
		->andWhere(['item.tenant_id' => $id])->andWhere(['in', 't.status', [
			self::STATUS_INACTIVE,
			self::STATUS_ACTIVE,
		]])->groupBy('t.id')->all(), 'id', 'name');
	}
}
