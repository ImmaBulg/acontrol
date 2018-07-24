<?php

namespace common\widgets\chart;

class ChartAsset extends \yii\web\AssetBundle
{
	public $css = [];

	public $js = [
		'//code.highcharts.com/highcharts.js',
		'//code.highcharts.com/highcharts-more.js',
		'//code.highcharts.com/modules/solid-gauge.src.js',
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];
}
