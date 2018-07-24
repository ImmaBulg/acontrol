<?php

namespace backend\models\forms;

use Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterSubchannel;
use common\models\MeterChannelMultiplier;
use common\models\MeterType;
use common\models\events\logs\EventLogMeterType;

/**
 * FormMeterType is the class for meter type create/edit.
 */
class FormMeterType extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $name;
	public $channels;
	public $phases;
	public $modbus;
	public $is_divide_by_1000;
	public $is_summarize_max_demand;

	public function rules()
	{
		return [
			[['name'], 'filter', 'filter' => 'strip_tags'],
			[['name'], 'filter', 'filter' => 'trim'],
			[['name', 'channels', 'phases'], 'required'],
			[['name'], 'string', 'max' => 255],
			[['channels'], 'integer', 'min' => 1],
			[['modbus'], 'number'],
			[['is_divide_by_1000','is_summarize_max_demand'], 'safe'],
			['phases', 'in', 'range' => array_keys(MeterType::getListPhases()), 'skipOnEmpty' => false],

			// On scenario create
			['name', 'unique', 'targetClass' => '\common\models\MeterType', 'filter' => function($model){
				return $model->where('name = :name COLLATE utf8_bin', ['name' => $this->name])
				->andWhere([
					'channels' => $this->channels,
					'phases' => $this->phases,
				])->andWhere(['in', 'status', [
					MeterType::STATUS_INACTIVE,
					MeterType::STATUS_ACTIVE,
				]]);
			}, 'message' => Yii::t('backend.meter', 'Meter type has already been taken.'), 'on' => self::SCENARIO_CREATE],

			// On scenario edit
			['name', 'unique', 'targetClass' => '\common\models\MeterType', 'filter' => function($model){
				return $model->where('name = :name COLLATE utf8_bin', ['name' => $this->name])
				->andWhere('id != :id', ['id' => $this->_id])
				->andWhere([
					'channels' => $this->channels,
					'phases' => $this->phases,
				])->andWhere(['in', 'status', [
					MeterType::STATUS_INACTIVE,
					MeterType::STATUS_ACTIVE,
				]]);
			}, 'message' => Yii::t('backend.meter', 'Meter type has already been taken.'), 'on' => self::SCENARIO_EDIT],
		];
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('backend.meter', 'Name'),
			'channels' => Yii::t('backend.meter', 'Channels'),
			'phases' => Yii::t('backend.meter', 'Phases'),
			'modbus' => Yii::t('backend.meter', 'MODBUS'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

				$this->name = $model->name;
				$this->channels = $model->channels;
				$this->phases = $model->phases;
				$this->modbus = $model->modbus;
				$this->is_divide_by_1000 = $model->is_divide_by_1000;
				$this->is_summarize_max_demand = $model->is_summarize_max_demand;
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new MeterType();
		$model->name = $this->name;
		$model->channels = $this->channels;
		$model->phases = $this->phases;
		$model->modbus = $this->modbus;
		$model->is_divide_by_1000 = $this->is_divide_by_1000;
		$model->is_summarize_max_demand = $this->is_summarize_max_demand;

		$event = new EventLogMeterType();
		$event->model = $model;
		$model->on(EventLogMeterType::EVENT_AFTER_INSERT, [$event, EventLogMeterType::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();
		
		try	{
			$model = MeterType::findOne($this->_id);
			$updated_channels = ($model->channels != $this->channels || $model->phases != $this->phases);

			$model->name = $this->name;
			$model->channels = $this->channels;
			$model->phases = $this->phases;
			$model->modbus = $this->modbus;
            $model->is_divide_by_1000 = $this->is_divide_by_1000;
            $model->is_summarize_max_demand = $this->is_summarize_max_demand;
			$event = new EventLogMeterType();
			$event->model = $model;
			$model->on(EventLogMeterType::EVENT_BEFORE_UPDATE, [$event, EventLogMeterType::METHOD_UPDATE]);

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			if ($updated_channels) {
				$model_meters = Meter::find()->where(['type_id' => $model->id])->all();

				if ($model_meters != null) {
					foreach ($model_meters as $model_meter) {
						MeterChannel::deleteAll('meter_id = :meter_id', ['meter_id' => $model_meter->id]);
						MeterSubchannel::deleteAll('meter_id = :meter_id', ['meter_id' => $model_meter->id]);
						//MeterRawData::deleteAll('meter_id = :name', ['name' => $model_meter->name]);

						$channels = $model->channels;
						$phases = $model->phases;
						$subchannel = 1;
						
						for ($i = 1; $i <= $channels; $i++) {
							$model_channel = new MeterChannel();
							$model_channel->meter_id = $model_meter->id;
							$model_channel->channel = $i;
							$model_channel->current_multiplier = MeterChannelMultiplier::DEFAULT_CURRENT_MULTIPLIER;
							$model_channel->voltage_multiplier = MeterChannelMultiplier::DEFAULT_VOLTAGE_MULTIPLIER;

							if (!$model_channel->save()) {
								throw new BadRequestHttpException(implode(' ', $model_channel->getFirstErrors()));
							}

							for ($j = 0; $j < $phases; $j++) {
								$model_subchannel = new MeterSubchannel();
								$model_subchannel->meter_id = $model_channel->meter_id;
								$model_subchannel->channel_id = $model_channel->id;
								$model_subchannel->channel = $subchannel;

								if (!$model_subchannel->save()) {
									throw new BadRequestHttpException(implode(' ', $model_subchannel->getFirstErrors()));
								}

								$subchannel++;
							}
						}
					}
				}
			}

			$transaction->commit();
			return $model;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}
}
