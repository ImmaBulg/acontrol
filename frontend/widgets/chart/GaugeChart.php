<?php

namespace frontend\widgets\chart;

use Yii;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

/**
 * GaugeChart
 */
class GaugeChart extends \common\widgets\chart\Chart
{
	public $width = 300;
	public $height = 200;

	public function init()
	{
		parent::init();

		$this->clientOptions = ArrayHelper::merge([
			'chart' => ['type' => 'solidgauge'],
			'pane' => [
				'center' => ['50%', '85%'],
				'size' => '140%',
				'startAngle' => -90,
				'endAngle' => 90,
				'background' => [
					'backgroundColor' => new JsExpression('(Highcharts.theme && Highcharts.theme.background2) || "#EEE"'),
					'innerRadius' => '60%',
					'outerRadius' => '100%',
					'shape' => 'arc',
				],
			],	
			'tooltip' => ['enabled' => false],
			'yAxis' => [
				'lineWidth' => 0,
				'minorTickInterval' => null,
				'tickPixelInterval' => 400,
				'tickWidth' => 0,
				'title' => ['y' => -70],
				'labels' => ['y' => 16],
			],
			'plotOptions' => [
				'solidgauge' => [
					'dataLabels' => [
						'y' => 5,
						'borderWidth' => 0,
						'useHTML' => true,
					],
				],
			],
		], $this->clientOptions);
	}
}