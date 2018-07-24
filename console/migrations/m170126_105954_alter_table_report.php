<?php

use yii\db\Schema;

class m170126_105954_alter_table_report extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('report', 'data_usage_method', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('report', 'data_usage_method');			
	}
}
