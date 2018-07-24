<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\User;

/**
 * SearchTaskComment is the class for search task comments.
 */
class SearchTaskComment extends Search
{
	public $modelClass = '\backend\models\searches\models\TaskComment';

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
				'created_at' => SORT_DESC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'description' => [
					'asc' => ['description' => SORT_ASC],
					'desc' => ['description' => SORT_DESC],
				],
				'status' => [
					'asc' => ['status' => SORT_ASC],
					'desc' => ['status' => SORT_DESC],
				],
				'created_at' => [
					'asc' => ['created_at' => SORT_ASC],
					'desc' => ['created_at' => SORT_DESC],
				],
			],
		];
	}
}
