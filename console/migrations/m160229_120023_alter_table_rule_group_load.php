<?php

use yii\db\Schema;

class m160229_120023_alter_table_rule_group_load extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('rule_group_load', 'tenant_group_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_rule_group_load_tenant_group_id', 'rule_group_load', 'tenant_group_id', 'tenant_group', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropForeignKey('FK_rule_group_load_tenant_group_id', 'rule_group_load');
		$this->dropColumn('rule_group_load', 'tenant_group_id');
	}
}
