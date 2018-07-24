<?php

use yii\db\Schema;

class m160405_053116_alter_table_tenant extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('tenant', 'hide_drilldown', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('tenant', 'hide_drilldown');
	}
}
