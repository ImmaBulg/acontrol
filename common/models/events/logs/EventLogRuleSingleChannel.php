<?php

namespace common\models\events\logs;

use Yii;
use yii\base\NotSupportedException;

use common\models\Log;
use common\models\events\logs\EventLog;

/**
 * EventLogRuleSingleChannel is the class for logs of the rule single channel events.
 */
class EventLogRuleSingleChannel extends EventLog
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
		$model_log->action = 'Create single channel association rule (# {id}) for tenant ({tenant_name})';
		$model_log->setFormattedTokens([
			'id' => $model->id,
			'tenant_name' => $model->relationTenant->name,
		]);
		$model_log->save();
	}

	/*
	 * On event update
	 */
	public function onUpdate()
	{
		$model = $this->model;
		
		if ($model->getUpdatedAttributes() != null) {
			$model_log = $this->getLogModel();
			$model_log->type = Log::TYPE_UPDATE;
			$model_log->action = 'Update single channel association rule (# {id}) for tenant ({tenant_name})';
			$model_log->setFormattedTokens([
				'id' => $model->id,
				'tenant_name' => $model->relationTenant->name,
			]);
			$model_log->save();
		}
	}

	/*
	 * On event delete
	 */
	public function onDelete()
	{
		$model = $this->model;
		$model_log = $this->getLogModel();
		$model_log->type = Log::TYPE_DELETE;
		$model_log->action = 'Delete single channel association rule (# {id}) from tenant ({tenant_name})';
		$model_log->setFormattedTokens([
			'id' => $model->id,
			'tenant_name' => $model->relationTenant->name,
		]);
		$model_log->save();
	}
}