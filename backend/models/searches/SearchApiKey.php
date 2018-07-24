<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\ApiKey;

/**
 * SearchApiKey is the class for search api keys.
 */
class SearchApiKey extends Search
{
	public $modelClass = '\backend\models\searches\models\ApiKey';

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
		]]);

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
				'created_at' => SORT_ASC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'api_key' => [
					'asc' => ['api_key' => SORT_ASC],
					'desc' => ['api_key' => SORT_DESC],
				],
				'status' => [
					'asc' => ['status' => SORT_ASC],
					'desc' => ['status' => SORT_DESC],
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
							$query->andFilterWhere(['like', "$t.id", $value. '%', false]);
							break;

						/*
						 * API key
						 */
						case 'api_key':
							$query->andFilterWhere(['like', "$t.api_key", $value]);
							break;

						/*
						 * Status
						 */
						case 'status':
							$query->andWhere(["$t.status" => $value]);
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
