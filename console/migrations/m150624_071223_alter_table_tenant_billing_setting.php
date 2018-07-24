<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150624_071223_alter_table_tenant_billing_setting extends Migration
{
	public function up()
	{
		$this->alterColumn('tenant_billing_setting', 'rate', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
	}

	public function down(){}
}
