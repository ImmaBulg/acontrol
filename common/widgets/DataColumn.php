<?php

namespace common\widgets;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

use common\helpers\Html;
use common\widgets\Select2;

class DataColumn extends \yii\grid\DataColumn
{
	const FILTER_CELL_TYPE_TEXT = 'text';
	const FILTER_CELL_TYPE_DATE = 'date';

	public $filterType = self::FILTER_CELL_TYPE_TEXT;

	/**
	 * @inheritdoc
	 */
	protected function renderFilterCellContent()
	{
		if (is_string($this->filter)) {
			return $this->filter;
		}

		$model = $this->grid->filterModel;

		if ($this->filter !== false && $model instanceof Model && $this->attribute !== null && $model->isAttributeActive($this->attribute)) {
			
			if ($model->hasErrors($this->attribute)) {
				Html::addCssClass($this->filterOptions, 'has-error');
				$error = ' ' . Html::error($model, $this->attribute, $this->grid->filterErrorOptions);
			} else {
				$error = '';
			}

			switch ($this->filterType) {
				case self::FILTER_CELL_TYPE_DATE:
					return Html::activeDateInput($model, $this->attribute, $this->filterInputOptions) . $error;
					break;
		
				case self::FILTER_CELL_TYPE_TEXT:
				default:
					if (is_array($this->filter)) {
						return Select2::widget([
							'model' => $model,
							'attribute' => $this->attribute,
							'data' => ArrayHelper::merge(['' => ''], $this->filter),
							'options' => $this->filterInputOptions,
						]) . $error;
					} else {
						return Html::activeTextInput($model, $this->attribute, $this->filterInputOptions) . $error;
					}
					break;
			}
		} else {
			return parent::renderFilterCellContent();
		}
	}
}