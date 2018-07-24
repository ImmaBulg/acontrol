<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150618_103817_table_user_contact extends Migration
{
	public function up()
	{
		$this->createTable('user_contact', [
			'id' => Schema::TYPE_PK,
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'email' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'address' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'job' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'phone' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'fax' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'comment' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_user_contact_user_id', 'user_contact', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_contact_created_by', 'user_contact', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_contact_modified_by', 'user_contact', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('user_contact');
	}
}
