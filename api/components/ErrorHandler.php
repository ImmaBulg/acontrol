<?php
namespace api\components;

use Yii;
use yii\base\Exception;
use yii\base\ErrorException;
use yii\base\UserException;

/**
 * ErrorHandler handles uncaught PHP errors and exceptions.
 *
 * ErrorHandler displays these errors using appropriate views based on the
 * nature of the errors and the mode the application runs at.
 *
 * ErrorHandler is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->errorHandler`.
 */
class ErrorHandler extends \yii\web\ErrorHandler
{
	/**
	 * Converts an exception into an array.
	 * @param \Exception $exception the exception being converted
	 * @return array the array representation of the exception.
	 */
	protected function convertExceptionToArray($exception)
	{
		if (!YII_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
			$exception = new HttpException(500, 'There was an error at the server.');
		}
		
		$array = [
			'name' => ($exception instanceof Exception || $exception instanceof ErrorException) ? $exception->getName() : 'Exception',
			'message' => $exception->getMessage(),
		];
		
		return ['error' => $array];
	}
}
