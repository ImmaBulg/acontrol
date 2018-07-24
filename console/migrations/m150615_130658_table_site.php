<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150615_130658_table_site extends Migration
{
	public function up()
	{
		$this->createTable('site', [
			'id' => Schema::TYPE_PK,
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'electric_company_id' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_site_user_id', 'site', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_created_by', 'site', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_modified_by', 'site', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('site');
	}
}
