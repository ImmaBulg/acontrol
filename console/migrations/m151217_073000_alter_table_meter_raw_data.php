<?php

use yii\db\Schema;

class m151217_073000_alter_table_meter_raw_data extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('meter_raw_data', 'export_shefel', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
		$this->addColumn('meter_raw_data', 'export_geva', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
		$this->addColumn('meter_raw_data', 'export_pisga', Schema::TYPE_DOUBLE . ' DEFAULT NULL');

		$this->addColumn('meter_raw_data', 'kvar_shefel', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
		$this->addColumn('meter_raw_data', 'kvar_geva', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
		$this->addColumn('meter_raw_data', 'kvar_pisga', Schema::TYPE_DOUBLE . ' DEFAULT NULL');
	}

	public function down()
	{
		$this->dropColumn('meter_raw_data', 'export_shefel');
		$this->dropColumn('meter_raw_data', 'export_geva');
		$this->dropColumn('meter_raw_data', 'export_pisga');

		$this->dropColumn('meter_raw_data', 'kvar_shefel');
		$this->dropColumn('meter_raw_data', 'kvar_geva');
		$this->dropColumn('meter_raw_data', 'kvar_pisga');
	}
}
