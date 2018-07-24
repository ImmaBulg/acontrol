<?php

use yii\db\Schema;

class m160420_074321_alter_table_site_billing_setting extends \common\components\db\Migration
{
	public function up()
	{
		$this->dropColumn('site_billing_setting', 'report_order');
	}

	public function down()
	{
		$this->addColumn('site_billing_setting', 'report_order', Schema::TYPE_TEXT . ' DEFAULT NULL');
	}
}
