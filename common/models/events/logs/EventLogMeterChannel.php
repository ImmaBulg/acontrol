<?php

namespace common\models\events\logs;

use Yii;
use yii\base\NotSupportedException;

use common\models\Log;
use common\models\events\logs\EventLog;

/**
 * EventLogMeterChannel is the class for logs of the meter channel events.
 */
class EventLogMeterChannel extends EventLog
{
	const METHOD_UPDATE = 'onUpdate';

	/*
	 * On event update
	 */
	public function onUpdate()
	{
		$model = $this->model;

		if ($model->getUpdatedAttributes() != null) {
			$model_log = $this->getLogModel();
			$model_log->type = Log::TYPE_UPDATE;
			$model_log->action = 'Update channel ({name}) for meter ({meter_name})';
			$model_log->setFormattedTokens([
				'name' => $model->channel,
				'meter_name' => $model->relationMeter->name,
			]);
			$model_log->save();
		}
	}
}