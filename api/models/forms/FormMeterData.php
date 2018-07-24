<?php

namespace api\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use api\models\Meter;
use common\models\MeterType;
use common\models\MeterChannel;
use common\models\MeterChannelMultiplier;
use common\models\MeterSubchannel;

/**
 * FormMeterData is the class for meter data create/edit.
 */
class FormMeterData extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	public $data;

	public function rules()
	{
		return [
			[['data'], 'required'],
			[['data'], 'validateData'],
		];
	}

	public function validateData($attribute, $params)
	{
		$values = [];

		if (!is_array($this->$attribute)) {
			return $this->addError($attribute, Yii::t('api.meter', '{attribute} must be an array.', [
				'attribute' => $this->getAttributeLabel($attribute),
			]));
		}

		foreach ($this->$attribute as &$data) {
			if (!is_array($data)) {
				return $this->addError($attribute, Yii::t('api.meter', '{attribute} elements must be an array.', [
					'attribute' => $this->getAttributeLabel($attribute),
				]));
			}

			$form = new FormMeterDataSingle();
			$form->attributes = $data;
			if (!$form->validate()) {
				throw new BadRequestHttpException(implode(' ', $form->getFirstErrors()));
			}

			$data = $form->attributes;
		}
	}

	public function attributeLabels()
	{
		return [
			'data' => Yii::t('api.meter', 'Data'),
		];
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$models = [];

			foreach ($this->data as $data) {
				$model = new Meter();
				$model->start_date = strtotime('midnight');
				$model->attributes = $data;
				$model->name = $data['meter_id'];

				if (!$model->save()) {
					throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
				}

				$model_type = MeterType::findOne($model->type_id);
				$channels = $model_type->channels;
				$phases = $model_type->phases;
				$subchannel = 1;

				for ($i = 1; $i <= $channels; $i++) {
					$model_channel = new MeterChannel();
					$model_channel->meter_id = $model->id;
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

				$models[] = $model;
			}

			$transaction->commit();
			return $models;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}
}
