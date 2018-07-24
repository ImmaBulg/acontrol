<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150727_134320_table_rule_fixed_load extends Migration
{
	public function up()
	{
		$this->createTable('rule_fixed_load', [
			'id' => Schema::TYPE_PK,
			'tenant_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'use_type' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'use_frequency' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'value' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'shefel' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'geva' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'pisga' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'description' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_rule_fixed_load_tenant_id', 'rule_fixed_load', 'tenant_id', 'tenant', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_fixed_load_created_by', 'rule_fixed_load', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rule_fixed_load_modified_by', 'rule_fixed_load', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('rule_fixed_load');
	}
}
