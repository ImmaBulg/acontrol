<?php
namespace api\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use api\components\data\Search;

/**
 * SearchSiteIpAddress is the class for search meter-raw-data.
 */
class SearchSiteIpAddress extends Search
{
	public $modelClass = '\api\models\SiteIpAddress';

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
			],
		];
	}
}
