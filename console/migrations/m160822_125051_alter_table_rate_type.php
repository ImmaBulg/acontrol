<?php

use yii\db\Schema;

class m160822_125051_alter_table_rate_type extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('rate_type', 'level', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('rate_type', 'level');
	}
}
