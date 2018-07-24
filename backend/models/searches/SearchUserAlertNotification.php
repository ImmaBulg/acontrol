<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\User;
use common\models\Site;
use common\models\UserAlertNotification;

/**
 * SearchUserAlertNotification is the class for search contacts.
 */
class SearchUserAlertNotification extends Search
{
	public $modelClass = '\backend\models\searches\models\UserAlertNotification';

	/**
	 * @inheritdoc
	 */
	public function getDefaultQuery()
	{
		$modelClass = $this->modelClass;
		$t = $modelClass::tableName();
		$query = $modelClass::find()->where(['in', "$t.status", [
			UserAlertNotification::STATUS_INACTIVE,
			UserAlertNotification::STATUS_ACTIVE,
		]])->joinWith([
			'relationSite',
			'relationSite.relationUser',
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
				'site_owner_name' => SORT_ASC,
				'site_name' => SORT_ASC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'site_owner_name' => [
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
						 * Site owner name
						 */
						case 'site_owner_name':
							$query->andFilterWhere(['like', User::tableName() .'.name', $value]);
							break;

						/*
						 * Site name
						 */
						case 'site_name':
							$query->andFilterWhere(['like', Site::tableName() .'.name', $value]);
							break;

						default:
							break;
					}
				}
			}
		}
	}
}
