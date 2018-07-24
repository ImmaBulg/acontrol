<?php

namespace backend\models\forms;

use Yii;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\models\events\logs\EventLogUser;

class FormUserPasswordChange extends \yii\base\Model
{
	private $_id;

	public $password;
	public $password_repeat;

	public function rules()
	{
		return [
			[['password', 'password_repeat'], 'required'],
			[['password', 'password_repeat'], 'string', 'min' => User::PASSWORD_MIN_LENGTH, 'max' => User::PASSWORD_MAX_LENGTH],
			['password_repeat', 'compare', 'compareAttribute' => 'password'],
		];
	}

	public function attributeLabels()
	{
		return [
			'password' => Yii::t('backend.user', 'Password'),
			'password_repeat' => Yii::t('backend.user', 'Password repeat'),
		];
	}

	public function loadAttributes($model)
	{
		$this->_id = $model->id;
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = User::findOne($this->_id);
		$model->generatePassword($this->password);
		$model->generateAuthKey();

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		$event = new EventLogUser();
		$event->model = $model;
		$model->on(EventLogUser::EVENT_INIT, [$event, EventLogUser::METHOD_UPDATE]);
		$model->init();

		return $model;
	}
}
