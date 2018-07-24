<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\MeterChannel;

/**
 * SearchMeterChannel is the class for search meter channels.
 */
class SearchMeterChannel extends Search
{
	public $modelClass = '\backend\models\searches\models\MeterChannel';

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
				'channel' => SORT_ASC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'channel' => [
					'asc' => ['channel' => SORT_ASC],
					'desc' => ['channel' => SORT_DESC],
				],
				'current_multiplier' => [
					'asc' => ['current_multiplier' => SORT_ASC],
					'desc' => ['current_multiplier' => SORT_DESC],
				],
				'voltage_multiplier' => [
					'asc' => ['voltage_multiplier' => SORT_ASC],
					'desc' => ['voltage_multiplier' => SORT_DESC],
				],
				'old_id' => [
					'asc' => ['old_id' => SORT_ASC],
					'desc' => ['old_id' => SORT_DESC],
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
						 * Channel
						 */
						case 'channel':
							$query->andFilterWhere(['like', "$t.channel", $value. '%', false]);
							break;

						/*
						 * Current multiplier
						 */
						case 'current_multiplier':
							$query->andFilterWhere(['like', "$t.current_multiplier", $value. '%', false]);
							break;

						/*
						 * Voltage multiplier
						 */
						case 'voltage_multiplier':
							$query->andFilterWhere(['like', "$t.voltage_multiplier", $value. '%', false]);
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
