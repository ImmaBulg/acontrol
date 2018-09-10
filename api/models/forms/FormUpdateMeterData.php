<?php

namespace api\models\forms;

use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterType;
use common\models\MeterChannelMultiplier;
use common\models\MeterSubchannel;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use Yii;

class FormUpdateMeterData extends \yii\base\Model
{
    public $data;

    public function rules()
    {
        return [
            [['data'], 'required'],
            [['data'], 'validateData'],
        ];
    }

    public function validateData($attribute, $params) {
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
			$is_main = isset($data['isMain']) ? $data['isMain'] : false;
            if (isset($data['isMain']))
                unset($data['isMain']);
			$data['is_main'] = $is_main;
			$form = new FormMeterDataSingleUpdate();
            $form->attributes = $data;

			if (!$form->validate()) {
				throw new BadRequestHttpException(implode(' ', $form->getFirstErrors()));
			}

            $data = $form->attributes;

            $_meter = Meter::find()->where(['name' => $form->meter_id])->one();
            if (is_null($_meter)) {
                return $this->addError($attribute, Yii::t('api.meter', 'Meter {meter_id} not found.', [
					'meter_id' => $data['meter_id'],
				]));
            }
		}
    }

    public function save()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$models = [];

			foreach ($this->data as $data) {
                $model = Meter::find()->where(['name' => $data['meter_id']])->one();
				$model->start_date = strtotime('midnight');
				$model->attributes = $data;
				$model->name = $data['meter_id'];
				if (!$model->save()) {
					throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
				}

				$model_type = MeterType::findOne($model->type_id);
				$channels = $model->relationMeterChannels;
				$phases = $model_type->phases;
				$subchannel = $model->relationMeterSubchannels;

				foreach ($channels as $index => &$channel) {
					$channel->meter_id = $model->id;
					$channel->channel = $index;
					$channel->meter_multiplier = MeterChannelMultiplier::DEFAULT_METER_MULTIPLIER;
                    if ($model_type->type === 'air') {
                        $channel->is_main = $data['is_main'];
                    }

					if (!$channel->save()) {
						throw new BadRequestHttpException(implode(' ', $channel->getFirstErrors()));
					}

					foreach ($subchannel as $index => &$subchannel) {
						$subchannel->meter_id = $channel->meter_id;
						$subchannel->channel_id = $channel->id;
						$subchannel->channel = $index;

						if (!$subchannel->save()) {
							throw new BadRequestHttpException(implode(' ', $subchannel->getFirstErrors()));
						}
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