<?php

namespace common\widgets;

use Yii;
use yii\helpers\Html;

class ListView extends \yii\widgets\ListView
{
	public $options = ['class' => 'list-view'];
	public $itemOptions = ['class' => 'list-view-item'];
	public $layout = "{items}{pager}{summary}";
	public $pager = [
		'class' => 'common\widgets\LinkPager',
	];
}