<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150616_122918_table_tenant extends Migration
{
	public function up()
	{
		$this->createTable('tenant', [
			'id' => Schema::TYPE_PK,
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'type' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'to_issue' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'square_meters' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'entrance_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'exit_date' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_tenant_user_id', 'tenant', 'user_id', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_site_id', 'tenant', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_created_by', 'tenant', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_tenant_modified_by', 'tenant', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('tenant');
	}
}
