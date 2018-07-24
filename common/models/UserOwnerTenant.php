<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

use yii\behaviors\TimestampBehavior;
use common\components\behaviors\UserIdBehavior;

/**
 * This is the model class for table "user_owner_tenant".
 *
 * @property integer $id
 * @property integer $tenant_id
 * @property integer $user_owner_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $modified_at
 * @property integer $created_by
 * @property integer $modified_by
 *
 * @property User $modifiedBy
 * @property User $createdBy
 * @property Tenant $tenant
 * @property User $userOwner
 */
class UserOwnerTenant extends \yii\db\ActiveRecord
{
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 2;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_owner_tenant';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tenant_id', 'user_owner_id'], 'required'],
            [['tenant_id', 'user_owner_id'], 'integer'],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],		
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tenant_id' => 'Tenant ID',
            'user_owner_id' => 'User Owner ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'modified_at' => 'Modified At',
            'created_by' => 'Created By',
            'modified_by' => 'Modified By',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModifiedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'modified_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTenant()
    {
        return $this->hasOne(Tenant::className(), ['id' => 'tenant_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserOwner()
    {
        return $this->hasOne(User::className(), ['id' => 'user_owner_id']);
    }
	
	public static function getListStatuses()
	{
		return [
			self::STATUS_INACTIVE => Yii::t('common.user', 'Inactive'),
			self::STATUS_ACTIVE => Yii::t('common.user', 'Active'),
		];
	}

	public function getAliasStatus()
	{
		$list = self::getListStatuses();
		return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
	}
	
	public static function getListTenants()
	{
		$query = (new Query())->from(Tenant::tableName(). ' t')
		->andWhere(['in', 't.status', [
			Tenant::STATUS_ACTIVE,
		]]);

		$rows = $query->orderBy(['t.name' => SORT_ASC])->all();
		return ArrayHelper::map($rows, 'id', 'name');
	}
}
