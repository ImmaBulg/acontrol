<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\RateType;

/**
 * SearchRateType is the class for search rate types.
 */
class SearchRateType extends Search
{
	public $modelClass = '\backend\models\searches\models\RateType';

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
				'id' => SORT_ASC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'name' => [
					'asc' => [
						'name_en' => SORT_ASC,
						'name_he' => SORT_ASC,
					],
					'desc' => [
						'name_en' => SORT_ASC,
						'name_he' => SORT_ASC,
					],
				],
				'type' => [
					'asc' => ['type' => SORT_ASC],
					'desc' => ['type' => SORT_DESC],
				],
				'level' => [
					'asc' => ['level' => SORT_ASC],
					'desc' => ['level' => SORT_DESC],
				],
				'status' => [
					'asc' => ['status' => SORT_ASC],
					'desc' => ['status' => SORT_DESC],
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
			$language = Yii::$app->language;

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
						 * Name
						 */
						case 'name':
							$query->andFilterWhere([
								'or',
								['like', "$t.name_en", $value],
								['like', "$t.name_he", $value],
							]);
							break;

						/*
						 * Type
						 */
						case 'type':
							$query->andFilterWhere(["$t.type" => $value]);
							break;

						/*
						 * Level
						 */
						case 'level':
							$query->andFilterWhere(["$t.level" => $value]);
							break;

						/*
						 * Status
						 */
						case 'status':
							$query->andFilterWhere(["$t.status" => $value]);
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
