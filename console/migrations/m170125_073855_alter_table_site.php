<?php

use yii\db\Schema;

class m170125_073855_alter_table_site extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropColumn('site', 'ip_address');
	}

	public function down()
	{
		$this->addColumn('site', 'ip_address', Schema::TYPE_STRING . ' DEFAULT NULL');
	}
}
