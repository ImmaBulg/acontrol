<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150720_070759_alter_table_tenant_contact extends Migration
{
	public function up()
	{
		$this->addColumn('tenant_contact', 'cell_phone', Schema::TYPE_STRING . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('tenant_contact', 'cell_phone');
	}
}
