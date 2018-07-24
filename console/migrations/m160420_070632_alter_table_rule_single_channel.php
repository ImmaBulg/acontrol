<?php

use yii\db\Schema;

class m160420_070632_alter_table_rule_single_channel extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('rule_single_channel', 'name', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('rule_single_channel', 'name');
	}
}
