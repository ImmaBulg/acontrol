<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\MeterChannel;
use common\models\events\logs\EventLogMeterChannel;

/**
 * FormMeterChannels is the class for meter channels mass edit.
 */
class FormMeterChannels extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	const METER_CHANNELS_FIELD_NAME = 'meter_channels';

	public $current_multiplier;
	public $voltage_multiplier;

	public function rules()
	{
		return [
			[['current_multiplier', 'voltage_multiplier'], 'number', 'min' => 0],
		];
	}

	public function attributeLabels()
	{
		return [
			'current_multiplier' => Yii::t('backend.meter', 'Current multiplier'),
			'voltage_multiplier' => Yii::t('backend.meter', 'Voltage multiplier'),
		];
	}

	public function save()
	{
		if (!$this->validate()) return false;
		$channels = Yii::$app->request->getQueryParam(self::METER_CHANNELS_FIELD_NAME);
		if ($channels == null || ($this->current_multiplier == null && $this->voltage_multiplier == null)) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$models = MeterChannel::find()->where(['in', 'id', $channels])->all();

			if ($models != null) {
				foreach ($models as $model) {
					if ($this->current_multiplier != null) {
						$model->current_multiplier = $this->current_multiplier;
					}

					if ($this->voltage_multiplier != null) {
						$model->voltage_multiplier = $this->voltage_multiplier;
					}

					$event = new EventLogMeterChannel();
					$event->model = $model;
					$model->on(EventLogMeterChannel::EVENT_BEFORE_UPDATE, [$event, EventLogMeterChannel::METHOD_UPDATE]);

					if (!$model->save()) {
						throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
					}
				}
			}

			$transaction->commit();
			return true;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}
}
