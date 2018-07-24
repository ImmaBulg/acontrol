<?php

use yii\db\Schema;

class m160905_110906_alter_table_site_billing_setting extends \common\components\db\Migration
{
	public function up()
	{
		$this->renameColumn('site_billing_setting', 'money_addition_amount', 'fixed_addition_value');
		$this->renameColumn('site_billing_setting', 'money_addition_comment', 'fixed_addition_comment');
		$this->addColumn('site_billing_setting', 'fixed_addition_type', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
		$this->addColumn('site_billing_setting', 'fixed_addition_load', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->renameColumn('site_billing_setting', 'fixed_addition_value', 'money_addition_amount');
		$this->renameColumn('site_billing_setting', 'fixed_addition_comment', 'money_addition_comment');
		$this->dropColumn('site_billing_setting', 'fixed_addition_type');
		$this->dropColumn('site_billing_setting', 'fixed_addition_load');
	}
}
