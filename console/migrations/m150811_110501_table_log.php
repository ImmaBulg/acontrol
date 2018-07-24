<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150811_110501_table_log extends Migration
{
	public function up()
	{
		$this->createTable('log', [
			'id' => Schema::TYPE_PK,
			'type' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'action' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'tokens' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'ip_address' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_log_created_by', 'log', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_log_modified_by', 'log', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('log');
	}
}
