<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150610_062227_table_user extends Migration
{
	public function up()
	{
		$this->createTable('user', [
			'id' => Schema::TYPE_PK,
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'email' => Schema::TYPE_STRING . ' NOT NULL',
			'role' => Schema::TYPE_STRING . ' NOT NULL',
			'password' => Schema::TYPE_STRING . ' NOT NULL',
			'password_reset_token' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'auth_key' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
	}

	public function down()
	{
		$this->dropTable('user');
	}
}
