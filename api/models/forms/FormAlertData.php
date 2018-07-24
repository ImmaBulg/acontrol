<?php

namespace api\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use api\models\Task;
use common\components\i18n\Formatter;

/**
 * FormAlertData is the class for alert data create/edit.
 */
class FormAlertData extends \yii\base\Model
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
			return $this->addError($attribute, Yii::t('api.task', '{attribute} must be an array.', [
				'attribute' => $this->getAttributeLabel($attribute),
			]));
		}

		foreach ($this->$attribute as &$data) {
			if (!is_array($data)) {
				return $this->addError($attribute, Yii::t('api.task', '{attribute} elements must be an array.', [
					'attribute' => $this->getAttributeLabel($attribute),
				]));
			}

			$form = new FormAlertDataSingle();
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
			'data' => Yii::t('api.task', 'Data'),
		];
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$models = [];
			$sql_date_format = Formatter::SQL_DATE_FORMAT;
			
			foreach ($this->data as $data) {
				$model = Task::find()->andWhere([
					'site_id' => (($site_id = ArrayHelper::getValue($data, 'site_id')) != null) ? $site_id : null,
					'site_contact_id' => (($site_contact_id = ArrayHelper::getValue($data, 'site_contact_id')) != null) ? $site_contact_id : null,
					'meter_id' => (($meter_id = ArrayHelper::getValue($data, 'meter_id')) != null) ? $meter_id : null,
					'channel_id' => (($channel_id = ArrayHelper::getValue($data, 'channel_id')) != null) ? $channel_id : null,
					'description' => (($description = ArrayHelper::getValue($data, 'description')) != null) ? $description : null,
					'type' => Task::TYPE_ALERT,
				])->andWhere("DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format') = :date", [
					'date' => Yii::$app->formatter->asDate((($date = ArrayHelper::getValue($data, 'date'))) ? Yii::$app->formatter->asTimestamp($date) : time()),
				])->one();

				if ($model == null) {
					$model = new Task();

					if ($model->user_id == null) {
						$model->user_id = Task::getAssigneeId();
					}

					$model->type = Task::TYPE_ALERT;
				}

				$model->attributes = $data;
				$model->date = (($date = ArrayHelper::getValue($data, 'date'))) ? Yii::$app->formatter->asTimestamp($date) : time();

				if (!$model->save()) {
					throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
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
