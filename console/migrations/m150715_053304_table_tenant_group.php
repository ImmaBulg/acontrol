<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150715_053304_table_tenant_group extends Migration
{
	public function up()
	{
		$this->createTable('tenant_group', [
			'id' => Schema::TYPE_PK,
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_tenant_group_user_id', 'tenant_group', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_group_site_id', 'tenant_group', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_group_created_by', 'tenant_group', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_group_modified_by', 'tenant_group', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('tenant_group');
	}
}
