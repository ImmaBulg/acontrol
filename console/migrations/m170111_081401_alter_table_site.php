<?php

use yii\db\Schema;

class m170111_081401_alter_table_site extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('site', 'auto_issue_reports', Schema::TYPE_TEXT . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('site', 'auto_issue_reports');
	}
}
