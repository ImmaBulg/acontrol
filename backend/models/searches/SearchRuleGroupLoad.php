<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelGroup;
use common\models\TenantGroup;
use common\models\RuleGroupLoad;

/**
 * SearchRuleGroupLoad is the class for search group load rules.
 */
class SearchRuleGroupLoad extends Search
{
	public $modelClass = '\backend\models\searches\models\RuleGroupLoad';

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
			'relationMeterChannelGroup',
			'relationTenantGroup',
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
				'name' => [
					'asc' => ['name' => SORT_ASC],
					'desc' => ['name' => SORT_DESC],
				],
				'use_type' => [
					'asc' => ['use_type' => SORT_ASC],
					'desc' => ['use_type' => SORT_DESC],
				],
				'use_percent' => [
					'asc' => ['use_percent' => SORT_ASC],
					'desc' => ['use_percent' => SORT_DESC],
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
				'group_name' => [
					'asc' => [
						MeterChannelGroup::tableName() .'.name' => SORT_ASC,
						TenantGroup::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						MeterChannelGroup::tableName() .'.name' => SORT_DESC,
						TenantGroup::tableName() .'.name' => SORT_DESC,
					],
				],
				'total_bill_action' => [
					'asc' => ['total_bill_action' => SORT_ASC],
					'desc' => ['total_bill_action' => SORT_DESC],
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
						 * Use percent
						 */
						case 'use_percent':
							$query->andWhere(["$t.use_percent" => $value]);
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
						 * Group name
						 */
						case 'group_name':
							$query->andFilterWhere(['like', MeterChannelGroup::tableName() .'.name', $value]);
							break;

						/*
						 * Total bill action
						 */
						case 'total_bill_action':
							$query->andWhere(["$t.total_bill_action" => $value]);
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
