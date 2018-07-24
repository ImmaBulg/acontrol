<?php

use yii\db\Schema;

class m160908_132012_alter_table_site extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('site', 'ip_address', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('site', 'ip_address');
	}
}
