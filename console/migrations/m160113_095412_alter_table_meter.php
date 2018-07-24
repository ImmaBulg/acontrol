<?php

use yii\db\Schema;
use common\models\Meter;

class m160113_095412_alter_table_meter extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('meter', 'data_usage_method', Schema::TYPE_BOOLEAN . ' NOT NULL');
		Meter::updateAll(['data_usage_method' => 1]);
	}

	public function down()
	{
		$this->dropColumn('meter', 'data_usage_method');
	}
}
