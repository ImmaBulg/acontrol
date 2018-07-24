<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Rate;
use common\models\RateType;

/**
 * SearchRate is the class for search rates.
 */
class SearchRate extends Search
{
	public $modelClass = '\backend\models\searches\models\Rate';

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
			'relationRateType',
		]);

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
				'start_date' => SORT_DESC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'rate_type_id' => [
					'asc' => [
						RateType::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						RateType::tableName() .'.name' => SORT_DESC,
					],
				],
				'season' => [
					'asc' => ['season' => SORT_ASC],
					'desc' => ['season' => SORT_DESC],
				],
				'fixed_payment' => [
					'asc' => ['fixed_payment' => SORT_ASC],
					'desc' => ['fixed_payment' => SORT_DESC],
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
						 * Rate Type ID
						 */
						case 'rate_type_id':
							$query->andFilterWhere(["$t.rate_type_id" => $value]);
							break;

						/*
						 * Season
						 */
						case 'season':
							$query->andFilterWhere(["$t.season" => $value]);
							break;

						/*
						 * Fixed payment
						 */
						case 'fixed_payment':
							$query->andFilterWhere(['like', "$t.fixed_payment", $value. '%', false]);
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
