<?php
namespace frontend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Report;
use common\models\Site;
use common\models\Tenant;
use common\models\User;

/**
 * SearchReport is the class for search reports.
 */
class SearchReport extends Search
{
	public $modelClass = '\frontend\models\searches\models\Report';

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
			'relationSiteOwner',
			'relationSite',
			'relationTenantReports',
			'relationTenantReports.relationTenant',
		], 'LEFT JOIN')->groupBy(["$t.id"]);

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
				'id' => SORT_DESC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'from_date' => [
					'asc' => ['from_date' => SORT_ASC],
					'desc' => ['from_date' => SORT_DESC],
				],
				'to_date' => [
					'asc' => ['to_date' => SORT_ASC],
					'desc' => ['to_date' => SORT_DESC],
				],
				'type' => [
					'asc' => ['type' => SORT_ASC],
					'desc' => ['type' => SORT_DESC],
				],
				'site_owner_name' => [
					'asc' => [
						User::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						User::tableName() .'.name' => SORT_DESC,
					],
				],
				'name' => [
					'asc' => [
						'type' => SORT_ASC,
						'from_date' => SORT_ASC,
						'to_date' => SORT_ASC,
					],
					'desc' => [
						'type' => SORT_DESC,
						'from_date' => SORT_DESC,
						'to_date' => SORT_DESC,
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
				'tenant_name' => [
					'asc' => [
						Tenant::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						Tenant::tableName() .'.name' => SORT_DESC,
					],
				],
				'is_public' => [
					'asc' => ['is_public' => SORT_ASC],
					'desc' => ['is_public' => SORT_DESC],
				],
				'level' => [
					'asc' => ['level' => SORT_ASC],
					'desc' => ['level' => SORT_DESC],
				],
				'created_at' => [
					'asc' => ['created_at' => SORT_ASC],
					'desc' => ['created_at' => SORT_DESC],
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
							$query->andFilterWhere(['in', "$t.id", explode(',', $value)]);
							break;

						/*
						 * Site owner name
						 */
						case 'site_owner_name':
							$query->andFilterWhere(['like', User::tableName() .'.name', $value]);
							break;

						/*
						 * Site name
						 */
						case 'site_name':
							$query->andFilterWhere(['like', Site::tableName() .'.name', $value]);
							break;

						/*
						 * Tenant name
						 */
						case 'tenant_name':
							$query->andFilterWhere(['like', Tenant::tableName() .'.name', $value]);
							break;

						/*
						 * From date
						 */
						case 'from_date':
							$query->andWhere("$t.from_date >= :from_date", [
								'from_date' => Yii::$app->formatter->modifyTimestamp($value, 'midnight'),
							]);
							break;

						/*
						 * To date
						 */
						case 'to_date':
							$query->andWhere("$t.to_date <= :to_date", [
								'to_date' => Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1,
							]);
							break;

						/*
						 * Type
						 */
						case 'type':
							$query->andFilterWhere(['like', "$t.type", $value]);
							break;

						/*
						 * Is public
						 */
						case 'is_public':
							$query->andFilterWhere(["$t.is_public" => $value]);
							break;

						/*
						 * Level
						 */
						case 'level':
							$query->andFilterWhere(["$t.level" => $value]);
							break;

						/*
						 * Created at
						 */
						case 'created_at':
							$query->andWhere("$t.created_at >= :created_at_from AND $t.created_at <= :created_at_to", [
								'created_at_from' => Yii::$app->formatter->modifyTimestamp($value, 'midnight'),
								'created_at_to' => Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1,
							]);
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
