<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150804_100708_alter_table_report_tenant_bill extends Migration
{
	public function up()
	{
		$this->dropTable('report_tenant_bill_file');
		$this->dropTable('report_tenant_bill');
	}

	public function down()
	{
		$this->createTable('report_tenant_bill', [
			'id' => Schema::TYPE_PK,
			'tenant_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'from_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'to_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_report_tenant_bill_tenant_id', 'report_tenant_bill', 'tenant_id', 'tenant', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_report_tenant_bill_created_by', 'report_tenant_bill', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_report_tenant_bill_modified_by', 'report_tenant_bill', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		
		$this->createTable('report_tenant_bill_file', [
			'id' => Schema::TYPE_PK,
			'report_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'file' => Schema::TYPE_TEXT . ' NOT NULL',
			'language' => Schema::TYPE_STRING . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_report_tenant_bill_file_report_id', 'report_tenant_bill_file', 'report_id', 'report_tenant_bill', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_report_tenant_bill_file_created_by', 'report_tenant_bill_file', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_report_tenant_bill_file_modified_by', 'report_tenant_bill_file', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}
}
