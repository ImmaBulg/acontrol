<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150626_085653_table_rate extends Migration
{
	public function up()
	{
		$this->createTable('rate', [
			'id' => Schema::TYPE_PK,
			'type' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'season' => Schema::TYPE_BOOLEAN . ' DEFAULT NULL',
			'day_type' => Schema::TYPE_BOOLEAN . ' DEFAULT NULL',
			'fixed_payment' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'shefel' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'geva' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'pisga' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'start_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'end_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_rate_created_by', 'rate', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_rate_modified_by', 'rate', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('rate');
	}
}
