<?php

use yii\db\Schema;

class m151118_115305_alter_table_meter extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('meter', 'site_id', Schema::TYPE_INTEGER . ' DEFAULT NULL');
		$this->addForeignKey('FK_meter_site_id', 'meter', 'site_id', 'site', 'id', 'CASCADE', 'RESTRICT');
	}

	public function down()
	{
		$this->dropForeignKey('FK_meter_site_id', 'meter');
		$this->dropColumn('meter', 'site_id');
	}
}
