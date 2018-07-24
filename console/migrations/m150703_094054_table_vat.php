<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150703_094054_table_vat extends Migration
{
	public function up()
	{
		$this->createTable('vat', [
			'id' => Schema::TYPE_PK,
			'vat' => Schema::TYPE_DOUBLE . ' NOT NULL',
			'start_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'end_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);
		
		$this->addForeignKey('FK_vat_created_by', 'vat', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_vat_modified_by', 'vat', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('vat');
	}
}
