<?php
namespace api\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use api\components\data\Search;

/**
 * SearchMeterRawData is the class for search meter-raw-data.
 */
class SearchMeterRawData extends Search
{
	public $modelClass = '\api\models\ElectricityMeterRawData';

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
				'date' => SORT_DESC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'meter_id' => [
					'asc' => ['meter_id' => SORT_ASC],
					'desc' => ['meter_id' => SORT_DESC],
				],
				'channel_id' => [
					'asc' => ['channel_id' => SORT_ASC],
					'desc' => ['channel_id' => SORT_DESC],
				],
				'date' => [
					'asc' => ['date' => SORT_ASC],
					'desc' => ['date' => SORT_DESC],
				],
				'reading_shefel' => [
					'asc' => ['reading_shefel' => SORT_ASC],
					'desc' => ['reading_shefel' => SORT_DESC],
				],
				'reading_geva' => [
					'asc' => ['reading_geva' => SORT_ASC],
					'desc' => ['reading_geva' => SORT_DESC],
				],
				'reading_pisga' => [
					'asc' => ['reading_pisga' => SORT_ASC],
					'desc' => ['reading_pisga' => SORT_DESC],
				],
				'max_shefel' => [
					'asc' => ['max_shefel' => SORT_ASC],
					'desc' => ['max_shefel' => SORT_DESC],
				],
				'max_geva' => [
					'asc' => ['max_geva' => SORT_ASC],
					'desc' => ['max_geva' => SORT_DESC],
				],
				'max_pisga' => [
					'asc' => ['max_pisga' => SORT_ASC],
					'desc' => ['max_pisga' => SORT_DESC],
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
						 * Meter ID
						 */
						case 'meter_id':
							$query->andFilterWhere(['like', "$t.meter_id", $value]);
							break;

						/*
						 * Channel ID
						 */
						case 'channel_id':
							$query->andFilterWhere(['like', "$t.channel", $value]);
							break;

						/*
						 * Date
						 */
						case 'date':
							$query->andWhere("$t.date >= :date_from AND $t.date <= :date_to", [
								'date_from' => Yii::$app->formatter->modifyTimestamp($value, 'midnight'),
								'date_to' => Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1,
							]);
							break;

						/*
						 * Reading shefel
						 */
						case 'reading_shefel':
							$query->andFilterWhere(['like', "$t.reading_shefel", $value. '%', false]);
							break;

						/*
						 * Reading geva
						 */
						case 'reading_geva':
							$query->andFilterWhere(['like', "$t.reading_geva", $value. '%', false]);
							break;

						/*
						 * Reading pisga
						 */
						case 'reading_pisga':
							$query->andFilterWhere(['like', "$t.reading_pisga", $value. '%', false]);
							break;

						/*
						 * Max shefel
						 */
						case 'max_shefel':
							$query->andFilterWhere(['like', "$t.max_shefel", $value. '%', false]);
							break;

						/*
						 * Max geva
						 */
						case 'max_geva':
							$query->andFilterWhere(['like', "$t.max_geva", $value. '%', false]);
							break;

						/*
						 * Max pisga
						 */
						case 'max_pisga':
							$query->andFilterWhere(['like', "$t.max_pisga", $value. '%', false]);
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
