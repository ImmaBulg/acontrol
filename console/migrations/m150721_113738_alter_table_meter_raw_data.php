<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150721_113738_alter_table_meter_raw_data extends Migration
{
	public function up()
	{
		$this->dropForeignKey('FK_meter_raw_data_channel_id', 'meter_raw_data');
		$this->dropColumn('meter_raw_data', 'channel_id');

		$this->addColumn('meter_raw_data', 'channel_id', Schema::TYPE_INTEGER . ' NOT NULL');
		$this->addForeignKey('FK_meter_raw_data_channel_id', 'meter_raw_data', 'channel_id', 'meter_channel', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropForeignKey('FK_meter_raw_data_channel_id', 'meter_raw_data');
		$this->dropColumn('meter_raw_data', 'channel_id');

		$this->addColumn('meter_raw_data', 'channel_id', Schema::TYPE_INTEGER . ' NOT NULL');
		$this->addForeignKey('FK_meter_raw_data_channel_id', 'meter_raw_data', 'channel_id', 'meter_subchannel', 'id', 'CASCADE', 'RESTRICT');
	}
}
