<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\widgets\Alert;
use common\models\User;
use common\models\UserProfile;
use common\models\SiteContact;
use common\models\UserOwnerSite;
use common\components\rbac\Role;

/**
 * FormSiteContacts is the class for site tenant contacts mass edit.
 */
class FormSiteContacts extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	const SITE_CONTACTS_FIELD_NAME = 'site-contacts';

	public $generate_password = true;
	public $is_create_users = true;

	public function rules()
	{
		return [
			[['generate_password', 'is_create_users'], 'boolean'],
		];
	}

	public function attributeLabels()
	{
		return [
			'generate_password' => Yii::t('backend.tenant', 'Generate password and send email'),
			'is_create_users' => Yii::t('backend.tenant', 'Is create users'),
		];
	}

	public function save()
	{
		if (!$this->validate()) return false;
		$contacts = Yii::$app->request->getQueryParam(self::SITE_CONTACTS_FIELD_NAME);
		if ($contacts == null) return false;

		$send_created_users = [];

		/**
		 * Create / update users from site contacts
		 */
		if ($this->is_create_users) {
			$models = SiteContact::find()->where(['in', 'id', $contacts])->all();

			if ($models != null) {
				foreach ($models as $model) {
					if (($model_user = $model->relationUser) == null) {
						$model_user = new User();
						$model_user->email = $model->email;
						$model_user->generateNickname($model->email);
					}

					if ($model_user->isNewRecord || $this->generate_password) {
						$password = Yii::$app->getSecurity()->generateRandomString(10);
						$model_user->generatePassword($password);
						$model_user->generateAuthKey();
					}

					$model_user->name = $model->name;
					$model_user->role = Role::ROLE_SITE;
					$model_user->status = User::STATUS_ACTIVE;

					$transaction = User::getDb()->beginTransaction();

					if (!$model_user->save()) {
						Yii::$app->session->addFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to create/update user from contact ({contact}): {errors}', [
							'contact' => $model->name,
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
						Yii::$app->session->addFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to create/update profile from contact ({contact}): {errors}', [
							'contact' => $model->name,
							'errors' => array_shift($model_profile->getFirstErrors()),
						]));
						continue;
					}

					if (($model_user_owner_site = $model_user->getRelationUserOwnerSites()->andWhere(['site_id' => $model->site_id])->one()) == null) {
						$model_user_owner_site = new UserOwnerSite();
						$model_user_owner_site->site_id = $model->site_id;
						$model_user_owner_site->user_owner_id = $model_user->id;
					}

					if (!$model_user_owner_site->save()) {
						Yii::$app->session->addFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to create/update user-site relation from contact ({contact}): {errors}', [
							'contact' => $model->name,
							'errors' => array_shift($model_user_owner_site->getFirstErrors()),
						]));
						continue;
					}

					$model->user_id = $model_user->id;

					if (!$model->save()) {
						Yii::$app->session->addFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to create/update user-site relation from contact ({contact}): {errors}', [
							'contact' => $model->name,
							'errors' => array_shift($model->getFirstErrors()),
						]));
						continue;
					} else {
						$transaction->commit();
						Yii::$app->session->addFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'User have been created/updated from contact ({contact})', [
							'contact' => $model->name,
						]));

						if (!empty($password)) {
							$send_created_users[$model_user->email] = [
								'user' => $model_user,
								'password' => $password,
							];
						}
					}
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
