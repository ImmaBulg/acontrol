<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150622_102500_table_meter extends Migration
{
	public function up()
	{
		$this->createTable('meter', [
			'id' => Schema::TYPE_PK,
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'type_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'current_multiplier' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'voltage_multiplier' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'communication_type' => Schema::TYPE_BOOLEAN . ' DEFAULT NULL',
			'phisical_location' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_meter_type_id', 'meter', 'type_id', 'meter_type', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_created_by', 'meter', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_modified_by', 'meter', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('meter');
	}
}
