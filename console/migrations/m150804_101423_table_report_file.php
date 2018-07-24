<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150804_101423_table_report_file extends Migration
{
	public function up()
	{
		$this->createTable('report_file', [
			'id' => Schema::TYPE_PK,
			'report_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'file' => Schema::TYPE_TEXT . ' NOT NULL',
			'file_type' => Schema::TYPE_STRING . ' NOT NULL',
			'language' => Schema::TYPE_STRING . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_report_file_report_id', 'report_file', 'report_id', 'report', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_report_file_created_by', 'report_file', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_report_file_modified_by', 'report_file', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('report_file');
	}
}
