<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150618_104718_table_tenant_contact extends Migration
{
	public function up()
	{
		$this->createTable('tenant_contact', [
			'id' => Schema::TYPE_PK,
			'tenant_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'email' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'address' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'job' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'phone' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'fax' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'comment' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_tenant_contact_tenant_id', 'tenant_contact', 'tenant_id', 'tenant', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_contact_created_by', 'tenant_contact', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_contact_modified_by', 'tenant_contact', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('tenant_contact');
	}
}
