<?php

namespace console\controllers;

use Yii;
use yii\helpers\Console;
use yii\console\Exception;

use console\models\forms\FormSiteReports;
use console\models\forms\FormSiteMeterRawData;

class SiteController extends \yii\console\Controller
{
	/**
	 * Issue sites reports
	 * The command "php yii site/reports"
	 */
	public function actionReports()
	{
		$form = new FormSiteReports();
		$this->stdout("{$form->send()} sites have been issued.\n", Console::FG_GREEN);
		return self::EXIT_CODE_NORMAL;
	}
}
