<?php

use yii\db\Schema;

class m160708_060000_alter_table_site_contact extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('site_contact', 'user_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_site_contact_user_id', 'site_contact', 'user_id', 'user', 'id', 'SET NULL');
	}

	public function down()
	{
		$this->dropForeignKey('FK_site_contact_user_id', 'site_contact');
		$this->dropColumn('site_contact', 'user_id');		
	}
}
