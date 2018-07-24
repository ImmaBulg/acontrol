<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150703_104553_table_meter_reading extends Migration
{
	public function up()
	{
		$this->createTable('meter_reading', [
			'id' => Schema::TYPE_PK,
			'old_id' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'date' => Schema::TYPE_INTEGER . ' NOT NULL',
			'shefel' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'geva' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'pisga' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_meter_reading_created_by', 'meter_reading', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_reading_modified_by', 'meter_reading', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('meter_reading');
	}
}
