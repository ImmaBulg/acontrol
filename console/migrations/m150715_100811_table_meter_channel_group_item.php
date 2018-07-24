<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150715_100811_table_meter_channel_group_item extends Migration
{
	public function up()
	{
		$this->createTable('meter_channel_group_item', [
			'id' => Schema::TYPE_PK,
			'group_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'channel_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_meter_channel_group_item_group_id', 'meter_channel_group_item', 'group_id', 'meter_channel_group', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_group_item_channel_id', 'meter_channel_group_item', 'channel_id', 'meter_channel', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_group_item_created_by', 'meter_channel_group_item', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_channel_group_item_modified_by', 'meter_channel_group_item', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('meter_channel_group_item');
	}
}
