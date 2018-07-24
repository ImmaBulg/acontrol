<?php

namespace common\models\events\logs;

use Yii;
use yii\base\Event;
use yii\base\NotSupportedException;

use common\models\Log;

/**
 * EventLog is the base class for logs events.
 */
class EventLog extends Event
{
	const EVENT_INIT = 'init';
	const EVENT_BEFORE_INSERT = 'beforeInsert';
	const EVENT_AFTER_INSERT = 'afterInsert';
	const EVENT_BEFORE_UPDATE = 'beforeUpdate';
	const EVENT_AFTER_UPDATE = 'afterUpdate';
	const EVENT_BEFORE_DELETE = 'beforeDelete';
	const EVENT_AFTER_DELETE = 'afterDelete';

	public $model;

	/**
	 * Get Log model with default attributes
	 */
	public function getLogModel()
	{
		$model = new Log();

		if (!(Yii::$app->request instanceof \yii\console\Request)) {
			$model->ip_address = Yii::$app->request->userIp;
		}
		
		return $model;
	}
}