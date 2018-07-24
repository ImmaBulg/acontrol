<?php

namespace common\widgets;

use Yii;
use common\helpers\Html;
use yii\helpers\ArrayHelper;

class ActiveField extends \yii\bootstrap\ActiveField
{
	public $template = "{label}{labelPrefix}\n{input}\n{hint}\n{error}";
	public $checkboxTemplate = "<div class=\"checkbox\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>";
	public $radioTemplate = "<div class=\"radio\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>";
	public $horizontalCheckboxTemplate = "{beginWrapper}\n<div class=\"checkbox\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n</div>\n{error}\n{endWrapper}\n{hint}";
	public $horizontalRadioTemplate = "{beginWrapper}\n<div class=\"radio\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n</div>\n{error}\n{endWrapper}\n{hint}";
	public $inlineCheckboxListTemplate = "{label}\n{beginWrapper}\n{input}\n{error}\n{endWrapper}\n{hint}";
	public $inlineRadioListTemplate = "{label}\n{beginWrapper}\n{input}\n{error}\n{endWrapper}\n{hint}";

	/**
	 * @inheritdoc
	 */
	public function render($content = null)
	{
		if ($content === null) {
			if ($this->enableLabel === false || !isset($this->parts['{labelPrefix}'])) {
				$this->parts['{labelPrefix}'] = '';
			}
		}

		return parent::render($content);
	}

	public function dateInput($options = [], $pluginOptions = [])
	{
		$options = array_merge($this->inputOptions, $options);
		$this->adjustLabelFor($options);
		$this->parts['{input}'] = Html::activeDateInput($this->model, $this->attribute, $options, $pluginOptions);
		return $this;
	}

	public function textInput($options = [])
	{
		$options = array_merge($this->inputOptions, $options);
		$this->adjustLabelFor($options);
		$this->parts['{input}'] = Html::activeTextInput($this->model, $this->attribute, $options);

		if (!empty($options['allow_only'])) {
			switch ($options['allow_only']) {
				case Html::TYPE_NUMBER:
					$this->parts['{labelPrefix}'] = " <span class=\"text-muted\">(" .Yii::t('common.common', 'number'). ")</span>";
					break;
				
				default:
					break;
			}
		}

		return $this;
	}

	public function multiSelect2Side($items, $options = [], $pluginOptions = [])
	{
		$options = array_merge($this->inputOptions, $options);
		$this->adjustLabelFor($options);
		$this->parts['{input}'] = Html::activeMultiSelect2Side($this->model, $this->attribute, $items, $options, $pluginOptions);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	protected function createLayoutConfig($instanceConfig)
	{
		$config = [
			'hintOptions' => [
				'tag' => 'p',
				'class' => 'help-block',
			],
			'errorOptions' => [
				'tag' => 'p',
				'class' => 'help-block help-block-error',
				'encode' => false,
			],
			'inputOptions' => [
				'class' => 'form-control',
			],
		];

		$layout = $instanceConfig['form']->layout;

		if ($layout === 'horizontal') {
			$config['template'] = "{label}\n{beginWrapper}\n{input}\n{error}\n{endWrapper}\n{hint}";
			$cssClasses = [
				'offset' => 'col-sm-offset-3',
				'label' => 'col-sm-3',
				'wrapper' => 'col-sm-6',
				'error' => '',
				'hint' => 'col-sm-3',
			];
			if (isset($instanceConfig['horizontalCssClasses'])) {
				$cssClasses = ArrayHelper::merge($cssClasses, $instanceConfig['horizontalCssClasses']);
			}
			$config['horizontalCssClasses'] = $cssClasses;
			$config['wrapperOptions'] = ['class' => $cssClasses['wrapper']];
			$config['labelOptions'] = ['class' => 'control-label ' . $cssClasses['label']];
			$config['errorOptions'] = ['class' => 'help-block help-block-error ' . $cssClasses['error']];
			$config['hintOptions'] = ['class' => 'help-block ' . $cssClasses['hint']];
		} elseif ($layout === 'inline') {
			$config['labelOptions'] = ['class' => 'sr-only'];
			$config['enableError'] = false;
		}

		return $config;
	}
}