<?php

namespace common\widgets;

use Yii;
use yii\helpers\ArrayHelper;

class Select2 extends \kartik\select2\Select2
{
	public $pluginOptions = [
		'minimumResultsForSearch' => 10,
	];
}