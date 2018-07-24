<?php

use yii\db\Schema;

class m160302_091855_delete_table_text extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropTable('text_item');
		$this->dropTable('text');
	}

	public function down()
	{
		$this->createTable('text', [
			'id' => Schema::TYPE_PK,
			'type' => Schema::TYPE_STRING . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_text_created_by', 'text', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_text_modified_by', 'text', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');

		$this->createTable('text_item', [
			'id' => Schema::TYPE_PK,
			'text_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'language' => Schema::TYPE_STRING . ' NOT NULL',
			'value' => Schema::TYPE_TEXT . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_text_item_text_id', 'text_item', 'text_id', 'text', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_text_item_created_by', 'text_item', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_text_item_modified_by', 'text_item', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	}
}
