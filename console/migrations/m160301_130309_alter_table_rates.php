<?php

use yii\db\Schema;

class m160301_130309_alter_table_rates extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('rate', 'rate_type_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_rate_rate_type_id', 'rate', 'rate_type_id', 'rate_type', 'id', 'CASCADE', 'RESTRICT');

		$this->addColumn('site_billing_setting', 'rate_type_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_site_billing_setting_rate_type_id', 'site_billing_setting', 'rate_type_id', 'rate_type', 'id', 'SET NULL', 'RESTRICT');

		$this->addColumn('tenant_billing_setting', 'rate_type_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_tenant_billing_setting_rate_type_id', 'tenant_billing_setting', 'rate_type_id', 'rate_type', 'id', 'SET NULL', 'RESTRICT');
	}

	public function down()
	{
		$this->dropForeignKey('FK_rate_rate_type_id', 'rate');
		$this->dropColumn('rate', 'rate_type_id');

		$this->dropForeignKey('FK_site_billing_setting_rate_type_id', 'site_billing_setting');
		$this->dropColumn('site_billing_setting', 'rate_type_id');

		$this->dropForeignKey('FK_tenant_billing_setting_rate_type_id', 'tenant_billing_setting');
		$this->dropColumn('tenant_billing_setting', 'rate_type_id');
	}
}
