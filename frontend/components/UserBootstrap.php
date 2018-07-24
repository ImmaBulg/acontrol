<?php

namespace frontend\components;

use Yii;
use yii\helpers\Url;

use common\models\User;
use common\components\rbac\Role;

class UserBootstrap implements \yii\base\BootstrapInterface
{
	/**
	 * @inheritdoc
	 */
	public function bootstrap($app)
	{
		if (!Yii::$app->user->isGuest) {
			$identity = Yii::$app->user->getIdentity();

			if (!in_array($identity->status, [
				User::STATUS_ACTIVE,
			]) || !in_array($identity->role, [
				Role::ROLE_CLIENT,
				Role::ROLE_SITE,
				Role::ROLE_TENANT,
			])) {
				Yii::$app->user->logout();
				Yii::$app->getResponse()->redirect(Url::to(['/site/index']), 302);
			}
		}
	}
}