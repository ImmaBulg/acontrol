<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Tenant;
use common\models\TenantContact;
use common\models\User;

/**
 * SearchTenantContact is the class for search contacts.
 */
class SearchTenantContact extends Search
{
	public $modelClass = '\backend\models\searches\models\TenantContact';

	/**
	 * @inheritdoc
	 */
	public function getDefaultQuery()
	{
		$modelClass = $this->modelClass;
		$t = $modelClass::tableName();
		$query = $modelClass::find()->where(['in', "$t.status", [
			TenantContact::STATUS_INACTIVE,
			TenantContact::STATUS_ACTIVE,
		]])->joinWith([
			'relationTenant',
			'relationUser',
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
				'email' => [
					'asc' => ['email' => SORT_ASC],
					'desc' => ['email' => SORT_DESC],
				],
				'job' => [
					'asc' => ['job' => SORT_ASC],
					'desc' => ['job' => SORT_DESC],
				],
				'phone' => [
					'asc' => ['phone' => SORT_ASC],
					'desc' => ['phone' => SORT_DESC],
				],
				'cell_phone' => [
					'asc' => ['cell_phone' => SORT_ASC],
					'desc' => ['cell_phone' => SORT_DESC],
				],
				'fax' => [
					'asc' => ['fax' => SORT_ASC],
					'desc' => ['fax' => SORT_DESC],
				],
				'old_id' => [
					'asc' => ['old_id' => SORT_ASC],
					'desc' => ['old_id' => SORT_DESC],
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
						 * Name
						 */
						case 'name':
							$query->andFilterWhere(['like', "$t.name", $value]);
							break;

						/*
						 * Email
						 */
						case 'email':
							$query->andFilterWhere(['like', "$t.email", $value]);
							break;

						/*
						 * Job
						 */
						case 'job':
							$query->andFilterWhere(['like', "$t.job", $value]);
							break;

						/*
						 * Phone
						 */
						case 'phone':
							$query->andFilterWhere(['like', "$t.phone", $value]);
							break;

						/*
						 * Cell phone
						 */
						case 'cell_phone':
							$query->andFilterWhere(['like', "$t.cell_phone", $value]);
							break;

						/*
						 * Fax
						 */
						case 'fax':
							$query->andFilterWhere(['like', "$t.fax", $value]);
							break;

						/*
						 * Old ID
						 */
						case 'old_id':
							$query->andFilterWhere(['like', "$t.old_id", $value]);
							break;

						/*
						 * User name
						 */
						case 'user_name':
							$query->andFilterWhere(['like', User::tableName() .'.name', $value]);
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
