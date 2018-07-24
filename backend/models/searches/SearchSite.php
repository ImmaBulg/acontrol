<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\User;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\RateType;
use common\models\Tenant;

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
			$modelClass::STATUS_INACTIVE,
			$modelClass::STATUS_ACTIVE,
		]])->joinWith([
			'relationUser',
			'relationSiteBillingSetting',
			'relationSiteBillingSetting.relationRateType',
			'relationTenants',
		])->groupBy(["$t.id"]);

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
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'site_name' => [
					'asc' => ['name' => SORT_ASC],
					'desc' => ['name' => SORT_DESC],
				],
				'electric_company_id' => [
					'asc' => ['electric_company_id' => SORT_ASC],
					'desc' => ['electric_company_id' => SORT_DESC],
				],
				'old_id' => [
					'asc' => ['old_id' => SORT_ASC],
					'desc' => ['old_id' => SORT_DESC],
				],
				'created_at' => [
					'asc' => ['created_at' => SORT_ASC],
					'desc' => ['created_at' => SORT_DESC],
				],
				'user_name' => [
					'asc' => [
						User::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						User::tableName() .'.name' => SORT_DESC,
					],
				],
				'to_issue' => [
					'asc' => ['to_issue' => SORT_ASC],
					'desc' => ['to_issue' => SORT_DESC],
				],
				'rate_type_id' => [
					'asc' => [
						RateType::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						RateType::tableName() .'.name' => SORT_DESC,
					],
				],
				'fixed_payment' => [
					'asc' => [
						SiteBillingSetting::tableName() .'.fixed_payment' => SORT_ASC,
					],
					'desc' => [
						SiteBillingSetting::tableName() .'.fixed_payment' => SORT_DESC,
					],
				],
				'square_meters' => [
					'asc' => ['SUM(IFNULL(' .Tenant::tableName(). '.square_meters, 0))' => SORT_ASC],
					'desc' => ['SUM(IFNULL(' .Tenant::tableName(). '.square_meters, 0))' => SORT_DESC],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function setFilters()
	{
		$filters = $this->getFilterParameters();

		if ($filters != null) {
			$modelClass = $this->modelClass;
			$t = $modelClass::tableName();
			$query = $this->getQuery();
			$model = $this->getModel();

			foreach ($filters as $attribute => $value) {
				if ($value != null) {
					switch ($attribute) {
						/*
						 * ID
						 */
						case 'id':
							$query->andFilterWhere(['like', "$t.id", $value. '%', false]);
							break;
						
						/*
						 * User name
						 */
						case 'user_name':
							$query->andFilterWhere(['like', User::tableName() .'.name', $value]);
							break;

						/*
						 * Name
						 */
						case 'site_name':
							$query->andFilterWhere(['like', "$t.name", $value]);
							break;

						/*
						 * Electric company ID
						 */
						case 'electric_company_id':
							$query->andFilterWhere(['like', "$t.electric_company_id", $value]);
							break;

						/*
						 * To issue
						 */
						case 'to_issue':
							$query->andWhere(["$t.to_issue" => $value]);
							break;

						/*
						 * Rate type ID
						 */
						case 'rate_type_id':
							$query->andWhere([SiteBillingSetting::tableName() .'.rate_type_id' => $value]);
							break;

						/*
						 * Fixed payment
						 */
						case 'fixed_payment':
							$query->andFilterWhere(['like', SiteBillingSetting::tableName() .'.fixed_payment', $value. '%', false]);
							break;

						/*
						 * Square meters
						 */
						case 'square_meters':
							$query->andHaving('SUM(IFNULL(' .Tenant::tableName(). '.square_meters, 0)) = :square_meters', [
								'square_meters' => $value,
							]);
							break;

						/*
						 * Old ID
						 */
						case 'old_id':
							$query->andFilterWhere(['like', "$t.old_id", $value]);
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
