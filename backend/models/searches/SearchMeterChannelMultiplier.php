<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\MeterChannelMultiplier;

/**
 * SearchMeterChannelMultiplier is the class for search meter channel multipliers.
 */
class SearchMeterChannelMultiplier extends Search
{
	public $modelClass = '\backend\models\searches\models\MeterChannelMultiplier';

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
				'end_date' => SORT_DESC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'current_multiplier' => [
					'asc' => ['current_multiplier' => SORT_ASC],
					'desc' => ['current_multiplier' => SORT_DESC],
				],
				'voltage_multiplier' => [
					'asc' => ['voltage_multiplier' => SORT_ASC],
					'desc' => ['voltage_multiplier' => SORT_DESC],
				],
				'start_date' => [
					'asc' => ['start_date' => SORT_ASC],
					'desc' => ['start_date' => SORT_DESC],
				],
				'end_date' => [
					'asc' => ['end_date' => SORT_ASC],
					'desc' => ['end_date' => SORT_DESC],
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
						 * Start date
						 */
						case 'start_date':
							$query->andWhere("$t.start_date >= :start_date", [
								'start_date' => Yii::$app->formatter->modifyTimestamp($value, 'midnight'),
							]);
							break;

						/*
						 * End date
						 */
						case 'end_date':
							$query->andWhere("$t.end_date <= :end_date", [
								'end_date' => Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1,
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
