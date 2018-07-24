<?php

namespace console\controllers;

use Yii;
use yii\helpers\Console;
use yii\console\Exception;

use console\models\forms\FormAlertsPush;
use console\models\forms\FormAlertsPushBlue;

class AlertController extends \yii\console\Controller
{
	/**
	 * Push alerts
	 * The command "php yii alert/push"
	 */
	public function actionPush()
	{
		$form = new FormAlertsPush();
		$this->stdout("{$form->send()} alerts have been pushed.\n", Console::FG_GREEN);
		return self::EXIT_CODE_NORMAL;
	}

	/**
	 * Push alerts
	 * The command "php yii alert/push-blue"
	 */
	public function actionPushBlue()
	{
		$form = new FormAlertsPushBlue();
		$this->stdout("{$form->send()} alerts have been pushed.\n", Console::FG_GREEN);
		return self::EXIT_CODE_NORMAL;
	}
}
