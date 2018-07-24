<?php

use yii\db\Schema;

class m160405_063542_alter_table_rate_type extends \common\components\db\Migration
{
	public function up()
	{
		$this->renameColumn('rate_type', 'name', 'name_he');
		$this->alterColumn('rate_type', 'name_he', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('rate_type', 'name_en', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->renameColumn('rate_type', 'name_he', 'name');
		$this->dropColumn('rate_type', 'name_en');
	}
}
