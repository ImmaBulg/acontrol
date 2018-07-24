<?php

namespace api\models\forms;

use Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\web\BadRequestHttpException;

use api\models\ElectricityMeterRawData;
use common\models\Meter;
use common\models\MeterChannel;
use common\components\i18n\Formatter;

/**
 * FormElectricityMeterRawData is the class for meter raw data create/edit.
 */
class FormMeterRawData extends \yii\base\Model
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

        Yii::getLogger()->log(json_encode($this->$attribute),Logger::LEVEL_INFO,'meter-raw-data-api');
		foreach ($this->$attribute as &$data) {
			if (!is_array($data)) {
				return $this->addError($attribute, Yii::t('api.meter', '{attribute} elements must be an array.', [
					'attribute' => $this->getAttributeLabel($attribute),
				]));
			}

			$form = new FormMeterRawDataSingle();
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
				$model = ElectricityMeterRawData::find()
				->andWhere([
					'meter_id' => $data['meter_id'],
					'channel_id' => $data['channel_id'],
				])->andWhere("date = :date", [
					'date' => Yii::$app->formatter->asTimestamp($data['date']),
				])->one();

				if ($model == null) {
					$model = new ElectricityMeterRawData();
					$model->meter_id = $data['meter_id'];
					$model->channel_id = $data['channel_id'];
				}

				$model->attributes = $data;

				if (!$model->save()) {
					throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
				}

				ElectricityMeterRawData::deleteCacheValue(["meter_raw_data:{$model->relationMeter->name}_{$model->relationMeterChannel->channel}"]);

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
