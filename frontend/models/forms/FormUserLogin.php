<?php

namespace frontend\models\forms;

use Yii;
use yii\web\BadRequestHttpException;
use common\models\User;
use common\components\rbac\Role;

/**
 * FormUserLogin is the class for user login.
 */
class FormUserLogin extends \yii\base\Model
{
	private $_user = false;
	
	public $nickname;
	public $password;
	public $rememberMe = true;

	const COOKIE_DURATION = 2592000; // 30 days

	public function rules()
	{
		return [
			[['nickname', 'password'], 'required'],
			['rememberMe', 'boolean'],
			['nickname', 'validateNickname'],
		];
	}

	public function attributeLabels()
	{
		return [
			'nickname' => Yii::t('frontend.user', 'Username'),
			'password' => Yii::t('frontend.user', 'Password'),
			'rememberMe' => Yii::t('frontend.user', 'Remember me'),
		];
	}

	public function validateNickname($attribute, $params)
	{
		$user = $this->getUser();
		
		if (!$user || !$user->validatePassword($this->password)) {
			return $this->addError($attribute, Yii::t('frontend.user', 'Wrong username or password.'));
		}
	}

	public function save()
	{
		if ($this->validate()) {
			Yii::$app->session->remove('switch');
			return Yii::$app->user->login($this->getUser(), $this->rememberMe ? self::COOKIE_DURATION : 0);
		} else {
			return false;
		}
	}

	private function getUser()
	{
		if ($this->_user === false) {
			$this->_user = User::find()
			->where('nickname = :nickname COLLATE utf8_bin', ['nickname' => $this->nickname])
			->andWhere([
				'status' => User::STATUS_ACTIVE,
			])->andWhere(['in', 'role', [
				Role::ROLE_CLIENT,
				Role::ROLE_SITE,
				Role::ROLE_TENANT,
			]])->one();
		}

		return $this->_user;
	}
}
