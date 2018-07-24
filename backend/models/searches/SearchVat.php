<?php
namespace backend\models\searches;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\Vat;
use common\models\User;

/**
 * SearchVat is the class for search vats.
 */
class SearchVat extends Search
{
	public $modelClass = '\backend\models\searches\models\Vat';

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
			'relationUserModificator',
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
				'end_date' => SORT_DESC,
				'start_date' => SORT_DESC,
			],
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
			'attributes' => [
				'id' => [
					'asc' => ['id' => SORT_ASC],
					'desc' => ['id' => SORT_DESC],
				],
				'vat' => [
					'asc' => ['vat' => SORT_ASC],
					'desc' => ['vat' => SORT_DESC],
				],
				'start_date' => [
					'asc' => ['start_date' => SORT_ASC],
					'desc' => ['start_date' => SORT_DESC],
				],
				'end_date' => [
					'asc' => ['end_date' => SORT_ASC],
					'desc' => ['end_date' => SORT_DESC],
				],
				'modificator_name' => [
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
						 * Vat
						 */
						case 'vat':
							$query->andFilterWhere(['like', "$t.vat", $value. '%', false]);
							break;

						/*
						 * Start date
						 */
						case 'start_date':
							$query->andWhere("$t.start_date >= :start_date", [
								'start_date' => Yii::$app->formatter->modifyTimestamp($value, 'midnight'),
							]);
							break;

						/*
						 * End date
						 */
						case 'end_date':
							$query->andWhere("$t.end_date <= :end_date", [
								'end_date' => Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1,
							]);
							break;

						/*
						 * Modificator name
						 */
						case 'modificator_name':
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
