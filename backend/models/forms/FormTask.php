<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\helpers\Html;
use common\models\Task;
use common\models\Site;
use common\models\User;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\SiteContact;
use common\components\i18n\Formatter;
use common\models\events\logs\EventLogTask;

/**
 * FormTask is the class for task create/edit.
 */
class FormTask extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $role;
	public $user_id;
	public $site_id;
	public $site_contact_id;
	public $meter_id;
	public $channel_id;
	public $description;
	public $type = Task::TYPE_HELPDESK;
	public $urgency = Task::URGENCY_LOW;
	public $status = Task::STATUS_ACTIVE;
	public $date;
	public $time;
	public $color = Task::COLOR_RED;

	public function rules()
	{
		return [
			[['description'], 'filter', 'filter' => 'strip_tags'],
			[['description'], 'filter', 'filter' => 'trim'],
			[['user_id', 'site_id', 'description', 'date', 'time'], 'required'],
			[['user_id', 'site_id', 'site_contact_id'], 'integer'],
			[['description'], 'string'],
			['role', 'in', 'range' => array_keys(Task::getListRoles()), 'skipOnEmpty' => true],
			['user_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\User', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					User::STATUS_INACTIVE,
					User::STATUS_ACTIVE,
				]]);
			}],
			['site_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Site', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					Site::STATUS_INACTIVE,
					Site::STATUS_ACTIVE,
				]]);
			}],
			['site_contact_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\SiteContact', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['site_id' => $this->site_id])
				->andWhere(['in', 'status', [
					SiteContact::STATUS_INACTIVE,
					SiteContact::STATUS_ACTIVE,
				]]);
			}],
			['meter_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Meter', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					Meter::STATUS_INACTIVE,
					Meter::STATUS_ACTIVE,
				]]);
			}],
			['channel_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\MeterChannel', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					MeterChannel::STATUS_INACTIVE,
					MeterChannel::STATUS_ACTIVE,
				]]);
			}],
			['date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['time', 'date', 'format' => Formatter::PHP_TIME_FORMAT],
			['type', 'default', 'value' => Task::TYPE_HELPDESK],
			['type', 'in', 'range' => array_keys(Task::getListTypes()), 'skipOnEmpty' => true],
			['urgency', 'default', 'value' => Task::URGENCY_LOW],
			['urgency', 'in', 'range' => array_keys(Task::getListUrgencies()), 'skipOnEmpty' => true],
			['color', 'in', 'range' => array_keys(Task::getListColors()), 'skipOnEmpty' => true],
			['status', 'default', 'value' => Task::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(Task::getListStatuses()), 'skipOnEmpty' => true],
			['channel_id', 'unique', 'targetClass' => '\common\models\Task', 'filter' => function($model){
				return $model->andWhere(['meter_id' => $this->meter_id])
				->andWhere(['in', 'status', [
					Task::STATUS_ACTIVE,
				]]);
			}, 'on' => self::SCENARIO_CREATE, 'message' => Yii::t('backend.task', '{attribute} has already been taken.')],
			['channel_id', 'unique', 'targetClass' => '\common\models\Task', 'filter' => function($model){
				return $model->andWhere('id != :id', ['id' => $this->_id])
				->andWhere(['meter_id' => $this->meter_id])
				->andWhere(['in', 'status', [
					Task::STATUS_ACTIVE,
				]]);
			}, 'on' => self::SCENARIO_EDIT, 'message' => Yii::t('backend.task', '{attribute} has already been taken.')],
		];
	}

	public function attributeLabels()
	{
		return [
			'role' => Yii::t('backend.task', 'Role'),
			'user_id' => Yii::t('backend.task', 'Assignee'),
			'site_id' => Yii::t('backend.task', 'Site'),
			'site_contact_id' => Yii::t('backend.task', 'Site contact'),
			'meter_id' => Yii::t('backend.task', 'Meter'),
			'channel_id' => Yii::t('backend.task', 'Channel'),
			'description' => Yii::t('backend.task', 'Description'),
			'type' => Yii::t('backend.task', 'Type'),
			'urgency' => Yii::t('backend.task', 'Urgency'),
			'status' => Yii::t('backend.task', 'Status'),
			'date' => Yii::t('backend.task', 'Date'),
			'time' => Yii::t('backend.task', 'Time'),
			'color' => Yii::t('backend.task', 'Color'),
		];
	}

	public function loadAttributes($scenario, $model = null)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;
				$this->user_id = $model->user_id;
				$this->role = $model->relationUser->role;
				$this->site_id = $model->site_id;
				$this->site_contact_id = $model->site_contact_id;
				$this->meter_id = $model->meter_id;
				$this->channel_id = $model->channel_id;
				$this->description = $model->description;
				$this->type = $model->type;
				$this->urgency = $model->urgency;
				$this->status = $model->status;
				

				if ($model->date != null) {
					$this->date = $model->date;
					$this->time = Yii::$app->formatter->asTime($model->date, 'HH:mm');
				}

				$this->color = $model->color;
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new Task();
		$model->user_id = $this->user_id;
		$model->site_id = $this->site_id;
		$model->site_contact_id = $this->site_contact_id;

		if ($this->meter_id != null) {
			$model->meter_id = $this->meter_id;
			$model->channel_id = $this->channel_id;
		} else {
			$model->meter_id = null;
			$model->channel_id = null;
		}

		$model->description = $this->description;
		$model->urgency = $this->urgency;
		$model->type = $this->type;
		$model->color = $this->color;
		$model->status = $this->status;
		$model->date = "{$this->date} {$this->time}";

		$event = new EventLogTask();
		$event->model = $model;
		$model->on(EventLogTask::EVENT_AFTER_INSERT, [$event, EventLogTask::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = Task::findOne($this->_id);
		$model->user_id = $this->user_id;
		$model->site_id = $this->site_id;
		$model->site_contact_id = $this->site_contact_id;

		if ($this->meter_id != null) {
			$model->meter_id = $this->meter_id;
			$model->channel_id = $this->channel_id;
		} else {
			$model->meter_id = null;
			$model->channel_id = null;
		}

		$model->description = $this->description;
		$model->urgency = $this->urgency;
		$model->type = $this->type;
		$model->status = $this->status;
		$model->color = $this->color;
		$model->date = "{$this->date} {$this->time}";

		$event = new EventLogTask();
		$event->model = $model;
		$model->on(EventLogTask::EVENT_BEFORE_UPDATE, [$event, EventLogTask::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
