<?php

use yii\db\Schema;

class m160601_120350_alter_table_report extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('report', 'parent_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_report_parent_id', 'report', 'parent_id', 'report', 'id', 'SET NULL');
	}

	public function down()
	{
		$this->dropForeignKey('FK_report_parent_id', 'report');
		$this->dropColumn('report', 'parent_id');
	}
}
