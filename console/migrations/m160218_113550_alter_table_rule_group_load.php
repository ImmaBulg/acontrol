<?php

use yii\db\Schema;

class m160218_113550_alter_table_rule_group_load extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('rule_group_load', 'percent', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('rule_group_load', 'percent');
	}
}
