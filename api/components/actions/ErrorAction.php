<?php
namespace api\components\actions;

use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\base\UserException;

/**
 * ErrorAction displays application errors using a specified view.
 *
 * To use ErrorAction, you need to do the following steps:
 *
 * First, declare an action of ErrorAction type in the `actions()` method of your `SiteController`
 * class (or whatever controller you prefer), like the following:
 *
 * ```php
 * public function actions()
 * {
 *     return [
 *         'error' => ['class' => 'yii\web\ErrorAction'],
 *     ];
 * }
 * ```
 *
 * Then, create a view file for this action. If the route of your error action is `site/error`, then
 * the view file should be `views/site/error.php`. In this view file, the following variables are available:
 *
 * - `$name`: the error name
 * - `$message`: the error message
 * - `$exception`: the exception being handled
 *
 * Finally, configure the "errorHandler" application component as follows,
 *
 * ```php
 * 'errorHandler' => [
 *     'errorAction' => 'site/error',
 * ]
 * ```
 */
class ErrorAction extends \yii\web\ErrorAction
{	
	public function run()
	{
		if (($exception = Yii::$app->errorHandler->exception) === null) {
			return '';
		}

		if ($exception instanceof HttpException) {
			$code = $exception->statusCode;
		} else {
			$code = $exception->getCode();
		}
		if ($exception instanceof Exception) {
			$name = $exception->getName();
		} else {
			$name = $this->defaultName ?: Yii::t('api.components', 'Error');
		}
		if ($code) {
			$name .= " (#$code)";
		}

		if ($exception instanceof UserException) {
			$message = $exception->getMessage();
		} else {
			$message = $this->defaultMessage ?: Yii::t('api.components', 'An internal server error occurred.');
		}

		$array = [
			'name' => $name,
			'message' => $message,
		];
		
		return ['error' => $array];
	}
}