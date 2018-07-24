<?php

namespace common\components;

use Yii;

class User extends \yii\web\User
{
	public $identityClass = 'common\models\User';
	
	public function getRole()
	{
		if (!$this->isGuest) {
			return $this->identity->role;
		}
	}
}