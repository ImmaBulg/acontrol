<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150616_144448_table_tenant_billing_setting extends Migration
{
	public function up()
	{
		$this->createTable('tenant_billing_setting', [
			'tenant_id' => Schema::TYPE_INTEGER . ' NOT NULL PRIMARY KEY',
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'rate' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'comment' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'fixed_payment' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'id_with_client' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'accounting_number' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'billing_content' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_tenant_billing_setting_tenant_id', 'tenant_billing_setting', 'tenant_id', 'tenant', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_billing_setting_site_id', 'tenant_billing_setting', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_billing_setting_created_by', 'tenant_billing_setting', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_billing_setting_modified_by', 'tenant_billing_setting', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('tenant_billing_setting');
	}
}
