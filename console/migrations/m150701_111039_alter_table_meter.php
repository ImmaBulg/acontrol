<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150701_111039_alter_table_meter extends Migration
{
	public function up()
	{
		$this->addColumn('meter', 'start_date', Schema::TYPE_INTEGER . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('meter', 'start_date');
	}
}
