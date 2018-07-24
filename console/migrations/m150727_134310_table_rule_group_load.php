<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150727_134310_table_rule_group_load extends Migration
{
	public function up()
	{
		$this->createTable('rule_group_load', [
			'id' => Schema::TYPE_PK,
			'tenant_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'total_bill_action' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'use_type' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'use_percent' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'calculate_bill_action' => Schema::TYPE_BOOLEAN . ' DEFAULT NULL',
			'channel_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'group_type' => Schema::TYPE_BOOLEAN . ' DEFAULT NULL',
			'channel_group_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'tenant_group_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_rule_group_load_tenant_id', 'rule_group_load', 'tenant_id', 'tenant', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_group_load_channel_id', 'rule_group_load', 'channel_id', 'meter_channel', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_group_load_tenant_group_id', 'rule_group_load', 'tenant_group_id', 'tenant_group', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_group_load_channel_group_id', 'rule_group_load', 'channel_group_id', 'meter_channel_group', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_group_load_created_by', 'rule_group_load', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_group_load_modified_by', 'rule_group_load', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('rule_group_load');
	}
}
