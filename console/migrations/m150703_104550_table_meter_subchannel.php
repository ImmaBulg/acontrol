<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150703_104550_table_meter_subchannel extends Migration
{
	public function up()
	{
		$this->createTable('meter_subchannel', [
			'id' => Schema::TYPE_PK,
			'meter_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'channel_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'channel' => Schema::TYPE_DOUBLE . ' NOT NULL',
			'phase' => Schema::TYPE_DOUBLE . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'old_id' => Schema::TYPE_STRING . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_meter_subchannel_meter_id', 'meter_subchannel', 'meter_id', 'meter', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_subchannel_channel_id', 'meter_subchannel', 'channel_id', 'meter_channel', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_subchannel_created_by', 'meter_subchannel', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_subchannel_modified_by', 'meter_subchannel', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('meter_subchannel');
	}
}
