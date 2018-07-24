<?php

use yii\db\Schema;

class m170125_072429_create_table_site_ip_address extends \common\components\db\Migration
{
	public function up()
	{
		$this->createTable('site_ip_address', [
			'id' => Schema::TYPE_PK,
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'ip_address' => Schema::TYPE_STRING . ' NOT NULL',
			'is_main' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_site_ip_address_site_id', 'site_ip_address', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_ip_address_created_by', 'site_ip_address', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_ip_address_modified_by', 'site_ip_address', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('site_ip_address');
	}
}
