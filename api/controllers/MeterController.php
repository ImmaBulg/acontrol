<?php
namespace api\controllers;

use api\models\forms\FormUpdateMeterData;
use Yii;
use yii\db\Query;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use api\components\Controller;
use api\components\filters\QueryParamAuth;
use api\models\forms\FormMeterData;

/**
 * @SWG\Tag(
 *   name="meter",
 *   description="Operations about meters"
 * )
 */
class MeterController extends Controller
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
	 *     path="/meters",
	 *     summary="Create new meters",
	 *     operationId="MetersCreate",
	 *     tags={"meter"},
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
		$form = new FormMeterData();
		$form->attributes = $request->bodyParams;
		
		if ($models = $form->save()) {
			return $models;
		} else {
			throw new BadRequestHttpException(implode(' ', $form->getFirstErrors()));
		}
	}

    /**
     * @SWG\Post(
     *     path="/meters/update",
     *     summary="Update meter",
     *     operationId="MeterUpdate",
     *     tags={"meter"},
     *     @SWG\Parameter(
     *         name="api_key",
     *         in="query",
     *         description="Api Key",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="meter_id",
     *         in="formData",
     *         description="Meter id",
     *         required=true,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="site_id",
     *         in="formData",
     *         description="Site id",
     *         required=true,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="channels",
     *         in="formData",
     *         description="Channels",
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

	public function actionUpdate()
    {
        $form = new FormUpdateMeterData();

        $form->load(Yii::$app->request->bodyParams,'');

        if ($models = $form->save()) {
            return $models;
        } else {
            throw new BadRequestHttpException(implode(' ', $form->getFirstErrors()));
        }
    }
}
