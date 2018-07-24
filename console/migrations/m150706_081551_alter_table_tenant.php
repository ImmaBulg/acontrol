<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150706_081551_alter_table_tenant extends Migration
{
	public function up()
	{
		$this->addColumn('tenant', 'city', Schema::TYPE_STRING . ' DEFAULT NULL');
		$this->addColumn('tenant', 'address', Schema::TYPE_TEXT . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('tenant', 'city');
		$this->dropColumn('tenant', 'address');
	}
}
