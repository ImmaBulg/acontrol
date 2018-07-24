<?php
use yii\db\Schema;
use common\models\User;
use common\components\rbac\Role;
use common\components\db\Migration;

class m150610_062718_default_user extends Migration
{
	public function up()
	{
		$model_user = new User();
		$model_user->name = 'Admin';
		$model_user->email = 'admin@dom.com';
		$model_user->generatePassword(12345);
		$model_user->role = Role::ROLE_ADMIN;
		$model_user->generateAuthKey();
		$model_user->status = User::STATUS_ACTIVE;
		$model_user->save();		
	}

	public function down()
	{
		User::deleteAll(['email' => 'admin@dom.com']);
	}
}
