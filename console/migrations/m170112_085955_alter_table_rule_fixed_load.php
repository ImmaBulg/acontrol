<?php

use yii\db\Schema;

class m170112_085955_alter_table_rule_fixed_load extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('rule_fixed_load', 'rate_type_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_rule_fixed_load_rate_type_id', 'rule_fixed_load', 'rate_type_id', 'rate_type', 'id', 'SET NULL');
	}

	public function down()
	{
		$this->dropForeignKey('FK_rule_fixed_load_rate_type_id', 'rule_fixed_load');
		$this->dropColumn('rule_fixed_load', 'rate_type_id');			
	}
}
