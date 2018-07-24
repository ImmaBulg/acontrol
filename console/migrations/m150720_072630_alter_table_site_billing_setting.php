<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150720_072630_alter_table_site_billing_setting extends Migration
{
	public function up()
	{
		$this->addColumn('site_billing_setting', 'fixed_payment', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('site_billing_setting', 'fixed_payment');
	}
}
