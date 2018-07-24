<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150805_070046_table_tenant_report extends Migration
{
	public function up()
	{
		$this->createTable('tenant_report', [
			'id' => Schema::TYPE_PK,
			'tenant_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'report_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_tenant_report_tenant_id', 'tenant_report', 'tenant_id', 'tenant', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_report_report_id', 'tenant_report', 'report_id', 'report', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_report_created_by', 'tenant_report', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_report_modified_by', 'tenant_report', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('tenant_report');
	}
}
