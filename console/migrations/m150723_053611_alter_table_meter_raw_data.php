<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150723_053611_alter_table_meter_raw_data extends Migration
{
	public function up()
	{
		$this->dropTable('meter_raw_data');
		$this->createTable('meter_raw_data', [
			'id' => Schema::TYPE_PK,
			'channel_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'date' => Schema::TYPE_INTEGER . ' NOT NULL',
			'shefel' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'geva' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'pisga' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'reading_shefel' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'reading_geva' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'reading_pisga' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'max_shefel' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'max_geva' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'max_pisga' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_meter_raw_data_channel_id', 'meter_raw_data', 'channel_id', 'meter_channel', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_raw_data_created_by', 'meter_raw_data', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_raw_data_modified_by', 'meter_raw_data', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('meter_raw_data');
		$this->createTable('meter_raw_data', [
			'id' => Schema::TYPE_PK,
			'channel_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'date' => Schema::TYPE_INTEGER . ' NOT NULL',
			'shefel' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'geva' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'pisga' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'max_shefel' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'max_geva' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'max_pisga' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_meter_raw_data_channel_id', 'meter_raw_data', 'channel_id', 'meter_channel', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_raw_data_created_by', 'meter_raw_data', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_raw_data_modified_by', 'meter_raw_data', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}
}
