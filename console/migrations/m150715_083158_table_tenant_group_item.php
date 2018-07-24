<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150715_083158_table_tenant_group_item extends Migration
{
	public function up()
	{
		$this->createTable('tenant_group_item', [
			'id' => Schema::TYPE_PK,
			'group_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'tenant_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_tenant_group_item_group_id', 'tenant_group_item', 'group_id', 'tenant_group', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_group_item_tenant_id', 'tenant_group_item', 'tenant_id', 'tenant', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_group_item_created_by', 'tenant_group_item', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_group_item_modified_by', 'tenant_group_item', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('tenant_group_item');
	}
}
