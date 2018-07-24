<?php

namespace api\modules\swagger\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * DefaultController
 * The command  for generate documentation "php ./vendor/bin/swagger ./api/controllers --output ./api/web/doc/swagger.json --bootstrap ./api/modules/swagger/config/constants.php"
 */
class DefaultController extends \yii\web\Controller
{
	public $layout = 'main';

	public function actionIndex()
	{
		return $this->render('index', [
			'jsonPath' => Yii::getAlias('@web/doc/swagger.json'),
		]);
	}
}
