<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\User;
use common\models\Site;
use common\models\Meter;
use common\models\MeterChannelGroup;
use common\models\MeterChannelGroupItem;

/**
 * SearchMeterChannelGroup is the class for search contacts.
 */
class SearchMeterChannelGroup extends Search
{
	public $modelClass = '\backend\models\searches\models\MeterChannelGroup';

	/**
	 * @inheritdoc
	 */
	public function getDefaultQuery()
	{
		$modelClass = $this->modelClass;
		$t = $modelClass::tableName();
		$query = $modelClass::find()->where(['in', "$t.status", [
			MeterChannelGroup::STATUS_INACTIVE,
			MeterChannelGroup::STATUS_ACTIVE,
		]])->joinWith([
			'relationUser',
			'relationSite',
			'relationMeter',
			'relationMeterChannelGroupItems',
		], 'INNER JOIN')->groupBy([MeterChannelGroup::tableName(). '.id']);

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
				'created_at' => [
					'asc' => ['created_at' => SORT_ASC],
					'desc' => ['created_at' => SORT_DESC],
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
				'meter_name' => [
					'asc' => [
						Meter::tableName() .'.name' => SORT_ASC,
					],
					'desc' => [
						Meter::tableName() .'.name' => SORT_DESC,
					],
				],
				'group_channels' => [
					'asc' => [
						'COUNT(' .MeterChannelGroupItem::tableName() .'.id)' => SORT_ASC,
					],
					'desc' => [
						'COUNT(' .MeterChannelGroupItem::tableName() .'.id)' => SORT_DESC,
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
						 * Name
						 */
						case 'name':
							$query->andFilterWhere(['like', "$t.name", $value]);
							break;

						/*
						 * User name
						 */
						case 'user_name':
							$query->andFilterWhere(['like', User::tableName() .'.name', $value]);
							break;

						/*
						 * Site name
						 */
						case 'site_name':
							$query->andFilterWhere(['like', Site::tableName() .'.name', $value]);
							break;

						/*
						 * Meter name
						 */
						case 'meter_name':
							$query->andFilterWhere(['like', Meter::tableName() .'.name', $value]);
							break;

						/*
						 * Channels
						 */
						case 'group_channels':
							$query->andHaving('COUNT(' .MeterChannelGroupItem::tableName(). '.id) = :group_channels', [
								'group_channels' => $value,
							]);
							break;

						/*
						 * Created at
						 */
						case 'created_at':
							$query->andWhere("$t.created_at >= :created_at_from AND $t.created_at <= :created_at_to", [
								'created_at_from' => Yii::$app->formatter->modifyTimestamp($value, 'midnight'),
								'created_at_to' => Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1,
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
