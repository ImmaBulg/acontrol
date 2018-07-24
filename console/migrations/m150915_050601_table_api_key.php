<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150915_050601_table_api_key extends Migration
{
	public function up()
	{
		$this->createTable('api_key', [
			'id' => Schema::TYPE_PK,
			'api_key' => Schema::TYPE_TEXT . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_api_key_created_by', 'api_key', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_api_key_modified_by', 'api_key', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('api_key');
	}
}
