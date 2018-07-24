<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150616_081636_table_site_billing_setting extends Migration
{
	public function up()
	{
		$this->createTable('site_billing_setting', [
			'site_id' => Schema::TYPE_INTEGER . ' NOT NULL PRIMARY KEY',
			'billing_day' => Schema::TYPE_STRING . ' DEFAULT NULL',
			'report_order' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'include_multiplier' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'include_vat' => Schema::TYPE_BOOLEAN . ' NOT NULL',
			'comment' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'money_addition_amount' => Schema::TYPE_DOUBLE . ' DEFAULT NULL',
			'money_addition_comment' => Schema::TYPE_TEXT . ' DEFAULT NULL',
			'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
			'modified_at' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'created_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
			'modified_by' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
		], $this->dbOptions);

		$this->addForeignKey('FK_site_billing_setting_site_id', 'site_billing_setting', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_billing_setting_created_by', 'site_billing_setting', 'created_by', 'user', 'id', 'CASCADE', 'RESTRICT');
		$this->addForeignKey('FK_site_billing_setting_modified_by', 'site_billing_setting', 'modified_by', 'user', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropTable('site_billing_setting');
	}
}
