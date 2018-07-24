<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\MeterChannel;
use common\models\RuleFixedLoad;

/**
 * SearchRuleFixedLoad is the class for search fixed load rules.
 */
class SearchRuleFixedLoad extends Search
{
	public $modelClass = '\backend\models\searches\models\RuleFixedLoad';

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
			'relationTenant',
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
				'id' => SORT_ASC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'use_type' => [
					'asc' => ['use_type' => SORT_ASC],
					'desc' => ['use_type' => SORT_DESC],
				],
				'use_frequency' => [
					'asc' => ['use_frequency' => SORT_ASC],
					'desc' => ['use_frequency' => SORT_DESC],
				],
				'value' => [
					'asc' => ['value' => SORT_ASC],
					'desc' => ['value' => SORT_DESC],
				],
				'pisga' => [
					'asc' => ['pisga' => SORT_ASC],
					'desc' => ['pisga' => SORT_DESC],
				],
				'geva' => [
					'asc' => ['geva' => SORT_ASC],
					'desc' => ['geva' => SORT_DESC],
				],
				'shefel' => [
					'asc' => ['shefel' => SORT_ASC],
					'desc' => ['shefel' => SORT_DESC],
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
						 * Use type
						 */
						case 'use_type':
							$query->andWhere(["$t.use_type" => $value]);
							break;

						/*
						 * Use frequency
						 */
						case 'use_frequency':
							$query->andWhere(["$t.use_frequency" => $value]);
							break;

						/*
						 * Value
						 */
						case 'value':
							$query->andFilterWhere(['like', "$t.value", $value. '%', false]);
							break;

						/*
						 * Pisga
						 */
						case 'pisga':
							$query->andFilterWhere(['like', "$t.pisga", $value. '%', false]);
							break;

						/*
						 * Geva
						 */
						case 'geva':
							$query->andFilterWhere(['like', "$t.geva", $value. '%', false]);
							break;

						/*
						 * Shefel
						 */
						case 'shefel':
							$query->andFilterWhere(['like', "$t.shefel", $value. '%', false]);
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
