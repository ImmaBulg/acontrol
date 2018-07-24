<?php

use yii\db\Schema;
use common\components\db\Migration;
use common\models\MeterChannelGroup;

class m150727_105812_alter_table_meter_channel_group extends Migration
{
	public function up()
	{
		MeterChannelGroup::deleteAll();

		$this->addColumn('meter_channel_group', 'user_id', Schema::TYPE_INTEGER . ' NOT NULL');
		$this->addForeignKey('FK_meter_channel_group_user_id', 'meter_channel_group', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');

		$this->addColumn('meter_channel_group', 'site_id', Schema::TYPE_INTEGER . ' NOT NULL');
		$this->addForeignKey('FK_meter_channel_group_site_id', 'meter_channel_group', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropForeignKey('FK_meter_channel_group_user_id', 'meter_channel_group');
		$this->dropColumn('meter_channel_group', 'user_id');

		$this->dropForeignKey('FK_meter_channel_group_site_id', 'meter_channel_group');
		$this->dropColumn('meter_channel_group', 'site_id');
	}
}
