<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\Task;
use common\models\events\logs\EventLogTask;

/**
 * FormTasks is the class for tasks mass edit.
 */
class FormTasks extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';
	const TASKS_FIELD_NAME = 'tasks';

	public $is_delete;

	public function rules()
	{
		return [
			['is_delete', 'boolean'],
		];
	}

	public function save()
	{
		if (!$this->validate()) return false;
		$tasks = Yii::$app->request->getQueryParam(self::TASKS_FIELD_NAME);
		if ($tasks == null) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$models = Task::find()->where(['in', 'id', $tasks])->all();

			if ($models != null) {
				foreach ($models as $model) {
					$event = new EventLogTask();
					$event->model = $model;
					$model->on(EventLogTask::EVENT_BEFORE_DELETE, [$event, EventLogTask::METHOD_DELETE]);

					if (!$model->delete()) {
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
