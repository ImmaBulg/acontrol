<?php

namespace backend\widgets\search;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

class SearchDetailView extends \yii\widgets\DetailView
{
	public $template = "<div class=\"clearfix\"><span class=\"text-muted\">{label}</span><span>{value}</span></div>";
	public $options = ['class' => ''];

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		$rows = [];
		$i = 0;
		foreach ($this->attributes as $attribute) {
			$rows[] = $this->renderAttribute($attribute, $i++);
		}

		$options = $this->options;
		$tag = ArrayHelper::remove($options, 'tag', 'div');
		echo Html::tag($tag, implode("\n", $rows), $options);
	}

	/**
	 * @inheritdoc
	 */
	protected function renderAttribute($attribute, $index)
	{
		if (is_string($this->template)) {
			$label = !empty($attribute['label']) ? $attribute['label']. ': ' : false;
			$value = ($attribute['value']  instanceof \Closure) ? call_user_func($attribute['value'], $this->model) : $attribute['value'];
			return strtr($this->template, [
				'{label}' => $label,
				'{value}' => $this->formatter->format($value, $attribute['format']),
			]);
		} else {
			return call_user_func($this->template, $attribute, $index, $this);
		}
	}
}