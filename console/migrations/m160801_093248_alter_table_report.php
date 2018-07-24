<?php

use yii\db\Schema;

class m160801_093248_alter_table_report extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('report', 'is_automatically_generated', Schema::TYPE_BOOLEAN . ' NOT NULL');
	}

	public function down()
	{
		$this->dropColumn('report', 'is_automatically_generated');	
	}
}
