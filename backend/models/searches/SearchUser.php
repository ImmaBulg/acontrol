<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\User;
use common\models\UserProfile;
use common\components\i18n\Formatter;

/**
 * SearchUser is the class for search users.
 */
class SearchUser extends Search
{
	public $modelClass = '\backend\models\searches\models\User';

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
			'relationUserProfile',
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
				'nickname' => [
					'asc' => ['nickname' => SORT_ASC],
					'desc' => ['nickname' => SORT_DESC],
				],
				'email' => [
					'asc' => ['email' => SORT_ASC],
					'desc' => ['email' => SORT_DESC],
				],
				'role' => [
					'asc' => ['role' => SORT_ASC],
					'desc' => ['role' => SORT_DESC],
				],
				'status' => [
					'asc' => ['status' => SORT_ASC],
					'desc' => ['status' => SORT_DESC],
				],
				'old_id' => [
					'asc' => ['old_id' => SORT_ASC],
					'desc' => ['old_id' => SORT_DESC],
				],
				'created_at' => [
					'asc' => ['created_at' => SORT_ASC],
					'desc' => ['created_at' => SORT_DESC],
				],
				'job' => [
					'asc' => [
						UserProfile::tableName() .'.job' => SORT_ASC,
					],
					'desc' => [
						UserProfile::tableName() .'.job' => SORT_DESC,
					],
				],
				'phone' => [
					'asc' => [
						UserProfile::tableName() .'.phone' => SORT_ASC,
					],
					'desc' => [
						UserProfile::tableName() .'.phone' => SORT_DESC,
					],
				],
				'fax' => [
					'asc' => [
						UserProfile::tableName() .'.fax' => SORT_ASC,
					],
					'desc' => [
						UserProfile::tableName() .'.fax' => SORT_DESC,
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
						 * Nickname
						 */
						case 'nickname':
							$query->andFilterWhere(['like', "$t.nickname", $value]);
							break;

						/*
						 * Email
						 */
						case 'email':
							$query->andFilterWhere(['like', "$t.email", $value]);
							break;

						/*
						 * Phone
						 */
						case 'phone':
							$query->andFilterWhere(['like', UserProfile::tableName() .'.phone', $value]);
							break;

						/*
						 * Fax
						 */
						case 'fax':
							$query->andFilterWhere(['like', UserProfile::tableName() .'.fax', $value]);
							break;

						/*
						 * Job
						 */
						case 'job':
							$query->andFilterWhere(['like', UserProfile::tableName() .'.job', $value]);
							break;

						/*
						 * Status
						 */
						case 'status':
							$query->andWhere(["$t.status" => $value]);
							break;

						/*
						 * Role
						 */
						case 'role':
							$query->andWhere(["$t.role" => $value]);
							break;

						/*
						 * Old ID
						 */
						case 'old_id':
							$query->andFilterWhere(['like', "$t.old_id", $value]);
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
