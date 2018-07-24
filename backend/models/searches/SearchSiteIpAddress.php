<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Site;
use common\models\SiteIpAddress;

/**
 * SearchSiteIpAddress is the class for search contacts.
 */
class SearchSiteIpAddress extends Search
{
	public $modelClass = '\backend\models\searches\models\SiteIpAddress';

	/**
	 * @inheritdoc
	 */
	public function getDefaultQuery()
	{
		$modelClass = $this->modelClass;
		$t = $modelClass::tableName();
		$query = $modelClass::find()->where(['in', "$t.status", [
			SiteIpAddress::STATUS_INACTIVE,
			SiteIpAddress::STATUS_ACTIVE,
		]])->joinWith([
			'relationSite',
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
				'id' => SORT_ASC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'ip_address' => [
					'asc' => ['ip_address' => SORT_ASC],
					'desc' => ['ip_address' => SORT_DESC],
				],
				'is_main' => [
					'asc' => ['is_main' => SORT_ASC],
					'desc' => ['is_main' => SORT_DESC],
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
						 * IP address
						 */
						case 'ip_address':
							$query->andFilterWhere(['like', "$t.ip_address", $value]);
							break;

						/*
						 * Is main
						 */
						case 'is_main':
							$query->andWhere(["$t.is_main" => $value]);
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
