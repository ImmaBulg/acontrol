<?php

namespace common\models\events\logs;

use Yii;
use yii\base\NotSupportedException;

use common\models\Log;
use common\models\events\logs\EventLog;

/**
 * EventLogSite is the class for logs of the site events.
 */
class EventLogSite extends EventLog
{
	const METHOD_CREATE = 'onCreate';
	const METHOD_UPDATE = 'onUpdate';
	const METHOD_DELETE = 'onDelete';

	/*
	 * On event create
	 */
	public function onCreate()
	{
		$model = $this->model;
		$model_log = $this->getLogModel();
		$model_log->type = Log::TYPE_CREATE;
		$model_log->action = 'Create site ({name}) for client ({user_name})';
		$model_log->setFormattedTokens([
			'name' => $model->name,
			'user_name' => $model->relationUser->name,
		]);
		$model_log->save();
	}

	/*
	 * On event update
	 */
	public function onUpdate()
	{
		$model = $this->model;
		$model_log = $this->getLogModel();
		$model_log->type = Log::TYPE_UPDATE;
		$model_log->action = 'Update site ({name}) for client ({user_name})';
		$model_log->setFormattedTokens([
			'name' => $model->name,
			'user_name' => $model->relationUser->name,
		]);
		$model_log->save();
	}

	/*
	 * On event delete
	 */
	public function onDelete()
	{
		$model = $this->model;
		$model_log = $this->getLogModel();
		$model_log->type = Log::TYPE_DELETE;
		$model_log->action = 'Delete site ({name}) from client ({user_name})';
		$model_log->setFormattedTokens([
			'name' => $model->name,
			'user_name' => $model->relationUser->name,
		]);
		$model_log->save();
	}
}