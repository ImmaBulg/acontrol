<?php
use yii\db\Schema;
use common\components\db\Migration;

class m150610_062720_table_user_profile extends Migration
{
	public function up()
	{
		$this->createTable('user_profile', [
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL PRIMARY KEY',
			'address' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'job' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'phone' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'fax' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'comment' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_user_profile_user_id', 'user_profile', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_profile_created_by', 'user_profile', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_profile_modified_by', 'user_profile', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('user_profile');
	}
}
