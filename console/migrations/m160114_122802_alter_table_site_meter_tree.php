<?php

use yii\db\Schema;

class m160114_122802_alter_table_site_meter_tree extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropTable('site_meter_tree');

		$this->createTable('site_meter_tree', [
			'id' => Schema::TYPE_PK,
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'meter_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'meter_channel_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'parent_meter_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'parent_meter_channel_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_site_meter_tree_site_id', 'site_meter_tree', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_meter_tree_meter_id', 'site_meter_tree', 'meter_id', 'meter', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_meter_tree_parent_meter_id', 'site_meter_tree', 'parent_meter_id', 'meter', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_meter_tree_meter_channel_id', 'site_meter_tree', 'meter_channel_id', 'meter_channel', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_meter_tree_parent_meter_channel_id', 'site_meter_tree', 'parent_meter_channel_id', 'meter_channel', 'id', 'CASCADE', 'RESTRICT');

		$this->addForeignKey('FK_site_meter_tree_created_by', 'site_meter_tree', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_meter_tree_modified_by', 'site_meter_tree', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('site_meter_tree');

		$this->createTable('site_meter_tree', [
			'id' => Schema::TYPE_PK,
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'meter_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'parent_meter_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_site_meter_tree_site_id', 'site_meter_tree', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_meter_tree_meter_id', 'site_meter_tree', 'meter_id', 'meter', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_meter_tree_parent_meter_id', 'site_meter_tree', 'parent_meter_id', 'meter', 'id', 'CASCADE', 'RESTRICT');

		$this->addForeignKey('FK_site_meter_tree_created_by', 'site_meter_tree', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_meter_tree_modified_by', 'site_meter_tree', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}
}
