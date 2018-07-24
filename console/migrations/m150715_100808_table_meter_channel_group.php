<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150715_100808_table_meter_channel_group extends Migration
{
	public function up()
	{
		$this->createTable('meter_channel_group', [
			'id' => Schema::TYPE_PK,
			'meter_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_meter_channel_group_meter_id', 'meter_channel_group', 'meter_id', 'meter', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_group_created_by', 'meter_channel_group', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_group_modified_by', 'meter_channel_group', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('meter_channel_group');
	}
}
