<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150723_065205_drop_table_meter_reading_max_demand extends Migration
{
	public function up()
	{
		$this->dropTable('meter_reading_max_demand');
	}

	public function down()
	{
		$this->createTable('meter_reading_max_demand', [
			'id' => Schema::TYPE_PK,
			'old_id' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'date' => Schema::TYPE_INTEGER . ' NOT NULL',
			'shefel' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'geva' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'pisga' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_meter_reading_max_demand_created_by', 'meter_reading_max_demand', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_meter_reading_max_demand_modified_by', 'meter_reading_max_demand', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}
}
