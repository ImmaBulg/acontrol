<?php

use yii\db\Schema;
use common\models\SiteBillingSetting;

class m160905_122117_alter_table_site_billing_setting extends \common\components\db\Migration
{
	public function up()
	{
		SiteBillingSetting::updateAll(['fixed_addition_type' => SiteBillingSetting::FIXED_ADDITION_TYPE_MONEY], 'fixed_addition_type IS NULL');
		SiteBillingSetting::updateAll(['fixed_addition_load' => SiteBillingSetting::FIXED_ADDITION_LOAD_FLAT], 'fixed_addition_load IS NULL');
	}

	public function down()
	{
		SiteBillingSetting::updateAll(['fixed_addition_type' => null], ['fixed_addition_type' => SiteBillingSetting::FIXED_ADDITION_TYPE_MONEY]);
		SiteBillingSetting::updateAll(['fixed_addition_load' => null], ['fixed_addition_load' => SiteBillingSetting::FIXED_ADDITION_LOAD_FLAT]);
	}
}
