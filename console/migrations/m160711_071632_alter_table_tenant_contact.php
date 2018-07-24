<?php

use yii\db\Schema;

class m160711_071632_alter_table_tenant_contact extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('tenant_contact', 'user_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_tenant_contact_user_id', 'tenant_contact', 'user_id', 'user', 'id', 'SET NULL');
	}

	public function down()
	{
		$this->dropForeignKey('FK_tenant_contact_user_id', 'tenant_contact');
		$this->dropColumn('tenant_contact', 'user_id');		
	}
}
