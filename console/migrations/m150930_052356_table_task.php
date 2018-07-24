<?php

use yii\db\Schema;

class m150930_052356_table_task extends \common\components\db\Migration
{
	public function up()
	{
		$this->createTable('task', [
			'id' => Schema::TYPE_PK,
			'user_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'description' => Schema::TYPE_TEXT . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_task_user_id', 'task', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_task_created_by', 'task', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_task_modified_by', 'task', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('task');
	}
}
