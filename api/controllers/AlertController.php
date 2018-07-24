<?php
namespace api\controllers;

use Yii;
use yii\db\Query;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use api\components\Controller;
use api\components\filters\QueryParamAuth;
use api\models\forms\FormAlertData;

/**
 * @SWG\Tag(
 *   name="alert",
 *   description="Operations about alerts"
 * )
 */
class AlertController extends Controller
{
	/**
	 * @inheritdoc
	 */
	protected function verbs()
	{
		return [
			'create' => ['POST'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'authenticator' => [
				'class' => QueryParamAuth::className(),
			],
		]);
	}

	/**
	 * @SWG\Post(
	 *     path="/alerts",
	 *     summary="Create new alerts",
	 *     operationId="AlertsCreate",
	 *     tags={"alert"},
	 *     @SWG\Parameter(
	 *         name="api_key",
	 *         in="query",
	 *         description="Api Key",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="data",
	 *         in="formData",
	 *         description="Data",
	 *         required=true,
	 *         type="array",
	 *         items=""
	 *     ),
	 *     @SWG\Response(
	 *         response=200,
	 *         description="Success"
	 *     ),
	 *     @SWG\Response(
	 *         response=400,
	 *         description="Invalid form parameters"
	 *     )
	 * )
	 */
	public function actionCreate()
	{
		$request = Yii::$app->request;
		$form = new FormAlertData();
		$form->attributes = $request->bodyParams;

		if ($models = $form->save()) {
			return $models;
		} else {
			throw new BadRequestHttpException(implode(' ', $form->getFirstErrors()));
		}
	}
}
