<?php namespace api\models\searches;

use Yii;
use api\components\data\Search;

/**
 * SearchAirMeterRawData is the class for search air-meter-raw-data.
 */
class SearchAirMeterRawData extends Search
{
	public $modelClass = '\api\models\AirMeterRawData';

	/**
	 * @inheritdoc
	 */
	public function getDefaultQuery()
	{
        /**
         * @var $modelClass \api\models\AirMeterRawData
         */
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
        /**
         * @var $modelClass \api\models\AirMeterRawData
         */
		$modelClass = $this->modelClass;

		return [
			'sortParam' => $modelClass::SORT_PARAM,
			'defaultOrder' => [
				'datetime' => SORT_DESC,
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
				'datetime' => [
					'asc' => ['datetime' => SORT_ASC],
					'desc' => ['datetime' => SORT_DESC],
				],

			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function setFilters()
	{
        /**
         * @var $modelClass \api\models\AirMeterRawData
         */
		$filters = $this->getFilterParameters();

		if ($filters != null) {
			$modelClass = $this->modelClass;
			$t = $modelClass::tableName();
			$query = $this->getQuery();
			$this->getModel();

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
						case 'datetime':
							$query->andWhere("$t.datetime >= :date_from AND $t.datetime <= :date_to", [
								'date_from' => Yii::$app->formatter->modifyTimestamp($value, 'midnight'),
								'date_to' => Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1,
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
