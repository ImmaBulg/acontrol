<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\widgets\Alert;
use common\models\events\logs\EventLogUser;

/**
 * FormUsers is the class for users mass edit.
 */
class FormUsers extends \yii\base\Model
{
	const USERS_FIELD_NAME = 'users';

	public $status;
	public $generate_nickname;
	public $generate_password;

	public function rules()
	{
		return [
			['status', 'in', 'range' => array_keys(User::getListStatuses()), 'skipOnEmpty' => true],
			[['generate_nickname', 'generate_password'], 'boolean'],
		];
	}

	public function attributeLabels()
	{
		return [
			'status' => Yii::t('backend.user', 'Status'),
			'generate_nickname' => Yii::t('backend.user', 'Generate new username'),
			'generate_password' => Yii::t('backend.user', 'Generate new password'),
		];
	}

	public function save()
	{
		if (!$this->validate()) return false;
		$users = Yii::$app->request->getQueryParam(self::USERS_FIELD_NAME);
		if ($users == null) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$models = User::find()->where(['in', 'id', $users])->andWhere(['!=', 'id', Yii::$app->user->id])->all();

			if ($models != null) {
				foreach ($models as $model) {
					$password = null;

					if ($this->status !== null) {
						$model->status = $this->status;
					}

					if ($this->generate_nickname) {
						$model->generateNickname($model->email);
					}

					if ($this->generate_password) {
						$password = Yii::$app->getSecurity()->generateRandomString(10);
						$model->generatePassword($password);
						$model->generateAuthKey();
					}

					if (!$model->validate()) {
						Yii::$app->session->setFlash(Alert::ALERT_DANGER, Yii::t('backend.user', 'Please fix the following errors for user {name}:', [
							'name' => $model->name,
						]). '<br/>' .implode(' ', $model->getFirstErrors()));
						return false;
					}

					if ($this->generate_nickname || $this->generate_password) {
						$mailer = Yii::$app->mailer
						->compose('new-user-credentials', [
							'user' => $model,
							'password' => $password,
						])
						->setFrom([Yii::$app->params['emailFrom'] => Yii::$app->name])
						->setTo([$model->email])
						->setSubject(Yii::t('backend.controller', 'New Credentials'));
						$mailer->send();
					}

					if ($model->getUpdatedAttributes() != null) {					
						if (!$model->save(false)) {
							throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
						}

						$event = new EventLogUser();
						$event->model = $model;
						$model->on(EventLogUser::EVENT_INIT, [$event, EventLogUser::METHOD_UPDATE]);
						$model->init();
					}
				}
			}

			$transaction->commit();
			return true;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}
}
