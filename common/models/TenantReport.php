<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * TenantReport is the class for the table "tenant_report".
 * @property $tenant_id
 * @property $report_id
 */
class TenantReport extends ActiveRecord
{
	public static function tableName()
	{
		return 'tenant_report';
	}

	public function rules()
	{
		return [
			[['report_id', 'tenant_id'], 'required'],
			[['report_id', 'tenant_id'], 'integer'],
		];
	}

	public function attributeLabels()
	{
		return [
			'report_id' => Yii::t('common.tenant', 'Report'),
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

	public function getRelationReport()
	{
		return $this->hasOne(Report::className(), ['id' => 'report_id']);
	}

	public function getRelationTenant()
	{
		return $this->hasOne(Tenant::className(), ['id' => 'tenant_id']);
	}
}
