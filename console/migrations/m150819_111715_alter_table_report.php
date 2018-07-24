<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150819_111715_alter_table_report extends Migration
{
	public function up()
	{
		$this->addColumn('report', 'level', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('report', 'level');
	}
}
