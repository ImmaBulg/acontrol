<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;
use common\components\behaviors\ToTimestampBehavior;

/**
 * Vat is the class for the table "vat".
 */
class Vat extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;

	public static function tableName()
	{
		return 'vat';
	}

	public function rules()
	{
		return [
			[['vat', 'start_date'], 'required'],
			[['vat'], 'number'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'id' => Yii::t('common.vat', 'ID'),
			'vat' => Yii::t('common.vat', 'Vat'),
			'start_date' => Yii::t('common.vat', 'Start date'),
			'end_date' => Yii::t('common.vat', 'End date'),
			'status' => Yii::t('common.vat', 'Status'),
			'created_at' => Yii::t('common.vat', 'Created at'),
			'modified_at' => Yii::t('common.vat', 'Modified at'),
			'created_by' => Yii::t('common.vat', 'Created by'),
			'modified_by' => Yii::t('common.vat', 'Modified by'),

			'modificator_name' => Yii::t('common.vat', 'Modified by'),
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
			[
				'class' => ToTimestampBehavior::className(),
				'attributes' => [
					'start_date',
					'end_date',
				],
			],
		];
	}

	public function getRelationUserModificator()
	{
		return $this->hasOne(User::className(), ['id' => 'modified_by']);
	}

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.vat', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.vat', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}
}
