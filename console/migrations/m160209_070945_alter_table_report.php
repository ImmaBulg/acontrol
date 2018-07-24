<?php

use yii\db\Schema;

class m160209_070945_alter_table_report extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('report', 'is_public', Schema::TYPE_BOOLEAN . ' NOT NULL');
	}

	public function down()
	{
		$this->dropColumn('report', 'is_public');
	}
}
