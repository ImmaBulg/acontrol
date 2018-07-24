<?php
namespace backend\widgets\site;

use Yii;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\base\InvalidValueException;

use common\helpers\Html;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\SiteMeterTree;

class SiteMeterTreeWidget extends \yii\base\Widget
{
	public $tree = [];
	public $options = [];
	public $pluginOptions = [];

	public function init()
	{
		parent::init();
	}

	public function run()
	{
		$id = $this->getId();
		$options = $this->options;
		Html::addCssClass($options, 'dd');
		$options['id'] = (!empty($options['id'])) ? $options['id'] : $id;
		$pluginOptions  = array_merge([
			'maxDepth' => 20,
		], $this->pluginOptions);

		if ($this->tree != null) {
			Yii::$app->view->registerJsFile('@web/js/plugins/nestable.js');
			Yii::$app->view->registerCssFile('@web/css/plugins/nestable.css');
			Yii::$app->view->registerJs("jQuery('#$id').nestable(" .Json::encode($pluginOptions). ");");

			echo Html::beginTag('div', $options);
			echo Html::beginTag('ol', ['class' => 'dd-list']);

			foreach ($this->tree as $model) {
				$this->buildTree($model);
			}

			echo Html::endTag('ol');
			echo Html::endTag('div');
		}
	}

	private function buildTree(MeterChannel $model)
	{
		echo Html::beginTag('li', ['class' => 'dd-item', 'data' => ['id' => $model->id]]);
		echo Html::tag('div', $model->relationMeter->name. ' - ' .$model->getChannelName(), ['class' => 'dd-handle']);

		if (($childrens = $model->relationSiteMeterTreeChildrens) != null) {
			echo Html::beginTag('ol', ['class' => 'dd-list']);

			foreach ($childrens as $children) {
				$this->buildTree($children->relationMeterChannel);
			}

			echo Html::endTag('ol');
		}

		echo Html::endTag('li');
	}
}
