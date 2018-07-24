<?php

namespace common\widgets;

use Yii;
use yii\helpers\Html;

class GridView extends \yii\grid\GridView
{
	public $dataColumnClass = '\common\widgets\DataColumn';
	public $tableOptions = ['class' => 'table table-hover'];
	public $options = [];
	public $layout = "{items}{pager}{summary}";
	public $pager = [
		'class' => 'common\widgets\LinkPager',
	];
}