<?php

use yii\db\Schema;

class m160513_070137_alter_table_site extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('site', 'cronjob_latest_issue_date_check', Schema::TYPE_INTEGER . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('site', 'cronjob_latest_issue_date_check');
	}
}
