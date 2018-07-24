<?php

use yii\db\Schema;

class m160801_132545_alter_table_task extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('task', 'ip_address', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('task', 'ip_address');	
	}
}
