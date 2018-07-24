<?php
namespace common\components\data;

use Yii;
use yii\db\Query;
use yii\base\Model;
use yii\base\InvalidValueException;
use yii\data\ActiveDataProvider;

/**
 * Search is the base class for search models.
 */
class Search extends Model
{
	/**
	 * @var object Query
	 */
	private $query;

	/**
	 * @var array sort parameters
	 */
	private $sort;

	/**
	 * @var array pagination parameters
	 */
	private $pagination;

	/**
	 * @var object Model
	 */
	private $model;

	/**
	 * @var string model class
	 */
	public $modelClass;

	/**
	 * Search by model class
	 * @return ActiveDataProvider
	 */
	public function search()
	{
		if (!class_exists($this->modelClass)) {
			throw new InvalidValueException("The search modelClass class not found");
		}

		return $this->generateDataProvider();
	}

	/**
	 * Filter by model class
	 * @return Modal
	 */
	public function filter()
	{
		if (!class_exists($this->modelClass)) {
			throw new InvalidValueException("The search modelClass class not found");
		}

		return $this->generateFilterModel();
	}

	/**
	 * Generate ActiveDataProvider based on model query
	 * @return ActiveDataProvider
	 */
	public function generateDataProvider()
	{
		return new ActiveDataProvider([
			'query' => $this->getQuery(),
			'sort' => $this->getSort(),
			'pagination' => $this->getPagination(),
		]);
	}

	/**
	 * Get Query object based on model class
	 * @return Query
	 */
	public function getQuery()
	{
		if ($this->query == null) {
			$this->query = $this->getDefaultQuery();
		}

		return $this->query;
	}

	/**
	 * Get default Query object based on model class
	 */
	public function getDefaultQuery()
	{
		$modelClass = $this->modelClass;
		return $modelClass::find();
	}

	/**
	 * Get sort paramters array based on model class
	 * @return array of sort paramters
	 */
	public function getSort()
	{
		if ($this->sort == null) {
			$this->sort = $this->getDefaultSort();
		}

		return $this->sort;
	}

	/**
	 * Get default sort paramters array based on model class
	 */
	public function getDefaultSort()
	{
		$modelClass = $this->modelClass;

		return [
			'sortParam' => $modelClass::SORT_PARAM,
			'enableMultiSort' => $modelClass::ENABLE_MULTI_SORT,
		];
	}

	/**
	 * Get pagination paramters array based on model class
	 * @return array of pagination paramters
	 */
	public function getPagination()
	{
		if ($this->pagination == null) {
			$this->pagination = $this->getDefaultPagination();
		}

		return $this->pagination;
	}

	/**
	 * Get default pagination paramters array based on model class
	 */
	public function getDefaultPagination()
	{
		$modelClass = $this->modelClass;
		
		return [
			'pageParam' => $modelClass::PAGE_PARAM,
			'pageSizeParam' => $modelClass::PAGE_SIZE_PARAM,
			'defaultPageSize' => $modelClass::PAGE_SIZE,
			'pageSizeLimit' => [
				$modelClass::PAGE_SIZE_LIMIT_MIN,
				$modelClass::PAGE_SIZE_LIMIT_MAX,
			],
		];
	}

	/**
	 * Generate Model filtered
	 * @return Model
	 */
	public function generateFilterModel()
	{
		$this->setFilters();
		return $this->getModel();
	}

	/**
	 * Get Model object based on model class
	 * @return Model
	 */
	public function getModel()
	{
		if ($this->model == null) {
			$this->model = $this->getDefaultModel();
		}

		return $this->model;
	}

	/**
	 * Get default Model object based on model class
	 */
	public function getDefaultModel()
	{
		$modelClass = $this->modelClass;
		return new $modelClass;
	}

	/**
	 * Add filters
	 */
	public function setFilters(){}

	/**
	 * Get query filters by GET parameters
	 * @return array of filters
	 */
	public function getFilterParameters()
	{
		if (!Yii::$app->request instanceof \yii\console\Request) {
			$model = $this->getModel();

			if ($model->load(Yii::$app->request->queryParams) && $model->validate()) {
				return Yii::$app->request->getQueryParam((new \ReflectionClass($this->modelClass))->getShortName());
			}
		}
	}
}
