<?php

namespace common\models\events\logs;

use Yii;
use yii\base\NotSupportedException;

use common\models\Log;
use common\models\events\logs\EventLog;

/**
 * EventLogUserAlertNotification is the class for logs of the user alert notification events.
 */
class EventLogUserAlertNotification extends EventLog
{
	const METHOD_CREATE = 'onCreate';
	const METHOD_DELETE = 'onDelete';

	/*
	 * On event create
	 */
	public function onCreate()
	{
		$model = $this->model;
		$model_log = $this->getLogModel();
		$model_log->type = Log::TYPE_CREATE;
		$model_log->action = 'Create alert notification (# {id}) for user ({user_name})';
		$model_log->setFormattedTokens([
			'id' => $model->id,
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
		$model_log->action = 'Delete alert notification (# {id}) from user ({user_name})';
		$model_log->setFormattedTokens([
			'id' => $model->id,
			'user_name' => $model->relationUser->name,
		]);
		$model_log->save();
	}
}