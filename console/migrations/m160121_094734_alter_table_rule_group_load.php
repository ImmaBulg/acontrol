<?php

use yii\db\Schema;
use common\models\RuleGroupLoad;

class m160121_094734_alter_table_rule_group_load extends \common\components\db\Migration
{
	public function up()
	{
		RuleGroupLoad::deleteAll();

		$this->dropForeignKey('FK_rule_group_load_tenant_group_id', 'rule_group_load');
		$this->dropColumn('rule_group_load', 'tenant_group_id');

		$this->dropColumn('rule_group_load', 'calculate_bill_action');
		$this->dropColumn('rule_group_load', 'group_type');
	}

	public function down()
	{
		RuleGroupLoad::deleteAll();
		
		$this->addColumn('rule_group_load', 'tenant_group_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_rule_group_load_tenant_group_id', 'rule_group_load', 'tenant_group_id', 'tenant_group', 'id', 'CASCADE', 'RESTRICT');
		
		$this->addColumn('rule_group_load', 'calculate_bill_action', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
		$this->addColumn('rule_group_load', 'group_type', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
	}
}
