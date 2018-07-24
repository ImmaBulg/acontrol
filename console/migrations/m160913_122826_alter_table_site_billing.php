<?php

use yii\db\Schema;
use common\models\SiteBillingSetting;

class m160913_122826_alter_table_site_billing extends \common\components\db\Migration
{
	public function up()
	{
		SiteBillingSetting::updateAll([
			'fixed_addition_value' => null,
			'fixed_addition_comment' => null,
			'fixed_addition_type' => null,
			'fixed_addition_load' => null,
		], 'site_id IS NOT NULL');
	}

	public function down()
	{
		SiteBillingSetting::updateAll([
			'fixed_addition_value' => null,
			'fixed_addition_comment' => null,
			'fixed_addition_type' => null,
			'fixed_addition_load' => null,
		], 'site_id IS NOT NULL');		
	}
}
