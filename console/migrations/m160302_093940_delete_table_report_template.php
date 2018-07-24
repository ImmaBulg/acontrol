<?php

use yii\db\Schema;

class m160302_093940_delete_table_report_template extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropTable('report_template');
	}

	public function down()
	{
		$this->createTable('report_template', [
			'id' => Schema::TYPE_PK,
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'type' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_report_template_created_by', 'report_template', 'created_by', 'user', 'id', 'SET NULL', 'RESTRICT');
		$this->addForeignKey('FK_report_template_modified_by', 'report_template', 'modified_by', 'user', 'id', 'SET NULL', 'RESTRICT');
	}
}
