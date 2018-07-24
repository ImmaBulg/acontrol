<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\Tenant;
use common\models\RuleSingleChannel;

/**
 * SearchRuleSingleChannel is the class for search single channel rules.
 */
class SearchRuleSingleChannel extends Search
{
	public $modelClass = '\backend\models\searches\models\RuleSingleChannel';

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
			'relationMeterChannel',
			'relationMeterChannel.relationMeter',
			'relationUsageTenant',
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
				'meter_name' => SORT_ASC,
				'channel_name' => SORT_ASC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'meter_name' => [
					'asc' => [
						Meter::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						Meter::tableName() .'.name' => SORT_DESC,
					],
				],
				'channel_name' => [
					'asc' => [
						MeterChannel::tableName() .'.channel' => SORT_ASC,
					],
					'desc' => [
						MeterChannel::tableName() .'.channel' => SORT_DESC,
					],
				],
				'usage_tenant_name' => [
					'asc' => [
						Tenant::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						Tenant::tableName() .'.name' => SORT_DESC,
					],
				],
				'use_type' => [
					'asc' => ['use_type' => SORT_ASC],
					'desc' => ['use_type' => SORT_DESC],
				],
				'use_percent' => [
					'asc' => ['use_percent' => SORT_ASC],
					'desc' => ['use_percent' => SORT_DESC],
				],
				'percent' => [
					'asc' => ['percent' => SORT_ASC],
					'desc' => ['percent' => SORT_DESC],
				],
				'from_hours' => [
					'asc' => ['from_hours' => SORT_ASC],
					'desc' => ['from_hours' => SORT_DESC],
				],
				'to_hours' => [
					'asc' => ['to_hours' => SORT_ASC],
					'desc' => ['to_hours' => SORT_DESC],
				],
				'total_bill_action' => [
					'asc' => ['total_bill_action' => SORT_ASC],
					'desc' => ['total_bill_action' => SORT_DESC],
				],
				'start_date' => [
					'asc' => ['start_date' => SORT_ASC],
					'desc' => ['start_date' => SORT_DESC],
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
						 * Meter name
						 */
						case 'meter_name':
							$query->andFilterWhere(['like', Meter::tableName() .'.name', $value]);
							break;

						/*
						 * Channel
						 */
						case 'channel_name':
							$query->andFilterWhere(['like',  MeterChannel::tableName(). '.channel', $value. '%', false]);
							break;

						/*
						 * Usage tenant name
						 */
						case 'usage_tenant_name':
							$query->andFilterWhere(['like',  Tenant::tableName(). '.name', $value]);
							break;

						/*
						 * Use type
						 */
						case 'use_type':
							$query->andWhere(["$t.use_type" => $value]);
							break;

						/*
						 * Use percent
						 */
						case 'use_percent':
							$query->andWhere(["$t.use_percent" => $value]);
							break;	

						/*
						 * Percent
						 */
						case 'percent':
							$query->andFilterWhere(['like',  "$t.percent", $value. '%', false]);
							break;

						/*
						 * Total bill action
						 */
						case 'total_bill_action':
							$query->andWhere(["$t.total_bill_action" => $value]);
							break;

						/*
						 * From hours
						 */
						case 'from_hours':
							$query->andWhere("$t.from_hours >= :from_hours", [
								'from_hours' => $value,
							]);
							break;

						/*
						 * To hours
						 */
						case 'to_hours':
							$query->andWhere("$t.to_hours <= :to_hours", [
								'to_hours' => $value,
							]);
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

						default:
							break;
					}
				}
			}
		}
	}
}
