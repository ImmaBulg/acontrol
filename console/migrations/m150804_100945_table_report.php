<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150804_100945_table_report extends Migration
{
	public function up()
	{
		$this->createTable('report', [
			'id' => Schema::TYPE_PK,
			'site_owner_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'from_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'to_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'type' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_report_site_owner_id', 'report', 'site_owner_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_report_site_id', 'report', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_report_created_by', 'report', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_report_modified_by', 'report', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('report');
	}
}
