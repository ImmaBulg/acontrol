<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * UserProfile is the class for the table "user_profile".
 */
class UserProfile extends ActiveRecord
{
	public static function tableName()
	{
		return 'user_profile';
	}

	public function rules()
	{
		return [
			[['address', 'job', 'comment'], 'filter', 'filter' => 'strip_tags'],
			[['address', 'job', 'comment'], 'filter', 'filter' => 'trim'],
			[['user_id'], 'required'],
			['user_id', 'integer'],
			['phone', 'match', 'pattern' => self::FAX_VALIDATION_PATTERN],
			['fax', 'match', 'pattern' => self::FAX_VALIDATION_PATTERN],
			[['job', 'phone', 'fax'], 'string', 'max' => 255],
			[['address', 'comment'], 'string'],
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('common.user', 'User ID'),
			'address' => Yii::t('common.user', 'Address'),
			'job' => Yii::t('common.user', 'Job'),
			'phone' => Yii::t('common.user', 'Phone'),
			'fax' => Yii::t('common.user', 'Fax'),
			'comment' => Yii::t('common.user', 'Comment'),
			'created_at' => Yii::t('common.user', 'Created at'),
			'modified_at' => Yii::t('common.user', 'Modified at'),
			'created_by' => Yii::t('common.user', 'Created by'),
			'modified_by' => Yii::t('common.user', 'Modified by'),
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
}
