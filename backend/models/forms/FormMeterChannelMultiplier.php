<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\MeterChannelMultiplier;
use common\components\i18n\Formatter;

/**
 * FormMeterChannelMultiplier is the class for meter channel multiplier create/edit.
 */
class FormMeterChannelMultiplier extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $start_date;
	public $end_date;
	public $current_multiplier;
	public $voltage_multiplier;

	public function rules()
	{
		return [
			[['start_date', 'end_date', 'current_multiplier', 'voltage_multiplier'], 'required'],
			['start_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['end_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['end_date', '\common\components\validators\DateTimeCompareValidator', 'compareValue' => Yii::$app->formatter->asDate(time()), 'format' => Formatter::PHP_DATE_FORMAT, 'operator' => '<'],
			['end_date', '\common\components\validators\DateTimeCompareValidator', 'compareAttribute' => 'start_date', 'format' => Formatter::PHP_DATE_FORMAT, 'operator' => '>'],
			[['current_multiplier', 'voltage_multiplier'], 'number', 'min' => 0],
		];
	}

	public function attributeLabels()
	{
		return [
			'start_date' => Yii::t('backend.meter', 'Start date'),
			'end_date' => Yii::t('backend.meter', 'End date'),
			'current_multiplier' => Yii::t('backend.meter', 'Current multiplier'),
			'voltage_multiplier' => Yii::t('backend.meter', 'Voltage multiplier'),
		];
	}

	public function loadAttributes($model)
	{
		$this->_id = $model->id;
		$this->start_date = $model->start_date;
		$this->end_date = $model->end_date;
		$this->current_multiplier = $model->current_multiplier;
		$this->voltage_multiplier = $model->voltage_multiplier;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = MeterChannelMultiplier::findOne($this->_id);
		$model->start_date = $this->start_date;
		$model->end_date = $this->end_date;
		$model->current_multiplier = $this->current_multiplier;
		$model->voltage_multiplier = $this->voltage_multiplier;

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
