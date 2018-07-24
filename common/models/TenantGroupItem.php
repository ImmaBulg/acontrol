<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * TenantGroupItem is the class for the table "tenant_group_item".
 */
class TenantGroupItem extends ActiveRecord
{
	public static function tableName()
	{
		return 'tenant_group_item';
	}

	public function rules()
	{
		return [
			[['group_id', 'tenant_id'], 'required'],
			[['group_id', 'tenant_id'], 'integer'],
		];
	}

	public function attributeLabels()
	{
		return [
			'group_id' => Yii::t('common.tenant', 'Tenant group'),
			'tenant_id' => Yii::t('common.tenant', 'Tenant'),
			'created_at' => Yii::t('common.tenant', 'Created at'),
			'modified_at' => Yii::t('common.tenant', 'Modified at'),
			'created_by' => Yii::t('common.tenant', 'Created by'),
			'modified_by' => Yii::t('common.tenant', 'Modified by'),
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

	public function getRelationTenantGroup()
	{
		return $this->hasOne(TenantGroup::className(), ['id' => 'group_id']);
	}

	public function getRelationTenant()
	{
		return $this->hasOne(Tenant::className(), ['id' => 'tenant_id']);
	}
}
