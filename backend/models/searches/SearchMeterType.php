<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\MeterType;

/**
 * SearchMeterType is the class for search meter types.
 */
class SearchMeterType extends Search
{
	public $modelClass = '\backend\models\searches\models\MeterType';

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
				'channels' => SORT_ASC,
				'phases' => SORT_ASC,
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
				'channels' => [
					'asc' => ['channels' => SORT_ASC],
					'desc' => ['channels' => SORT_DESC],
				],
				'phases' => [
					'asc' => ['phases' => SORT_ASC],
					'desc' => ['phases' => SORT_DESC],
				],
				'modbus' => [
					'asc' => ['modbus' => SORT_ASC],
					'desc' => ['modbus' => SORT_DESC],
				],
				'old_id' => [
					'asc' => ['old_id' => SORT_ASC],
					'desc' => ['old_id' => SORT_DESC],
				],
                'type' => [
                    'asc' => ['type' => SORT_ASC],
                    'desc' => ['type' => SORT_DESC],
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
						 * Name
						 */
						case 'name':
							$query->andFilterWhere(['like', "$t.name", $value]);
							break;

						/*
						 * Channels
						 */
						case 'channels':
							$query->andFilterWhere(['like', "$t.channels", $value. '%', false]);
							break;

						/*
						 * Phases
						 */
						case 'phases':
							$query->andWhere(["$t.phases" => $value]);
							break;

						/*
						 * Modbus
						 */
						case 'modbus':
							$query->andFilterWhere(['like', "$t.modbus", $value. '%', false]);
							break;

						/*
						 * Old ID
						 */
						case 'old_id':
							$query->andFilterWhere(['like', "$t.old_id", $value]);
							break;

                        case 'type':
                            $query->andFilterWhere(["$t.type" => $value]);
                            break;

						default:
							break;
					}
				}
			}
		}
	}
}
