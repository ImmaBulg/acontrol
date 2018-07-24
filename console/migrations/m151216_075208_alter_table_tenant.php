<?php

use yii\db\Schema;
use yii\helpers\Json;

use common\models\Site;
use common\models\Tenant;
use common\models\ReportTemplate;

class m151216_075208_alter_table_tenant extends \common\components\db\Migration
{
	public function up()
	{
		Tenant::updateAll(['included_reports' => Json::encode([
			ReportTemplate::TYPE_NIS,
			ReportTemplate::TYPE_KWH,
			ReportTemplate::TYPE_SUMMARY,
			ReportTemplate::TYPE_METERS,
			ReportTemplate::TYPE_NIS_KWH,
			ReportTemplate::TYPE_RATES_COMPRASION,
			ReportTemplate::TYPE_TENANT_BILLS,
			ReportTemplate::TYPE_YEARLY,
		])], ['in', 'to_issue', [
			Site::TO_ISSUE_AUTOMATIC,
			Site::TO_ISSUE_MANUAL,
		]]);

		Tenant::updateAll(['included_reports' => Json::encode([
			ReportTemplate::TYPE_SUMMARY,
		])], ['in', 'to_issue', [
			Site::TO_ISSUE_NO,
		]]);

		Tenant::updateAll(['entrance_date' => strtotime('01-01-2010')], 'entrance_date IS NULL');
	}

	public function down()
	{
		Tenant::updateAll(['included_reports' => NULL], 'included_reports IS NOT NULL');
	}
}
