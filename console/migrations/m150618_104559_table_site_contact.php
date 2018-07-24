<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150618_104559_table_site_contact extends Migration
{
	public function up()
	{
		$this->createTable('site_contact', [
			'id' => Schema::TYPE_PK,
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'name' => Schema::TYPE_STRING . ' NOT NULL',
			'email' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'address' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'job' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'phone' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'fax' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'comment' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'status' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_site_contact_site_id', 'site_contact', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_contact_created_by', 'site_contact', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_contact_modified_by', 'site_contact', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('site_contact');
	}
}
