<?php

namespace common\widgets\chart;

use Yii;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

use common\helpers\Html;
use common\widgets\chart\ChartAsset;
use common\components\i18n\LanguageSelector;

/**
 * http://www.highcharts.com/
 */
class Chart extends \yii\base\Widget
{
	public $width = null;
	public $height = 500;
	public $options = [];
	public $clientOptions = [];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		$this->options['id'] = ArrayHelper::getValue($this->options, 'id', $this->getId());
		Html::addCssClass($this->options, 'chart');
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		$id = ArrayHelper::getValue($this->options, 'id');
		$direction = LanguageSelector::getAliasLanguageDirection();
		$clientOptions = ArrayHelper::merge([
			'chart' => [
				'width' => $this->width,
				'height' => $this->height,
				'style' => [
					'fontFamily' => 'Source Sans Pro',
				],
			],
			'title' => [
				'text' => null,
				'useHTML' => (($direction == LanguageSelector::DIRECTION_RTL) ? new JsExpression('Highcharts.hasBidiBug') : false),
			],
			'tooltip' => [
				'backgroundColor' => '#fff',
				'useHTML' => (($direction == LanguageSelector::DIRECTION_RTL) ? true : false),
			],
			'xAxis' => [
				'labels' => [
					'y' => 35,
					'style' => [
						'fontSize' => '13px',
					],
				],
				'reversed' => (($direction == LanguageSelector::DIRECTION_RTL) ? true : false),
			],
			'yAxis' => [
				'title' => [
					'text' => null,
				],
				'labels' => [
					'style' => [
						'fontSize' => '13px',
					],
				],
				'opposite' => (($direction == LanguageSelector::DIRECTION_RTL) ? true : false),
			],
			'legend' => [
				'itemStyle' => [
					'fontSize' => '14px',
				],
				'useHTML' => (($direction == LanguageSelector::DIRECTION_RTL) ? true : false),
			],
			'credits' => [
				'enabled' => false,
			],
		], $this->clientOptions);

		ChartAsset::register($this->getView());
		$this->getView()->registerJs("Highcharts.setOptions(" .Json::encode([
			'lang' => [
				'resetZoom' => Yii::t('common.common', 'Reset zoom'),
				'resetZoomTitle' => Yii::t('common.common', 'Reset zoom level 1:1'),
			],
		]). ");");
		$this->getView()->registerJs("jQuery('#$id').highcharts(" .Json::encode($clientOptions). ");");
		return Html::tag('div', '', $this->options);
	}
}