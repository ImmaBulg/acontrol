<?php

use yii\db\Schema;

class m151021_053838_alter_table_meter extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('meter', 'breaker_name', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('meter', 'breaker_name');
	}
}
