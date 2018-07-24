<?php

use yii\db\Schema;

class m151020_122945_alter_table_task extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('task', 'create_date', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addColumn('task', 'urgency', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');

		$this->addColumn('task', 'site_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_task_site_id', 'task', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');

		$this->addColumn('task', 'site_contact_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_task_site_contact_id', 'task', 'site_contact_id', 'site_contact', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropColumn('task', 'create_date');
		$this->dropColumn('task', 'urgency');

		$this->dropForeignKey('FK_task_site_id', 'task');
		$this->dropColumn('task', 'site_id');

		$this->dropForeignKey('FK_task_site_contact_id', 'task');
		$this->dropColumn('task', 'site_contact_id');
	}
}
