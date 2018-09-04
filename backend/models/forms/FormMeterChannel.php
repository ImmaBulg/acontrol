<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelMultiplier;
use common\components\i18n\Formatter;
use common\models\events\logs\EventLogMeterChannel;

/**
 * FormMeterChannel is the class for meter channel create/edit.
 */
class FormMeterChannel extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $channel;
	public $meter_multiplier;
    public $is_main = 0;

	public function rules()
	{
		return [
			['meter_multiplier', 'required'],
			[['channel', 'is_main'], 'integer'],
			['meter_multiplier', 'number', 'min' => 0],
		];
	}

	public function attributeLabels()
	{
		return [
			'channel' => Yii::t('backend.meter', 'Channel'),
			'meter_multiplier' => Yii::t('backend.meter', 'Meter multiplier'),
            'is_main' => Yii::t('backend.meter', 'Is main'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

				$this->channel = $model->channel;
				$this->meter_multiplier = $model->meter_multiplier;
                $this->is_main = $model->is_main;
				break;

			default:
				break;
		}
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$sql_date_format = Formatter::SQL_DATE_FORMAT;

			$model = MeterChannel::findOne($this->_id);
			$model->meter_multiplier = $this->meter_multiplier;
            $model->is_main = $this->is_main;
			$event = new EventLogMeterChannel();
			$event->model = $model;
			$model->on(EventLogMeterChannel::EVENT_BEFORE_UPDATE, [$event, EventLogMeterChannel::METHOD_UPDATE]);

			$old_meter_multiplier = ArrayHelper::getValue($model->getOldAttributes(), 'meter_multiplier');

			if ($old_meter_multiplier != $model->meter_multiplier) {
				$model_multiplier = MeterChannelMultiplier::find()
				->andWhere([
					'meter_id' => $model->meter_id,
					'channel_id' => $model->id,
				])
				->orderBy(['end_date' => SORT_DESC])->one();

				if ($model_multiplier != null) {
					$start_date = $model_multiplier->end_date;

					if (date('dmY', $start_date) != date('dmY', strtotime('today') - 1)) {
						$model_multiplier = new MeterChannelMultiplier();
						$model_multiplier->meter_id = $model->meter_id;
						$model_multiplier->channel_id = $model->id;
						$model_multiplier->start_date = strtotime(date('m/d/Y', $start_date). ' +1 days');
					}
				} else {
					$model_multiplier = new MeterChannelMultiplier();
					$model_multiplier->meter_id = $model->meter_id;
					$model_multiplier->channel_id = $model->id;
					$model_multiplier->start_date = $model->created_at;
				}

				$model_multiplier->meter_multiplier = $old_meter_multiplier;
				$model_multiplier->end_date = strtotime('today') - 1;

				if (!$model_multiplier->save()) {
					throw new BadRequestHttpException(implode(' ', $model_multiplier->getFirstErrors()));
				}
			}

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			$transaction->commit();
			return $model;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}
}
