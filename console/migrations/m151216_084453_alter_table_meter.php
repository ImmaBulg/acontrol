<?php

use yii\db\Schema;

use common\models\Meter;

class m151216_084453_alter_table_meter extends \common\components\db\Migration
{
	public function up()
	{
		Meter::updateAll(['start_date' => strtotime('01-01-2010')], 'start_date IS NULL');
	}

	public function down()
	{
		
	}
}
