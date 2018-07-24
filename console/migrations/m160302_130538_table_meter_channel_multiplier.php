<?php

use yii\db\Schema;

class m160302_130538_table_meter_channel_multiplier extends \common\components\db\Migration
{
	public function up()
	{
		$this->createTable('meter_channel_multiplier', [
			'id' => Schema::TYPE_PK,
			'meter_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'channel_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'current_multiplier' => Schema::TYPE_DOUBLE . ' NOT NULL',
			'voltage_multiplier' => Schema::TYPE_DOUBLE . ' NOT NULL',
			'start_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'end_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_meter_channel_multiplier_meter_id', 'meter_channel_multiplier', 'meter_id', 'meter', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_multiplier_channel_id', 'meter_channel_multiplier', 'channel_id', 'meter_channel', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_multiplier_created_by', 'meter_channel_multiplier', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_multiplier_modified_by', 'meter_channel_multiplier', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('meter_channel_multiplier');
	}
}
