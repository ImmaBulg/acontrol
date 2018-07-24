<?php

use yii\db\Schema;

class m160615_081218_alter_table_task extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('task', 'meter_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_task_meter_id', 'task', 'meter_id', 'meter', 'id', 'SET NULL');
		
		$this->addColumn('task', 'channel_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_task_channel_id', 'task', 'channel_id', 'meter_channel', 'id', 'SET NULL');

		$this->addColumn('task', 'color', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropForeignKey('FK_task_meter_id', 'task');
		$this->dropColumn('task', 'meter_id');

		$this->dropForeignKey('FK_task_channel_id', 'task');
		$this->dropColumn('task', 'channel_id');

		$this->dropColumn('task', 'color');
	}
}
