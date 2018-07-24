<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150714_070704_alter_table_site_billing_setting extends Migration
{
	public function up()
	{
		$this->dropColumn('site_billing_setting', 'include_multiplier');
	}

	public function down()
	{
		$this->addColumn('site_billing_setting', 'include_multiplier', Schema::TYPE_BOOLEAN . ' NOT NULL');
	}
}
