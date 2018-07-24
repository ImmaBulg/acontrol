<?php

use yii\db\Schema;

class m160303_094214_alter_table_task extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropTable('task_comment');
		$this->dropTable('task');
		$this->createTable('task', [
			'id' => Schema::TYPE_PK,
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'site_contact_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'date' => Schema::TYPE_INTEGER . ' NOT NULL',
			'description' => Schema::TYPE_TEXT . ' NOT NULL',
			'type' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'urgency' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'is_sent' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_task_user_id', 'task', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_task_site_id', 'task', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_task_site_contact_id', 'task', 'site_contact_id', 'site_contact', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_task_created_by', 'task', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_task_modified_by', 'task', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

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
		$this->addForeignKey('FK_task_comment_created_by', 'task_comment', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_task_comment_modified_by', 'task_comment', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('task_comment');
		$this->dropTable('task');
		$this->createTable('task', [
			'id' => Schema::TYPE_PK,
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'site_contact_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'description' => Schema::TYPE_TEXT . ' NOT NULL',
			'create_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'urgency' => Schema::TYPE_BOOLEAN . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_task_user_id', 'task', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_task_site_id', 'task', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_task_site_contact_id', 'task', 'site_contact_id', 'site_contact', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_task_created_by', 'task', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_task_modified_by', 'task', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

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
		$this->addForeignKey('FK_task_comment_created_by', 'task_comment', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_task_comment_modified_by', 'task_comment', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	}
}
