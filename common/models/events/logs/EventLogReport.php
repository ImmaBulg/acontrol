<?php

namespace common\models\events\logs;

use Yii;
use yii\base\NotSupportedException;

use common\models\Log;
use common\models\events\logs\EventLog;

/**
 * EventLogReport is the class for logs of the report events.
 */
class EventLogReport extends EventLog
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
		$model_log->action = 'Create report (# {id}) for client ({user_name})';
		$model_log->setFormattedTokens([
			'id' => $model->id,
			'user_name' => $model->relationSiteOwner->name,
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
		$model_log->action = 'Delete report (# {id}) from client ({user_name})';
		$model_log->setFormattedTokens([
			'id' => $model->id,
			'user_name' => $model->relationSiteOwner->name,
		]);
		$model_log->save();
	}
}