<?php

use yii\db\Schema;

class m151211_070248_alter_table_tenant extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('tenant', 'included_reports', Schema::TYPE_TEXT . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('tenant', 'included_reports');
	}
}
