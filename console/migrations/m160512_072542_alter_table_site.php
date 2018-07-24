<?php

use yii\db\Schema;

class m160512_072542_alter_table_site extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('site', 'cronjob_latest_meter_date_check', Schema::TYPE_INTEGER . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('site', 'cronjob_latest_meter_date_check');
	}
}
