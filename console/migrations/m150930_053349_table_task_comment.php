<?php

use yii\db\Schema;

class m150930_053349_table_task_comment extends \common\components\db\Migration
{
	public function up()
	{
		$this->createTable('task_comment', [
			'id' => Schema::TYPE_PK,
			'task_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'description' => Schema::TYPE_TEXT . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_task_comment_task_id', 'task_comment', 'task_id', 'task', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_task_comment_created_by', 'task_comment', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_task_comment_modified_by', 'task_comment', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('task_comment');
	}
}
