<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150724_070539_alter_table_tenant extends Migration
{
	public function up()
	{
		$this->dropColumn('tenant', 'city');
		$this->dropColumn('tenant', 'address');
	}

	public function down()
	{
		$this->addColumn('tenant', 'city', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('tenant', 'address', Schema::TYPE_TEXT . ' DEFAULT NULL');
	}
}
