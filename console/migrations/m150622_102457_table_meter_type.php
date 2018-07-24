<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150622_102457_table_meter_type extends Migration
{
	public function up()
	{
		$this->createTable('meter_type', [
			'id' => Schema::TYPE_PK,
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'channels' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'phases' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'serie_number' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'modbus' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_meter_type_created_by', 'meter_type', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_type_modified_by', 'meter_type', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('meter_type');
	}
}
