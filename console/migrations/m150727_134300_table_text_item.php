<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150727_134300_table_text_item extends Migration
{
	public function up()
	{
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
		$this->addForeignKey('FK_text_item_created_by', 'text_item', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_text_item_modified_by', 'text_item', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('text_item');
	}
}
