<?php

namespace backend\widgets\search;

use Yii;
use yii\helpers\Html;

class SearchView extends \common\widgets\ListView
{
	public $options = ['class' => 'list-group'];
	public $itemOptions = ['class' => 'list-group-item'];
	public $layout = "{items}{pager}";
	public $pager = [
		'class' => 'common\widgets\LinkPager',
	];
	public $highlightText = false;

	public function init()
	{
		parent::init();

		if ($this->highlightText !== false) {
			Yii::$app->view->registerJsFile('@web/js/plugins/highlight.js');
			Yii::$app->view->registerJs("jQuery('.search-result').highlight('{$this->highlightText}');");
		}
	}
}