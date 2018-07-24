<?php

use yii\db\Schema;

class m160301_110243_table_rate_type extends \common\components\db\Migration
{
	public function up()
	{
		$this->createTable('rate_type', [
			'id' => Schema::TYPE_PK,
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'type' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_rate_type_created_by', 'rate_type', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_rate_type_modified_by', 'rate_type', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('rate_type');
	}
}
