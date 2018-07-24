<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\helpers\Html;
use common\models\TaskComment;
use common\models\events\logs\EventLogTaskComment;

/**
 * FormTaskComment is the class for task comment create/edit.
 */
class FormTaskComment extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;
	private $_task_id;

	public $user_id;
	public $description;
	public $status;

	public function rules()
	{
		return [
			[['description'], 'filter', 'filter' => 'strip_tags'],
			[['description'], 'filter', 'filter' => 'trim'],
			[['description'], 'required'],
			[['description'], 'string'],
		];
	}

	public function attributeLabels()
	{
		return [
			'description' => Yii::t('backend.task', 'Comment'),
		];
	}

	public function loadAttributes($scenario, $model = null)
	{
		switch ($scenario) {
			case self::SCENARIO_CREATE:
				$this->_task_id = $model->id;
				break;
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;
				$this->_task_id = $model->task_id;

				$this->description = $model->description;
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new TaskComment();
		$model->task_id = $this->_task_id;
		$model->description = $this->description;

		$event = new EventLogTaskComment();
		$event->model = $model;
		$model->on(EventLogTaskComment::EVENT_AFTER_INSERT, [$event, EventLogTaskComment::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = TaskComment::findOne($this->_id);
		$model->description = $this->description;

		$event = new EventLogTaskComment();
		$event->model = $model;
		$model->on(EventLogTaskComment::EVENT_BEFORE_UPDATE, [$event, EventLogTaskComment::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
