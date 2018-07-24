<?php

use yii\db\Schema;

class m160301_132217_alter_table_rates extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropColumn('rate', 'type');
		$this->dropColumn('site_billing_setting', 'rate');
		$this->dropColumn('tenant_billing_setting', 'rate');
	}

	public function down()
	{
		$this->addColumn('rate', 'type', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
		$this->addColumn('site_billing_setting', 'rate', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
		$this->addColumn('tenant_billing_setting', 'rate', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');		
	}
}
