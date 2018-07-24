<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\User;
use common\models\Site;
use common\models\SiteContact;
use common\models\Meter;
use common\models\MeterChannel;

/**
 * SearchTask is the class for search tasks.
 */
class SearchTask extends Search
{
	public $modelClass = '\backend\models\searches\models\Task';

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
			'relationUser',
			'relationSite',
			'relationSiteContact',
			'relationMeter',
			'relationMeterChannel',
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
				'date' => SORT_DESC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'description' => [
					'asc' => ['description' => SORT_ASC],
					'desc' => ['description' => SORT_DESC],
				],
				'type' => [
					'asc' => ['type' => SORT_ASC],
					'desc' => ['type' => SORT_DESC],
				],
				'color' => [
					'asc' => ['color' => SORT_ASC],
					'desc' => ['color' => SORT_DESC],
				],
				'urgency' => [
					'asc' => ['urgency' => SORT_ASC],
					'desc' => ['urgency' => SORT_DESC],
				],
				'status' => [
					'asc' => ['status' => SORT_ASC],
					'desc' => ['status' => SORT_DESC],
				],
				'date' => [
					'asc' => ['date' => SORT_ASC],
					'desc' => ['date' => SORT_DESC],
				],
				'ip_address' => [
					'asc' => ['ip_address' => SORT_ASC],
					'desc' => ['ip_address' => SORT_DESC],
				],
				'date_timestamp' => [
					'asc' => ['date' => SORT_ASC],
					'desc' => ['date' => SORT_DESC],
				],
				'user_role' => [
					'asc' => [
						User::tableName() .'.role' => SORT_ASC,
					],
					'desc' => [
						User::tableName() .'.role' => SORT_DESC,
					],
				],
				'user_name' => [
					'asc' => [
						User::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						User::tableName() .'.name' => SORT_DESC,
					],
				],
				'site_name' => [
					'asc' => [
						Site::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						Site::tableName() .'.name' => SORT_DESC,
					],
				],
				'site_contact_name' => [
					'asc' => [
						SiteContact::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						SiteContact::tableName() .'.name' => SORT_DESC,
					],
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
						 * Description
						 */
						case 'description':
							$query->andFilterWhere(['like', "$t.description", $value]);
							break;

						/*
						 * Type
						 */
						case 'type':
							$query->andWhere(["$t.type" => $value]);
							break;

						/*
						 * Color
						 */
						case 'color':
							$query->andWhere(["$t.color" => $value]);
							break;

						/*
						 * Urgency
						 */
						case 'urgency':
							$query->andWhere(["$t.urgency" => $value]);
							break;

						/*
						 * Status
						 */
						case 'status':
							$query->andWhere(["$t.status" => $value]);
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
						 * Date timestamp
						 */
						case 'date_timestamp':
							$query->andWhere(["$t.date" => $value]);
							break;

						/*
						 * Ip address
						 */
						case 'ip_address':
							$query->andFilterWhere(['like', "$t.ip_address", $value]);
							break;

						/*
						 * User name
						 */
						case 'user_name':
							$query->andFilterWhere([User::tableName() .'.id' => $value]);
							break;

						/*
						 * User role
						 */
						case 'user_role':
							$query->andWhere([User::tableName() .'.role' => $value]);
							break;

						/*
						 * Site name
						 */
						case 'site_name':
							$query->andFilterWhere(['like', Site::tableName() .'.name', $value]);
							break;

						/*
						 * Site contact name
						 */
						case 'site_contact_name':
							$query->andFilterWhere(['like', SiteContact::tableName() .'.name', $value]);
							break;

						/*
						 * Meter name
						 */
						case 'meter_name':
							$query->andFilterWhere(['like', Meter::tableName() .'.name', $value]);
							break;

						/*
						 * Channel name
						 */
						case 'channel_name':
							$query->andFilterWhere(['like', MeterChannel::tableName() .'.channel', $value]);
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
