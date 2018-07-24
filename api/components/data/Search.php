<?php
namespace api\components\data;

use Yii;
use yii\db\Query;
use yii\base\InvalidValueException;

use api\components\data\ActiveDataProvider;

/**
 * Search is the API base class for search models.
 */
class Search extends \common\components\data\Search
{
	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	public function getFilterParameters()
	{
		if (!Yii::$app->request instanceof \yii\console\Request) {
			return Yii::$app->request->getQueryParam('filter');
		}
	}
}
