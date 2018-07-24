<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Log;
use common\models\User;

/**
 * SearchLog is the class for search logs.
 */
class SearchLog extends Search
{
	public $modelClass = '\backend\models\searches\models\Log';

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
			'relationUserCreator',
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
				'created_at' => SORT_DESC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'type' => [
					'asc' => ['type' => SORT_ASC],
					'desc' => ['type' => SORT_DESC],
				],
				'action' => [
					'asc' => ['action' => SORT_ASC],
					'desc' => ['action' => SORT_DESC],
				],
				'ip_address' => [
					'asc' => ['ip_address' => SORT_ASC],
					'desc' => ['ip_address' => SORT_DESC],
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
						 * Type
						 */
						case 'type':
							$query->andFilterWhere(["$t.type" => $value]);
							break;

						/*
						 * Action
						 */
						case 'action':
							$query->andFilterWhere(['like', "$t.action", $value]);
							break;

						/*
						 * Ip address
						 */
						case 'ip_address':
							$query->andFilterWhere(['like', "$t.ip_address", $value]);
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

						/*
						 * User name
						 */
						case 'user_name':
							$query->andFilterWhere(['like', User::tableName(). '.name', $value]);
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
