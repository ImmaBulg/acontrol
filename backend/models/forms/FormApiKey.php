<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\ApiKey;
use common\models\events\logs\EventLogApiKey;

/**
 * FormApiKey is the class for api key create/edit.
 */
class FormApiKey extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $api_key;
	public $status;

	public function rules()
	{
		return [
			[['api_key'], 'filter', 'filter' => 'strip_tags'],
			[['api_key'], 'filter', 'filter' => 'trim'],
			[['api_key'], 'required'],
			[['api_key'], 'string'],
			['status', 'default', 'value' => ApiKey::STATUS_ACTIVE],
			['status', 'in', 'range' => array_keys(ApiKey::getListStatuses()), 'skipOnEmpty' => true],
		];
	}

	public function attributeLabels()
	{
		return [
			'api_key' => Yii::t('backend.api', 'API key'),
			'status' => Yii::t('backend.api', 'Status'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

				$this->api_key = $model->api_key;
				$this->status = $model->status;
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new ApiKey();
		$model->api_key = $this->api_key;
		$model->status = $this->status;

		$event = new EventLogApiKey();
		$event->model = $model;
		$model->on(EventLogApiKey::EVENT_AFTER_INSERT, [$event, EventLogApiKey::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = ApiKey::findOne($this->_id);
		$model->api_key = $this->api_key;
		$model->status = $this->status;

		$event = new EventLogApiKey();
		$event->model = $model;
		$model->on(EventLogApiKey::EVENT_BEFORE_UPDATE, [$event, EventLogApiKey::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
