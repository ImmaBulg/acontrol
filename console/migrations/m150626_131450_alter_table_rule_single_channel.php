<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150626_131450_alter_table_rule_single_channel extends Migration
{
	public function up()
	{
		$this->dropColumn('rule_single_channel', 'channel');

		$this->dropForeignKey('FK_rule_single_channel_site_id', 'rule_single_channel');
		$this->dropColumn('rule_single_channel', 'site_id');

		$this->dropForeignKey('FK_rule_single_channel_meter_id', 'rule_single_channel');
		$this->dropColumn('rule_single_channel', 'meter_id');

		$this->addColumn('rule_single_channel', 'channel_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_rule_single_channel_channel_id', 'rule_single_channel', 'channel_id', 'meter_channel', 'id', 'CASCADE', 'RESTRICT');
	
		$this->addColumn('rule_single_channel', 'start_date', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addColumn('rule_single_channel', 'total_bill_action', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->addColumn('rule_single_channel', 'channel', Schema::TYPE_DOUBLE . ' NOT NULL');

		$this->addColumn('rule_single_channel', 'site_id', Schema::TYPE_INTEGER . ' NOT NULL');
		$this->addForeignKey('FK_rule_single_channel_site_id', 'rule_single_channel', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');

		$this->addColumn('rule_single_channel', 'meter_id', Schema::TYPE_INTEGER . ' NOT NULL');
		$this->addForeignKey('FK_rule_single_channel_meter_id', 'rule_single_channel', 'meter_id', 'meter', 'id', 'CASCADE', 'RESTRICT');
		
		$this->dropForeignKey('FK_rule_single_channel_channel_id', 'rule_single_channel');
		$this->dropColumn('rule_single_channel', 'channel_id');

		$this->dropColumn('rule_single_channel', 'start_date');
		$this->dropColumn('rule_single_channel', 'total_bill_action');
	}
}
