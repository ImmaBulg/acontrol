<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150626_103552_table_meter_channel extends Migration
{
	public function up()
	{
		$this->createTable('meter_channel', [
			'id' => Schema::TYPE_PK,
			'meter_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'channel' => Schema::TYPE_DOUBLE . ' NOT NULL',
			'current_multiplier' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'voltage_multiplier' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_meter_channel_meter_id', 'meter_channel', 'meter_id', 'meter', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_created_by', 'meter_channel', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_modified_by', 'meter_channel', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('meter_channel');
	}
}
