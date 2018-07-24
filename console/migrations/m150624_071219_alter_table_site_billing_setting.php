<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150624_071219_alter_table_site_billing_setting extends Migration
{
	public function up()
	{
		$this->addColumn('site_billing_setting', 'rate', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('site_billing_setting', 'rate');
	}
}
