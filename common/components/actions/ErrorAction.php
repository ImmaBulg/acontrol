<?php
namespace common\components\actions;

use Yii;
use yii\base\Exception;
use yii\base\UserException;

/**
 * Error action is the base class for error actions.
 */
class ErrorAction extends \yii\web\ErrorAction
{
	public $layout;
	public $view = 'error';

	public function run()
	{
		if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
			return '';
		}

		$code = $exception->statusCode;

		if ($exception instanceof Exception) {
			$name = $exception->getName();
		} else {
			$name = Yii::t('common.common', 'Error');
		}

		if ($exception instanceof UserException) {
			$message = $exception->getMessage();
		} else {
			$message = Yii::t('common.common', 'An internal server error occurred.');
		}

		if ($this->layout != null) {
			$this->controller->layout = $this->layout;
		}
		
		return $this->controller->render($this->view, [
			'name' => $name,
			'code' => $code,
			'message' => $message,
			'exception' => $exception,
		]);
	}
}
