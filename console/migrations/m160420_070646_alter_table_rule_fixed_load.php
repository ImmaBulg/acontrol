<?php

use yii\db\Schema;

class m160420_070646_alter_table_rule_fixed_load extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('rule_fixed_load', 'name', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('rule_fixed_load', 'name');
	}
}
