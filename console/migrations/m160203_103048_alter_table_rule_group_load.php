<?php

use yii\db\Schema;

class m160203_103048_alter_table_rule_group_load extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('rule_group_load', 'name', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('rule_group_load', 'name');
	}
}
