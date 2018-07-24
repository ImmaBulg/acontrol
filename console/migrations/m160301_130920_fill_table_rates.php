<?php

use yii\db\Schema;
use common\models\Rate;
use common\models\RateType;
use common\models\SiteBillingSetting;
use common\models\TenantBillingSetting;

class m160301_130920_fill_table_rates extends \common\components\db\Migration
{
	public function up()
	{
		/**
		 * Rate Table
		 */

		// Home
		Rate::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Home'])->scalar(),
		], [
			'type' => 1,
		]);

		// General
		Rate::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'General'])->scalar(),
		], [
			'type' => 2,
		]);

		// Street lighting
		Rate::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Street lighting'])->scalar(),
		], [
			'type' => 3,
		]);

		// Low
		Rate::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Low'])->scalar(),
		], [
			'type' => 4,
		]);

		// High
		Rate::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'High'])->scalar(),
		], [
			'type' => 5,
		]);

		// Supreme
		Rate::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Supreme'])->scalar(),
		], [
			'type' => 6,
		]);

		// AVG
		Rate::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'AVG'])->scalar(),
		], [
			'type' => 7,
		]);

		// Pisga mobile
		Rate::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Pisga mobile'])->scalar(),
		], [
			'type' => 8,
		]);

		/**
		 * Site Billing Setting Table
		 */

		// Home
		SiteBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Home'])->scalar(),
		], [
			'rate' => 1,
		]);

		// General
		SiteBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'General'])->scalar(),
		], [
			'rate' => 2,
		]);

		// Street lighting
		SiteBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Street lighting'])->scalar(),
		], [
			'rate' => 3,
		]);

		// Low
		SiteBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Low'])->scalar(),
		], [
			'rate' => 4,
		]);

		// High
		SiteBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'High'])->scalar(),
		], [
			'rate' => 5,
		]);

		// Supreme
		SiteBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Supreme'])->scalar(),
		], [
			'rate' => 6,
		]);

		// AVG
		SiteBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'AVG'])->scalar(),
		], [
			'rate' => 7,
		]);

		// Pisga mobile
		SiteBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Pisga mobile'])->scalar(),
		], [
			'rate' => 8,
		]);

		/**
		 * Tenant Billing Setting Table
		 */

		// Home
		TenantBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Home'])->scalar(),
		], [
			'rate' => 1,
		]);

		// General
		TenantBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'General'])->scalar(),
		], [
			'rate' => 2,
		]);

		// Street lighting
		TenantBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Street lighting'])->scalar(),
		], [
			'rate' => 3,
		]);

		// Low
		TenantBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Low'])->scalar(),
		], [
			'rate' => 4,
		]);

		// High
		TenantBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'High'])->scalar(),
		], [
			'rate' => 5,
		]);

		// Supreme
		TenantBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Supreme'])->scalar(),
		], [
			'rate' => 6,
		]);

		// AVG
		TenantBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'AVG'])->scalar(),
		], [
			'rate' => 7,
		]);

		// Pisga mobile
		TenantBillingSetting::updateAll([
			'rate_type_id' => RateType::find()->select(['id'])->where(['name' => 'Pisga mobile'])->scalar(),
		], [
			'rate' => 8,
		]);
	}

	public function down()
	{
		Rate::updateAll(['rate_type_id' => NULL]);
		SiteBillingSetting::updateAll(['rate_type_id' => NULL]);
		TenantBillingSetting::updateAll(['rate_type_id' => NULL]);
	}
}
