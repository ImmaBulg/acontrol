<?php

use yii\db\Schema;

class m160324_095536_create_table_user_owner_tenant extends \common\components\db\Migration
{
	public function up()
	{
		$this->createTable('user_owner_tenant', [
			'id' => Schema::TYPE_PK,
			'tenant_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'user_owner_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_user_owner_tenant_tenant_id', 'user_owner_tenant', 'tenant_id', 'tenant', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_owner_tenant_user_owner_id', 'user_owner_tenant', 'user_owner_id', 'user', 'id', 'CASCADE', 'RESTRICT');

		$this->addForeignKey('FK_user_owner_tenant_created_by', 'user_owner_tenant', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_owner_tenant_modified_by', 'user_owner_tenant', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('user_owner_tenant');
	}
}
