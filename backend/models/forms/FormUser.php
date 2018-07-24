<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\models\UserProfile;
use common\models\UserAlertNotification;
use common\models\UserOwner;
use common\models\UserOwnerSite;
use common\models\UserOwnerTenant;
use common\models\Site;
use common\components\rbac\Role;
use common\models\events\logs\EventLogUser;

/**
 * FormUser is the class for user create/edit.
 */
class FormUser extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $name;
	public $nickname;
	public $email;
	public $role;
	public $password;
	public $password_repeat;
	public $address;
	public $phone;
	public $fax;
	public $job;
	public $comment;
	public $alert_notification_email;
	public $alert_notifications;
	public $users;
	public $sites;
	public $tenants;
	public $status;


	public function rules()
	{
		return [
			[['name', 'address', 'job', 'comment'], 'filter', 'filter' => 'strip_tags'],
			[['name', 'email', 'nickname', 'address', 'job', 'comment'], 'filter', 'filter' => 'trim'],
			[['name', 'nickname', 'email', 'role'], 'required'],
			['nickname', 'match', 'pattern' => '/^([0-9_a-zA-Z]+)$/'],
			[['nickname'], 'string', 'min' => 4, 'max' => 255],
			['email', 'email'],
			[['password', 'password_repeat'], 'string', 'min' => User::PASSWORD_MIN_LENGTH, 'max' => User::PASSWORD_MAX_LENGTH],
			['password_repeat', 'compare', 'compareAttribute' => 'password'],
			['role', 'in', 'range' => array_keys(Role::getAliasAllowedRoles()), 'skipOnEmpty' => false],
			['phone', 'match', 'pattern' => UserProfile::FAX_VALIDATION_PATTERN],
			['fax', 'match', 'pattern' => UserProfile::FAX_VALIDATION_PATTERN],
			[['job', 'phone', 'fax'], 'string', 'max' => 255],
			[['address', 'comment'], 'string'],
			['alert_notification_email', 'default', 'value' => false],
			['alert_notification_email', 'boolean'],
			['alert_notifications', 'validateAlertNotifications'],
			['users', 'validateUsers'],
			['sites', 'validateSites'],
			['tenants', 'validateTenants'],
			['status', 'default', 'value' => User::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(User::getListStatuses()), 'skipOnEmpty' => true],

			// On scenario create
			[['password', 'password_repeat'], 'required', 'on' => self::SCENARIO_CREATE],
			['nickname', 'unique', 'targetClass' => '\common\models\User', 'filter' => function($model){
				return $model->where('nickname = :nickname COLLATE utf8_bin', ['nickname' => $this->nickname])
				->andWhere(['in', 'status', [
					User::STATUS_INACTIVE,
					User::STATUS_ACTIVE,
				]]);
			}, 'on' => self::SCENARIO_CREATE],

			// On scenario edit
			['nickname', 'unique', 'targetClass' => '\common\models\User', 'filter' => function($model){
				return $model->where('nickname = :nickname COLLATE utf8_bin', ['nickname' => $this->nickname])
				->andWhere('id != :id', ['id' => $this->_id])
				->andWhere(['in', 'status', [
					User::STATUS_INACTIVE,
					User::STATUS_ACTIVE,
				]]);
			}, 'on' => self::SCENARIO_EDIT],
		];
	}

	public function validateAlertNotifications($attribute, $params)
	{
		$values = (array) $this->$attribute;

		$count = Site::find()->where(['in', 'id', array_values($values)])
		->andWhere(['in', 'status', [
			Site::STATUS_ACTIVE,
		]])->count();

		if (count($values) != $count) {
			return $this->addError($attribute, Yii::t('backend.user', '{attribute} is invalid.', [
				'attribute' => $this->getAttributeLabel($attribute),
			]));
		}
	}

	public function validateUsers($attribute, $params)
	{
		$values = (array) $this->$attribute;

		$query = User::find()->where(['in', 'id', array_values($values)]);

		if ($this->_id != null) {
			$query->andWhere('id != :id', ['id' => $this->_id]);
		}

		$count = $query->count();

		if (count($values) != $count) {
			return $this->addError($attribute, Yii::t('backend.user', '{attribute} is invalid.', [
				'attribute' => $this->getAttributeLabel($attribute),
			]));
		}
	}
	
	public function validateTenants($attribute, $params)
	{
		return true;
		$values = (array) $this->$attribute;

		$query = User::find()->where(['in', 'id', array_values($values)]);

		if ($this->_id != null) {
			$query->andWhere('id != :id', ['id' => $this->_id]);
		}

		$count = $query->count();

		if (count($values) != $count) {
			return $this->addError($attribute, Yii::t('backend.user', '{attribute} is invalid.', [
				'attribute' => $this->getAttributeLabel($attribute),
			]));
		}
	}
	
	public function validateSites($attribute, $params)
	{
		return true;
		$values = (array) $this->$attribute;

		$query = User::find()->where(['in', 'id', array_values($values)]);

		if ($this->_id != null) {
			$query->andWhere('id != :id', ['id' => $this->_id]);
		}

		$count = $query->count();

		if (count($values) != $count) {
			return $this->addError($attribute, Yii::t('backend.user', '{attribute} is invalid.', [
				'attribute' => $this->getAttributeLabel($attribute),
			]));
		}
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('backend.user', 'Name'),
			'email' => Yii::t('backend.user', 'Email'),
			'nickname' => Yii::t('backend.user', 'Username'),
			'role' => Yii::t('backend.user', 'Role'),
			'password' => Yii::t('backend.user', 'Password'),
			'password_repeat' => Yii::t('backend.user', 'Password repeat'),
			'address' => Yii::t('backend.user', 'Address'),
			'job' => Yii::t('backend.user', 'Job'),
			'phone' => Yii::t('backend.user', 'Phone'),
			'fax' => Yii::t('backend.user', 'Fax'),
			'comment' => Yii::t('backend.user', 'Comment'),
			'alert_notification_email' => Yii::t('backend.user', 'Alert notifications by Email'),
			'alert_notifications' => Yii::t('backend.user', 'Alert notification for Sites'),
			'users' => Yii::t('backend.user', 'Associated users'),
			'sites' => Yii::t('backend.user', 'Associated sites'),
			'tenants' => Yii::t('backend.user', 'Associated tenants'),
			'status' => Yii::t('backend.user', 'Status'),
		];
	}

	/**
	 * Load attributes
	 * @param integer $scenario
	 * @param User $model
	 */
	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

				$this->name = $model->name;
				$this->nickname = $model->nickname;
				$this->email = $model->email;
				$this->role = $model->role;
				$this->alert_notification_email = $model->alert_notification_email;
				$this->status = $model->status;

				$model_profile = $model->relationUserProfile;

				if ($model_profile != null) {
					$this->address = $model_profile->address;
					$this->job = $model_profile->job;
					$this->phone = $model_profile->phone;
					$this->fax = $model_profile->fax;
					$this->comment = $model_profile->comment;
				}

				if (!Yii::$app->request->isPost) {
					$model_alert_notifications = $model->relationUserAlertNotifications;

					foreach ($model_alert_notifications as $model_alert_notification) {
						$this->alert_notifications[] = $model_alert_notification->site_id;
					}

					$model_user_owners = $model->relationUserOwners;

					foreach ($model_user_owners as $model_user_owner) {
						$this->users[] = $model_user_owner->user_id;
					}
					
					$model_user_owner_sites = $model->relationUserOwnerSites;

					foreach ($model_user_owner_sites as $model_user_owner_site) {
						$this->sites[] = $model_user_owner_site->site_id;
					}
					
					$model_user_owner_tenants = $model->relationUserOwnerTenants;

					foreach ($model_user_owner_tenants as $model_user_owner_tenant) {
						$this->tenants[] = $model_user_owner_tenant->tenant_id;
					}
				}
				break;
			
			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();
		
		try	{
			$model = new User();
			$model->name = $this->name;
			$model->nickname = $this->nickname;
			$model->email = $this->email;
			$model->generatePassword($this->password);
			$model->generateAuthKey();
			$model->role = $this->role;
			$model->status = $this->status;
			
			$event = new EventLogUser();
			$event->model = $model;
			$model->on(EventLogUser::EVENT_AFTER_INSERT, [$event, EventLogUser::METHOD_CREATE]);

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			switch ($model->role) {
				case Role::ROLE_TECHNICIAN:
					$model->alert_notification_email = $this->alert_notification_email;
					$alert_notifications = $this->alert_notifications;
					
					if ($alert_notifications != null) {
						foreach ($alert_notifications as $site_id) {
							$model_alert_notification = new UserAlertNotification();
							$model_alert_notification->user_id = $model->id;
							$model_alert_notification->site_id = $site_id;

							if (!$model_alert_notification->save()) {
								throw new BadRequestHttpException(implode(' ', $model_alert_notification->getFirstErrors()));
							}
						}
					}
					break;
				
				case Role::ROLE_CLIENT:
					$user_owners = $this->users;

					if ($user_owners != null) {
						foreach ($user_owners as $user_id) {
							$model_user_owner = new UserOwner();
							$model_user_owner->user_id = $user_id;
							$model_user_owner->user_owner_id = $model->id;

							if (!$model_user_owner->save()) {
								throw new BadRequestHttpException(implode(' ', $model_user_owner->getFirstErrors()));
							}
						}
					}
					break;
				case Role::ROLE_SITE:
					$user_owner_sites = $this->sites;

					if ($user_owner_sites != null) {
						foreach ($user_owner_sites as $site_id) {
							$model_user_owner_site = new UserOwnerSite();
							$model_user_owner_site->site_id = $site_id;
							$model_user_owner_site->user_owner_id = $model->id;

							if (!$model_user_owner_site->save()) {
								throw new BadRequestHttpException(implode(' ', $model_user_owner_site->getFirstErrors()));
							}
						}
					}
					
					break;
				case Role::ROLE_TENANT:
					$user_owner_tenants = $this->tenants;

					if ($user_owner_tenants != null) {
						foreach ($user_owner_tenants as $tenant_id) {
							$model_user_owner_tenant = new UserOwnerTenant();
							$model_user_owner_tenant->tenant_id = $tenant_id;
							$model_user_owner_tenant->user_owner_id = $model->id;

							if (!$model_user_owner_tenant->save()) {
								throw new BadRequestHttpException(implode(' ', $model_user_owner_tenant->getFirstErrors()));
							}
						}
					}
					
					break;
				default:
					break;
			}

			$model_profile = new UserProfile();
			$model_profile->user_id = $model->id;
			$model_profile->address = $this->address;
			$model_profile->job = $this->job;
			$model_profile->phone = $this->phone;
			$model_profile->fax = $this->fax;
			$model_profile->comment = $this->comment;

			if (!$model_profile->save()) {
				throw new BadRequestHttpException(implode(' ', $model_profile->getFirstErrors()));
			}

			$transaction->commit();
			return $model;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$updated_attributes = [];

			$model = User::findOne($this->_id);
			$model->name = $this->name;
			$model->nickname = $this->nickname;
			$model->email = $this->email;
			$model->role = $this->role;
			$model->status = $this->status;

			switch ($model->role) {
				case Role::ROLE_TECHNICIAN:
					$model->alert_notification_email = $this->alert_notification_email;

					$alert_notifications = $this->alert_notifications;
					$model_alert_notifications = $model->relationUserAlertNotifications;

					foreach ($model_alert_notifications as $model_alert_notification) {
						if (in_array($model_alert_notification->site_id, (array) $alert_notifications)) {
							unset($alert_notifications[array_search($model_alert_notification->site_id, $alert_notifications)]);
						} else {
							$model_alert_notification->delete();
						}
					}

					if ($alert_notifications != null) {
						foreach ($alert_notifications as $site_id) {
							$model_alert_notification = new UserAlertNotification();
							$model_alert_notification->user_id = $model->id;
							$model_alert_notification->site_id = $site_id;

							if (!$model_alert_notification->save()) {
								throw new BadRequestHttpException(implode(' ', $model_alert_notification->getFirstErrors()));
							}
						}
					}

					UserOwner::deleteAll(['user_owner_id' => $model->id]);
					break;

				case Role::ROLE_CLIENT:
					$user_owners = $this->users;
					$model_user_owners = $model->relationUserOwners;

					foreach ($model_user_owners as $model_user_owner) {
						if (in_array($model_user_owner->user_id, (array) $user_owners)) {
							unset($user_owners[array_search($model_user_owner->user_id, $user_owners)]);
						} else {
							$model_user_owner->delete();
						}
					}

					if ($user_owners != null) {
						foreach ($user_owners as $user_id) {
							$model_user_owner = new UserOwner();
							$model_user_owner->user_id = $user_id;
							$model_user_owner->user_owner_id = $model->id;

							if (!$model_user_owner->save()) {
								throw new BadRequestHttpException(implode(' ', $model_user_owner->getFirstErrors()));
							}
						}
					}

					$model->alert_notification_email = NULL;
					UserAlertNotification::deleteAll(['user_id' => $model->id]);
					break;
				
				case Role::ROLE_SITE:
					$user_owner_sites = $this->sites;
					$model_user_owner_sites = $model->relationUserOwnerSites;

					foreach ($model_user_owner_sites as $model_user_owner_site) {
						if (in_array($model_user_owner_site->site_id, (array) $user_owner_sites)) {
							unset($user_owner_sites[array_search($model_user_owner_site->site_id, $user_owner_sites)]);
						} else {
							$model_user_owner_site->delete();
						}
					}

					if ($user_owner_sites != null) {
						foreach ($user_owner_sites as $site_id) {
							$model_user_owner_site = new UserOwnerSite();
							$model_user_owner_site->site_id = $site_id;
							$model_user_owner_site->user_owner_id = $model->id;
							
							if (!$model_user_owner_site->save()) {
								throw new BadRequestHttpException(implode(' ', $model_user_owner_site->getFirstErrors()));
							}
						}
					}

					$model->alert_notification_email = NULL;
					UserAlertNotification::deleteAll(['user_id' => $model->id]);
					break;
				
				case Role::ROLE_TENANT:
					$user_owner_tenants = $this->tenants;
					$model_user_owner_tenants = $model->relationUserOwnerTenants;

					foreach ($model_user_owner_tenants as $model_user_owner_tenant) {
						if (in_array($model_user_owner_tenant->tenant_id, (array) $user_owner_tenants)) {
							unset($user_owner_tenants[array_search($model_user_owner_tenant->tenant_id, $user_owner_tenants)]);
						} else {
							$model_user_owner_tenant->delete();
						}
					}

					if ($user_owner_tenants != null) {
						foreach ($user_owner_tenants as $tenant_id) {
							$model_user_owner_tenant = new UserOwnerTenant();
							$model_user_owner_tenant->tenant_id = $tenant_id;
							$model_user_owner_tenant->user_owner_id = $model->id;

							if (!$model_user_owner_tenant->save()) {
								throw new BadRequestHttpException(implode(' ', $model_user_owner_tenant->getFirstErrors()));
							}
						}
					}

					$model->alert_notification_email = NULL;
					UserAlertNotification::deleteAll(['user_id' => $model->id]);
					break;
				
				default:
					$model->alert_notification_email = NULL;
					UserAlertNotification::deleteAll(['user_id' => $model->id]);
					UserOwner::deleteAll(['user_owner_id' => $model->id]);
					break;
			}

			$updated_attributes = ArrayHelper::merge($model->getUpdatedAttributes(), $updated_attributes);

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			$model_profile = $model->relationUserProfile;

			if ($model_profile == null) {
				$model_profile = new UserProfile();
				$model_profile->user_id = $model->id;
			}

			$model_profile->address = $this->address;
			$model_profile->job = $this->job;
			$model_profile->phone = $this->phone;
			$model_profile->fax = $this->fax;
			$model_profile->comment = $this->comment;

			$updated_attributes = ArrayHelper::merge($model_profile->getUpdatedAttributes(), $updated_attributes);
			
			if (!$model_profile->save()) {
				throw new BadRequestHttpException(implode(' ', $model_profile->getFirstErrors()));
			}

			if ($updated_attributes != null) {
				$event = new EventLogUser();
				$event->model = $model;
				$model->on(EventLogUser::EVENT_INIT, [$event, EventLogUser::METHOD_UPDATE]);
				$model->init();
			}

			$transaction->commit();
			return $model;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}
}
