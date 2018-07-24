<?php

namespace common\widgets;

use Yii;
use yii\helpers\ArrayHelper;

class DepDrop extends \kartik\depdrop\DepDrop
{
	public $select2Options = [
		'pluginOptions' => [
			'minimumResultsForSearch' => 10,
		],
	];
}