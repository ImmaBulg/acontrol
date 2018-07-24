<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Meter;
use common\models\MeterType;
use common\models\Site;
use yii\helpers\VarDumper;

/**
 * SearchMeter is the class for search meters.
 */
class SearchMeter extends Search
{
	public $modelClass = '\backend\models\searches\models\Meter';

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
			'relationMeterType',
		], 'LEFT JOIN');

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
				'name' => SORT_ASC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'name' => [
					'asc' => ['name' => SORT_ASC],
					'desc' => ['name' => SORT_DESC],
				],
				'communication_type' => [
					'asc' => ['communication_type' => SORT_ASC],
					'desc' => ['communication_type' => SORT_DESC],
				],
				'start_date' => [
					'asc' => ['start_date' => SORT_ASC],
					'desc' => ['start_date' => SORT_DESC],
				],
				'status' => [
					'asc' => ['status' => SORT_ASC],
					'desc' => ['status' => SORT_DESC],
				],
				'old_id' => [
					'asc' => ['old_id' => SORT_ASC],
					'desc' => ['old_id' => SORT_DESC],
				],
				'type_name' => [
					'asc' => [
						MeterType::tableName() .'.name' => SORT_ASC,
						MeterType::tableName() .'.channels' => SORT_ASC,
						MeterType::tableName() .'.phases' => SORT_ASC,
					],
					'desc' => [
						MeterType::tableName() .'.name' => SORT_DESC,
						MeterType::tableName() .'.channels' => SORT_DESC,
						MeterType::tableName() .'.phases' => SORT_DESC,
					],
				],
				'site_name' => [
					'asc' => [Site::tableName() .'.name' => SORT_ASC],
					'desc' => [Site::tableName() .'.name' => SORT_DESC],
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
						 * Type name
						 */
						case 'type_name':
							$query->andFilterWhere([
								'or',
								['like', MeterType::tableName() .'.name', $value],
								['like', MeterType::tableName() .'.channels', $value],
								['like', MeterType::tableName() .'.phases', $value],
							]);
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
						case 'name':
							$query->andFilterWhere(['like', "$t.name", $value]);
							break;

						/*
						 * Communication type
						 */
						case 'communication_type':
							$query->andFilterWhere(["$t.communication_type" => $value]);
							break;

						/*
						 * Start date
						 */
						case 'start_date':
							$query->andWhere("$t.start_date >= :start_date_from AND $t.start_date <= :start_date_to", [
								'start_date_from' => Yii::$app->formatter->modifyTimestamp($value, 'midnight'),
								'start_date_to' => Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1,
							]);
							break;

						/*
						 * Status
						 */
						case 'status':
							$query->andWhere(["$t.status" => $value]);
							break;

						/*
						 * Old ID
						 */
						case 'old_id':
							$query->andFilterWhere(['like', "$t.old_id", $value]);
							break;

                        case 'type':
                            $query->andFilterWhere(['like', "$t.type", $value]);
                            break;

						default:
							break;
					}
				}
			}
		}
	}
}
