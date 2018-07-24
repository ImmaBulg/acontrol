<?php

use yii\db\Schema;

class m160118_093734_table_user_owner extends \common\components\db\Migration
{
	public function up()
	{
		$this->createTable('user_owner', [
			'id' => Schema::TYPE_PK,
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'user_owner_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_user_owner_user_id', 'user_owner', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_owner_user_owner_id', 'user_owner', 'user_owner_id', 'user', 'id', 'CASCADE', 'RESTRICT');

		$this->addForeignKey('FK_user_owner_created_by', 'user_owner', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_owner_modified_by', 'user_owner', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('user_owner');
	}
}
