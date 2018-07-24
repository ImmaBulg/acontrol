<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150623_090830_table_rule_single_channel extends Migration
{
	public function up()
	{
		$this->createTable('rule_single_channel', [
			'id' => Schema::TYPE_PK,
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'tenant_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'meter_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'channel' => Schema::TYPE_DOUBLE . ' NOT NULL',
			'use_type' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'replaced' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'percent' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'from_hours' => Schema::TYPE_TIME . ' DEFAULT NULL',
			'to_hours' => Schema::TYPE_TIME . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_rule_single_channel_site_id', 'rule_single_channel', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_single_channel_tenant_id', 'rule_single_channel', 'tenant_id', 'tenant', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_single_channel_meter_id', 'rule_single_channel', 'meter_id', 'meter', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_single_channel_created_by', 'rule_single_channel', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_single_channel_modified_by', 'rule_single_channel', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('rule_single_channel');
	}
}
