<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;
use yii\web\ForbiddenHttpException;
use yii\behaviors\TimestampBehavior;

use common\components\rbac\Role;
use common\components\db\ActiveRecord;

/**
 * User is the class for the table "user".
 * @property  $id
 * @property  $name
 */
class User extends ActiveRecord implements IdentityInterface
{
	const AUTH_KEY_LENGTH = 32;
	const PASSWORD_RESET_TOKEN_LENGTH = 100;
	
	const PASSWORD_MIN_LENGTH = 5;
	const PASSWORD_MAX_LENGTH = 100;

	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;

	public static function tableName()
	{
		return 'user';
	}

	public function rules()
	{
		return [
			[['name'], 'filter', 'filter' => 'strip_tags'],
			[['name', 'nickname', 'email'], 'filter', 'filter' => 'trim'],
			[['name', 'email', 'nickname'], 'required'],
			['nickname', 'match', 'pattern' => '/^([0-9_a-zA-Z]+)$/'],
			[['nickname'], 'string', 'min' => 4, 'max' => 255],
			['nickname', 'unique', 'filter' => function($model){
				return $model->where('nickname = :nickname COLLATE utf8_bin', ['nickname' => $this->nickname])
				->andWhere(['!=', 'id', $this->id])
				->andWhere(['in', 'status', [
					self::STATUS_INACTIVE,
					self::STATUS_ACTIVE,
				]]);
			}],
			['password', 'string', 'min' => self::PASSWORD_MIN_LENGTH, 'max' => self::PASSWORD_MAX_LENGTH],
			[['name', 'email', 'password_reset_token', 'auth_key'], 'string', 'max' => 255],
			['role', 'in', 'range' => array_keys(Role::getListRoles()), 'skipOnEmpty' => false],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
			[['alert_notification_email'], 'default', 'value' => self::YES],
			[['alert_notification_email'], 'boolean'],
		];
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('common.user', 'Name'),
			'nickname' => Yii::t('common.user', 'Username'),
			'email' => Yii::t('common.user', 'Email'),
			'password' => Yii::t('common.user', 'Password'),
			'password_reset_token' => Yii::t('common.user', 'Password reset token'),
			'role' => Yii::t('common.user', 'Role'),
			'auth_key' => Yii::t('common.user', 'Authorization key'),
			'status' => Yii::t('common.user', 'Status'),
			'old_id' => Yii::t('common.user', 'Old ID'),
			'alert_notification_email' => Yii::t('common.user', 'Alert notifications by Email'),
			'created_at' => Yii::t('common.user', 'Created at'),
			'modified_at' => Yii::t('common.user', 'Modified at'),

			'address' => Yii::t('common.user', 'Address'),
			'job' => Yii::t('common.user', 'Job'),
			'phone' => Yii::t('common.user', 'Phone'),
			'fax' => Yii::t('common.user', 'Fax'),
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
		];
	}

	public function getRelationUserProfile()
	{
		return $this->hasOne(UserProfile::className(), ['user_id' => 'id']);
	}

	public function getRelationUserAlertNotifications()
	{
		return $this->hasMany(UserAlertNotification::className(), ['user_id' => 'id']);
	}

	public function getRelationSites()
	{
		return $this->hasMany(Site::className(), ['user_id' => 'id']);
	}

	public function getRelationTenants()
	{
		return $this->hasMany(Tenant::className(), ['user_id' => 'id']);
	}

	public function getRelationUserContacts()
	{
		return $this->hasMany(UserContact::className(), ['user_id' => 'id']);
	}

	public function getRelationUserOwners()
	{
		return $this->hasMany(UserOwner::className(), ['user_owner_id' => 'id']);
	}
	
	public function getRelationUserOwnerSites()
	{
		return $this->hasMany(UserOwnerSite::className(), ['user_owner_id' => 'id']);
	}
	
	public function getRelationUserOwnerTenants()
	{
		return $this->hasMany(UserOwnerTenant::className(), ['user_owner_id' => 'id']);
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

	public function getAliasRole()
	{
		$list = Role::getListRoles();
		return (isset($list[$this->role])) ? $list[$this->role] : $this->role;
	}

	public function validatePassword($password)
	{
		return Yii::$app->getSecurity()->validatePassword($password, $this->password);
	}

	public function generatePassword($password)
	{
		$this->password = Yii::$app->getSecurity()->generatePasswordHash($password);
	}

	public function deletePasswordResetToken()
	{
		$this->password_reset_token = null;
	}

	public function generatePasswordResetToken()
	{
		$this->password_reset_token = Yii::$app->getSecurity()->generateRandomString(self::PASSWORD_RESET_TOKEN_LENGTH) . '_' . time();
	}

	public static function findByPasswordResetToken($token)
	{
		$expire = Yii::$app->params['passwordResetTokenExpire'];
		$parts = explode('_', $token);
		$timestamp = (int) end($parts);
		
		if($timestamp + $expire < time())
		{
			// token expired
			return null;
		}
		
		return static::findOne([
			'password_reset_token' => $token,
			'status' => self::STATUS_ACTIVE,
		]);
	}

	public function getId()
	{
		return $this->getPrimaryKey();
	}

	public static function findIdentity($id)
	{
		return static::findOne($id);
	}

	public static function findIdentityByAccessToken($token, $type = null)
	{
		throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	}

	public function getAuthKey()
	{
		return $this->auth_key;
	}

	public function generateAuthKey()
	{
		$this->auth_key = Yii::$app->getSecurity()->generateRandomString(self::AUTH_KEY_LENGTH);
	}

	public function deleteAuthKey()
	{
		$this->auth_key = null;
	}

	public function validateAuthKey($authKey)
	{
		return ($authKey === $this->auth_key);
	}

	public function generateNickname($value)
	{
		//preg_replace('/([^@]*).*/', '$1', $email)
		$this->nickname = preg_replace('/[^a-zA-Z0-9_]/', '_', $value);
	}

	public static function getListClients()
	{
		$rows = (new Query())->from(self::tableName())
		->andWhere(['role' => Role::ROLE_CLIENT]);
        if(!Yii::$app->user->can('SiteController.actionCreate')) {
			if(Yii::$app->user->can('SiteController.actionCreateOwner')) {
				$users_model = Yii::$app->user->identity->relationUserOwners;
				$user_ids = ArrayHelper::getColumn($users_model, 'user_id');
				array_unshift($user_ids, Yii::$app->user->id);
				$rows->andWhere(['id' => $user_ids]);
			} elseif(Yii::$app->user->can('SiteController.actionCreateSiteOwner')) {
				$sites = UserOwnerSite::find()->where(['user_owner_id' => Yii::$app->user->id])->all();
				$site_ids = ArrayHelper::getColumn($sites, 'site_id');
				$rows->andWhere(['t.id' => $site_ids]);
			} elseif(!Yii::$app->user->can('SiteController.actionEdit')) {
				$id_site = Yii::$app->request->getQueryParam('id');
				$model_site = Site::loadSite($id_site);
				switch (true) {
					case Yii::$app->user->can('SiteController.actionEditOwner', ['model' => $model_site]):
						$users_model = Yii::$app->user->identity->relationUserOwners;
						$user_ids = ArrayHelper::getColumn($users_model, 'user_id');
						array_unshift($user_ids, Yii::$app->user->id);
						$rows->andWhere([self::tableName(). '.id' => $user_ids]);
						break;
					case Yii::$app->user->can('SiteController.actionEditSiteOwner', ['model' => $model_site]):
						$rows->andWhere([self::tableName(). '.id' => $model_site->user_id]);
						break;
					default :
						throw new ForbiddenHttpException('Access denied for User::getListClients()');
				}
			}
		}
		$rows = $rows->all();
		return ArrayHelper::map($rows, 'id', 'name');
	}

	public static function getListByRole($role)
	{
		return ArrayHelper::map((new Query())->from(self::tableName())
		->andWhere(['role' => $role])->andWhere(['in', 'status', [
			self::STATUS_INACTIVE,
			self::STATUS_ACTIVE,
		]])->all(), 'id', 'name');
	}

	public function isSuperClient()
	{
		return $this->getRelationUserOwners()->count();
	}
}
