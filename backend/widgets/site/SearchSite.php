<?php
namespace backend\widgets\site;

use \DateTime;
use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\Tenant;
use common\models\Report;
use common\models\RuleSingleChannel;
use common\models\RuleGroupLoad;
use common\models\RuleFixedLoad;
use common\components\i18n\Formatter;

/**
 * SearchSite is the class for search sites.
 */
class SearchSite extends Search
{
	public $modelClass = '\backend\models\searches\models\Site';

	/**
	 * @inheritdoc
	 */
	public function getDefaultQuery()
	{
		$modelClass = $this->modelClass;
		$t = $modelClass::tableName();
		$query = $modelClass::find()->where(['in', "$t.status", [
			$modelClass::STATUS_ACTIVE,
		]])->andWhere(['in', "$t.to_issue", [
			$modelClass::TO_ISSUE_MANUAL,
			$modelClass::TO_ISSUE_AUTOMATIC,
		]])
		->joinWith([
			'relationSiteBillingSetting',
			'relationTenantsToIssued',
		], 'INNER JOIN')
		->joinWith([
			'relationReports' => function($query) {
				$query->andOnCondition(['is_automatically_generated' => true]);
			},
		])
		->andWhere("$t.auto_issue_reports IS NOT NULL")
		->groupBy(["$t.id"]);

		return $query;	
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultSort()
	{
		$modelClass = $this->modelClass;

		return [
			'sortParam' => $modelClass::SORT_PARAM,
			'defaultOrder' => [
				'site_name' => SORT_ASC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'site_name' => [
					'asc' => ['name' => SORT_ASC],
					'desc' => ['name' => SORT_DESC],
				],
				'to_issue' => [
					'asc' => ['to_issue' => SORT_ASC],
					'desc' => ['to_issue' => SORT_DESC],
				],
				'billing_day' => [
					'asc' => [
						SiteBillingSetting::tableName() .'.billing_day' => SORT_ASC,
					],
					'desc' => [
						SiteBillingSetting::tableName() .'.billing_day' => SORT_DESC,
					],
				],
				'issue_dates' => [
					'asc' => [
						SiteBillingSetting::tableName() .'.billing_day' => SORT_ASC,
					],
					'desc' => [
						SiteBillingSetting::tableName() .'.billing_day' => SORT_DESC,
					],
				],
				'last_issue_date' => [
					'asc' => [
						Report::tableName() .'.to_date' => SORT_ASC,
					],
					'desc' => [
						Report::tableName() .'.to_date' => SORT_DESC,
					],
				],
				'cronjob_latest_issue_date_check' => [
					'asc' => ['cronjob_latest_issue_date_check' => SORT_ASC],
					'desc' => ['cronjob_latest_issue_date_check' => SORT_DESC],
				],
				'issue_tenants' => [
					'asc' => [
						'COUNT(tenant.id)' => SORT_ASC,
					],
					'desc' => [
						'COUNT(tenant.id)' => SORT_DESC,
					],
				],
			],
		];
	}
}
