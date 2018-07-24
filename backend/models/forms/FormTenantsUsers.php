<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\widgets\Alert;
use common\models\Site;
use common\models\Tenant;
use common\models\TenantContact;
use common\models\User;
use common\models\UserProfile;
use common\models\UserOwnerTenant;
use common\components\rbac\Role;

/**
 * FormTenantsUsers is the class for tenant users mass edit.
 */
class FormTenantsUsers extends \yii\base\Model
{
	private $_site;

	public function loadAttributes($site)
	{
		$this->_site = $site;
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$site = $this->_site;
		$send_created_users = [];

		/**
		 * Create / update users from tenants
		 */
		$tenants = Tenant::find()->where([
			'and',
			['site_id' => $site->id],
			['status' => Tenant::STATUS_ACTIVE],
			['in', 'to_issue', [Site::TO_ISSUE_MANUAL, Site::TO_ISSUE_AUTOMATIC]],
		])->all();

		if ($tenants != null) {
			foreach ($tenants as $tenant) {
				$model = $tenant->getRelationTenantContacts()->andWhere([
					'and',
					TenantContact::tableName(). '.email IS NOT NULL',
					[TenantContact::tableName(). '.status' => TenantContact::STATUS_ACTIVE],
				])->one();

				if ($model != null) {
					if (($model_user = $model->relationUser) == null) {
						$model_user = new User();
						$model_user->email = $model->email;
						$model_user->generateNickname($model->email);
					}

					if ($model_user->isNewRecord) {
						$password = Yii::$app->getSecurity()->generateRandomString(10);
						$model_user->generatePassword($password);
						$model_user->generateAuthKey();
					}

					$model_user->name = $model->name;
					$model_user->role = Role::ROLE_TENANT;
					$model_user->status = User::STATUS_ACTIVE;

					$transaction = User::getDb()->beginTransaction();

					if (!$model_user->save()) {
						Yii::$app->session->addFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to create/update user from tenant ({tenant}): {errors}', [
							'tenant' => $tenant->name,
							'errors' => array_shift($model_user->getFirstErrors()),
						]));
						continue;
					}

					if (($model_profile = $model_user->relationUserProfile) == null) {
						$model_profile = new UserProfile();
						$model_profile->user_id = $model_user->id;
					}
					
					$model_profile->address = $model->address;
					$model_profile->job = $model->job;
					$model_profile->phone = $model->phone;
					$model_profile->fax = $model->fax;
					$model_profile->comment = $model->comment;

					if (!$model_profile->save()) {
						Yii::$app->session->addFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to create/update profile from tenant ({tenant}): {errors}', [
							'tenant' => $tenant->name,
							'errors' => array_shift($model_profile->getFirstErrors()),
						]));
						continue;
					}

					if (($model_user_owner_tenant = $model_user->getRelationUserOwnerTenants()->andWhere(['tenant_id' => $model->tenant_id])->one()) == null) {
						$model_user_owner_tenant = new UserOwnerTenant();
						$model_user_owner_tenant->tenant_id = $model->tenant_id;
						$model_user_owner_tenant->user_owner_id = $model_user->id;
					}

					if (!$model_user_owner_tenant->save()) {
						Yii::$app->session->addFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to create/update user-tenant relation from tenant ({tenant}): {errors}', [
							'tenant' => $tenant->name,
							'errors' => array_shift($model_user_owner_tenant->getFirstErrors()),
						]));
						continue;
					}

					$model->user_id = $model_user->id;

					if (!$model->save()) {
						Yii::$app->session->addFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to create/update user-tenant relation from tenant ({tenant}): {errors}', [
							'tenant' => $tenant->name,
							'errors' => array_shift($model->getFirstErrors()),
						]));
						continue;
					} else {
						$transaction->commit();
						Yii::$app->session->addFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'User have been created/updated from tenant ({tenant})', [
							'tenant' => $tenant->name,
						]));

						if (!empty($password)) {
							$send_created_users[$model_user->email] = [
								'user' => $model_user,
								'password' => $password,
							];
						}
					}
				} else {
					Yii::$app->session->addFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to create/update user from tenant ({tenant}): {errors}', [
						'tenant' => $tenant->name,
						'errors' => Yii::t('backend.controller', "Tenant contact does not exists."),
					]));
					continue;
				}
			}
		}
		
		foreach ($send_created_users as $email => $item) {
			$mailer = Yii::$app->mailer
			->compose('new-user-credentials', [
				'user' => $item['user'],
				'password' => $item['password'],
			])
			->setFrom([Yii::$app->params['emailFrom'] => Yii::$app->name])
			->setTo([$email])
			->setSubject(Yii::t('backend.controller', 'New Credentials'));
			$mailer->send();
		}

		return true;
	}
}
