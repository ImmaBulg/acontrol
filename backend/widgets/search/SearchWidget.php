<?php
namespace backend\widgets\search;

use Yii;
use yii\helpers\Url;
use backend\models\forms\FormSearch;

class SearchWidget extends \yii\base\Widget
{
	public $options = [];

	public function init()
	{
		parent::init();
	}

	public function run()
	{
		return $this->render('search', [
			'options' => $this->options,
			'q' => Yii::$app->request->getQueryParam('q'),
		]);
	}
}
