<?php

use yii\db\Schema;
use common\models\Tenant;

class m160914_062016_alter_table_tenant extends \common\components\db\Migration
{
	public function up()
	{
		$this->addColumn('tenant', 'is_visible_on_dat_file', Schema::TYPE_BOOLEAN . ' DEFAULT NULL');
		Tenant::updateAll(['is_visible_on_dat_file' => 1], 'id IS NOT NULL');
	}

	public function down()
	{
		$this->dropColumn('tenant', 'is_visible_on_dat_file');
	}
}
