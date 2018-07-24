<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;
use common\components\behaviors\ToTimestampBehavior;

/**
 * TaskComment is the class for the table "task_comment".
 */
class TaskComment extends ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;
	
	public static function tableName()
	{
		return 'task_comment';
	}

	public function rules()
	{
		return [
			[['description'], 'filter', 'filter' => 'strip_tags'],
			[['description'], 'filter', 'filter' => 'trim'],
			[['task_id', 'description'], 'required'],
			[['task_id'], 'integer'],
			[['description'], 'string'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'task_id' => Yii::t('common.task', 'Task ID'),
			'description' => Yii::t('common.task', 'Description'),
			'status' => Yii::t('common.task', 'Status'),
			'created_at' => Yii::t('common.task', 'Created at'),
			'modified_at' => Yii::t('common.task', 'Modified at'),
			'created_by' => Yii::t('common.task', 'Created by'),
			'modified_by' => Yii::t('common.task', 'Modified by'),
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

	public function getRelationTask()
	{
		return $this->hasOne(Task::className(), ['id' => 'task_id']);
	}

	public function getRelationUserCreator()
	{
		return $this->hasOne(User::className(), ['id' => 'created_by']);
	}

	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.task', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.task', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}
}
