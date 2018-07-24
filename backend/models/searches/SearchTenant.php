<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\Tenant;
use common\models\TenantBillingSetting;
use common\models\RateType;

/**
 * SearchTenant is the class for search site tenants.
 */
class SearchTenant extends Search
{
	public $modelClass = '\backend\models\searches\models\Tenant';

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
			'relationSite',
			'relationSite.relationSiteBillingSetting',
			'relationSite.relationSiteBillingSetting.relationRateType rate_type_site',
			'relationTenantBillingSetting',
			'relationTenantBillingSetting.relationRateType rate_type_tenant',
		], 'INNER JOIN');

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
				'tenant_name' => SORT_ASC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'tenant_name' => [
					'asc' => ['name' => SORT_ASC],
					'desc' => ['name' => SORT_DESC],
				],
				'square_meters' => [
					'asc' => ['square_meters' => SORT_ASC],
					'desc' => ['square_meters' => SORT_DESC],
				],
				'entrance_date' => [
					'asc' => ['entrance_date' => SORT_ASC],
					'desc' => ['entrance_date' => SORT_DESC],
				],
				'exit_date' => [
					'asc' => ['exit_date' => SORT_ASC],
					'desc' => ['exit_date' => SORT_DESC],
				],
				'old_id' => [
					'asc' => ['old_id' => SORT_ASC],
					'desc' => ['old_id' => SORT_DESC],
				],
				'old_channel_id' => [
					'asc' => ['old_channel_id' => SORT_ASC],
					'desc' => ['old_channel_id' => SORT_DESC],
				],
				'created_at' => [
					'asc' => ['created_at' => SORT_ASC],
					'desc' => ['created_at' => SORT_DESC],
				],
				'to_issue' => [
					'asc' => ['to_issue' => SORT_ASC],
					'desc' => ['to_issue' => SORT_DESC],
				],
				'rate_type_id' => [
					'asc' => [
						'rate_type_tenant.name' => SORT_ASC,
						'rate_type_site.name' => SORT_ASC,
					],
					'desc' => [
						'rate_type_tenant.name' => SORT_DESC,
						'rate_type_site.name' => SORT_DESC,
					],
				],
				'fixed_payment' => [
					'asc' => [
						TenantBillingSetting::tableName() .'.fixed_payment' => SORT_ASC,
						SiteBillingSetting::tableName() .'.fixed_payment' => SORT_ASC,
					],
					'desc' => [
						TenantBillingSetting::tableName() .'.fixed_payment' => SORT_DESC,
						SiteBillingSetting::tableName() .'.fixed_payment' => SORT_DESC,
					],
				],
				'site_name' => [
					'asc' => [
						Site::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						Site::tableName() .'.name' => SORT_DESC,
					],
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
						 * Site name
						 */
						case 'site_name':
							$query->andFilterWhere(['like', Site::tableName() .'.name', $value]);
							break;

						/*
						 * Name
						 */
						case 'tenant_name':
							$query->andFilterWhere(['like', "$t.name", $value]);
							break;

						/*
						 * Entrance date
						 */
						case 'entrance_date':
							$query->andWhere("$t.entrance_date >= :entrance_date", [
								'entrance_date' => Yii::$app->formatter->modifyTimestamp($value, 'midnight'),
							]);
							break;

						/*
						 * Exit date
						 */
						case 'exit_date':
							$query->andWhere("$t.exit_date <= :exit_date", [
								'exit_date' => Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1,
							]);
							break;

						/*
						 * Square meters
						 */
						case 'square_meters':
							$query->andFilterWhere(['like', "$t.square_meters", $value. '%', false]);
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
							$query->andWhere([
								'or',
								[TenantBillingSetting::tableName() .'.rate_type_id' => $value],
								[
									'and',
									[SiteBillingSetting::tableName() .'.rate_type_id' => $value],
									TenantBillingSetting::tableName() .'.rate_type_id IS NULL',
								],
							]);
							break;

						/*
						 * Fixed payment
						 */
						case 'fixed_payment':
							$query->andFilterWhere([
								'or',
								[
									'and',
									['like', TenantBillingSetting::tableName() .'.fixed_payment', $value. '%', false],
									TenantBillingSetting::tableName() .'.fixed_payment IS NOT NULL',
								],
								['like', SiteBillingSetting::tableName() .'.fixed_payment', $value. '%', false],
							]);
							break;

						/*
						 * Old ID
						 */
						case 'old_id':
							$query->andFilterWhere(['like', "$t.old_id", $value]);
							break;

						/*
						 * Old channel ID
						 */
						case 'old_channel_id':
							$query->andFilterWhere(['like', "$t.old_channel_id", $value]);
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
