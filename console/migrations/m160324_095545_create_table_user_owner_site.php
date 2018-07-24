<?php

use yii\db\Schema;

class m160324_095545_create_table_user_owner_site extends \common\components\db\Migration
{
	public function up()
	{
		$this->createTable('user_owner_site', [
			'id' => Schema::TYPE_PK,
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'user_owner_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_user_owner_site_site_id', 'user_owner_site', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_owner_site_user_owner_id', 'user_owner_site', 'user_owner_id', 'user', 'id', 'CASCADE', 'RESTRICT');

		$this->addForeignKey('FK_user_owner_site_created_by', 'user_owner_site', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_user_owner_site_modified_by', 'user_owner_site', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('user_owner_site');
	}
}
