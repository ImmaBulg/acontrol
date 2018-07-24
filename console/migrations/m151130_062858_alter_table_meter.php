<?php

use yii\db\Schema;

class m151130_062858_alter_table_meter extends \common\components\db\Migration
{
	public function up()
	{
		$this->renameColumn('meter', 'phisical_location', 'physical_location');
	}

	public function down()
	{
		$this->renameColumn('meter', 'physical_location', 'phisical_location');
	}
}
