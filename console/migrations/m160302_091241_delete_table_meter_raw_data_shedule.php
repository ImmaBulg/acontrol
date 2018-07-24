<?php

use yii\db\Schema;

class m160302_091241_delete_table_meter_raw_data_shedule extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropTable('meter_raw_data_schedule');
	}

	public function down()
	{
		$this->createTable('meter_raw_data_schedule', [
			'id' => Schema::TYPE_PK,
			'executed_rows' => Schema::TYPE_INTEGER . ' NOT NULL',
			'executed_time' => Schema::TYPE_INTEGER . ' NOT NULL',
			'message' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'message_tokens' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'next_schedule_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_meter_raw_data_schedule_created_by', 'meter_raw_data_schedule', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_meter_raw_data_schedule_modified_by', 'meter_raw_data_schedule', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	}
}
