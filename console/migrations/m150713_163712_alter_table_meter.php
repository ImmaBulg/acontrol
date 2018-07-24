<?php

use yii\db\Schema;
use common\components\db\Migration;

class m150713_163712_alter_table_meter extends Migration
{
	public function up()
	{
		$this->dropColumn('meter', 'current_multiplier');
		$this->dropColumn('meter', 'voltage_multiplier');
	}

	public function down()
	{
		$this->addColumn('meter', 'current_multiplier', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
		$this->addColumn('meter', 'voltage_multiplier', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
	}
}
