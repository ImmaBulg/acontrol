<?php

use yii\db\Schema;

class m160905_102735_alter_table_rule_group_load extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('rule_group_load', 'flat_percent', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('rule_group_load', 'flat_percent');
	}
}
