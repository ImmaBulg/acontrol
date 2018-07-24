<?php

use yii\db\Schema;

class m170125_083512_alter_table_meter extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('meter', 'ip_address', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('meter', 'ip_address');
	}
}
