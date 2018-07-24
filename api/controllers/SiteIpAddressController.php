<?php
namespace api\controllers;

use Yii;
use yii\db\Query;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\data\ActiveDataProvider;

use api\models\Site;
use api\models\SiteIpAddress;
use api\models\searches\SearchSiteIpAddress;
use api\components\Controller;
use api\components\filters\QueryParamAuth;

/**
 * @SWG\Tag(
 *   name="site-ip-address",
 *   description="Operations about site ip addresses"
 * )
 */
class SiteIpAddressController extends Controller
{
	/**
	 * @inheritdoc
	 */
	protected function verbs()
	{
		return [
			'list' => ['GET'],
			'view' => ['GET'],
			'update' => ['PUT'],
			'create' => ['POST'],
			'delete' => ['DELETE'],
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
	 * @SWG\Get(
	 *     path="/site/{site_id}/ip-address",
	 *     summary="Returns site ip addresses",
	 *     operationId="SiteIpAddressList",
	 *     tags={"site-ip-address"},
	 *     @SWG\Parameter(
	 *         name="api_key",
	 *         in="query",
	 *         description="Api Key",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="site_id",
	 *         in="path",
	 *         description="Site ID",
	 *         required=true,
	 *         type="integer"
	 *     ),
	 *     @SWG\Response(
	 *         response=200,
	 *         description="Success"
	 *     ),
	 *     @SWG\Response(
	 *         response=402,
	 *         description="Bad request"
	 *     )
	 * )
	 */
	public function actionList($site_id)
	{
		$request = Yii::$app->request;
		$site = $this->findSiteModel($site_id);
		$search = new SearchSiteIpAddress();
		$data_provider = $search->search();
		$data_provider->query->andWhere([SiteIpAddress::tableName(). '.site_id' => $site_id]);
		$search->filter();
		return $data_provider->prepareList();
	}

	/**
	 * @SWG\Post(
	 *     path="/site/{site_id}/ip-address",
	 *     summary="Creates a new site ip address",
	 *     operationId="SiteIpAddressCreate",
	 *     tags={"site-ip-address"},
	 *     @SWG\Parameter(
	 *         name="api_key",
	 *         in="query",
	 *         description="Api Key",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="site_id",
	 *         in="path",
	 *         description="Site ID",
	 *         required=true,
	 *         type="integer"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="ip_address",
	 *         in="formData",
	 *         description="IP address",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="is_main",
	 *         in="formData",
	 *         description="Is main",
	 *         required=false,
	 *         type="integer",
	 *         enum={0, 1}
	 *     ),
	 *     @SWG\Response(
	 *         response=200,
	 *         description="Success"
	 *     ),
	 *     @SWG\Response(
	 *         response=402,
	 *         description="Bad request"
	 *     ),
	 *     @SWG\Response(
	 *         response=400,
	 *         description="Invalid form parameters"
	 *     )
	 * )
	 */
	public function actionCreate($site_id)
	{
		$request = Yii::$app->request;
		$site = $this->findSiteModel($site_id);
		$model = new SiteIpAddress();
		$model->site_id = $site_id;
		$model->ip_address = $request->getBodyParam('ip_address');
		$model->is_main = $request->getBodyParam('is_main');
		
		if ($model->save()) {
			return $model;
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	/**
	 * @SWG\Get(
	 *     path="/site/{site_id}/ip-address/{id}",
	 *     summary="Find site ip address by Site ID and ID",
	 *     operationId="SiteIpAddressView",
	 *     tags={"site-ip-address"},
	 *     @SWG\Parameter(
	 *         name="api_key",
	 *         in="query",
	 *         description="Api Key",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="site_id",
	 *         in="path",
	 *         description="Site ID",
	 *         required=true,
	 *         type="integer"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="id",
	 *         in="path",
	 *         description="ID",
	 *         required=true,
	 *         type="integer"
	 *     ),
	 *     @SWG\Response(
	 *         response=200,
	 *         description="Success"
	 *     ),
	 *     @SWG\Response(
	 *         response=402,
	 *         description="Bad request"
	 *     )
	 * )
	 */
	public function actionView($site_id, $id)
	{
		$request = Yii::$app->request;
		$model = $this->findModel($site_id, $id);
		return $model;
	}

	/**
	 * @SWG\Put(
	 *     path="/site/{site_id}/ip-address/{id}",
	 *     summary="Update site ip address by Site ID and ID",
	 *     operationId="SiteIpAddressUpdate",
	 *     tags={"site-ip-address"},
	 *     @SWG\Parameter(
	 *         name="api_key",
	 *         in="query",
	 *         description="Api Key",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="site_id",
	 *         in="path",
	 *         description="Site ID",
	 *         required=true,
	 *         type="integer"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="id",
	 *         in="path",
	 *         description="ID",
	 *         required=true,
	 *         type="integer"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="ip_address",
	 *         in="formData",
	 *         description="IP address",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="is_main",
	 *         in="formData",
	 *         description="Is main",
	 *         required=false,
	 *         type="integer",
	 *         enum={0, 1}
	 *     ),
	 *     @SWG\Response(
	 *         response=204,
	 *         description="Success"
	 *     ),
	 *     @SWG\Response(
	 *         response=402,
	 *         description="Bad request"
	 *     )
	 * )
	 */
	public function actionUpdate($site_id, $id)
	{
		$request = Yii::$app->request;
		$model = $this->findModel($site_id, $id);
		$model->ip_address = $request->getBodyParam('ip_address', $model->ip_address);
		$model->is_main = $request->getBodyParam('is_main', $model->is_main);

		if ($model->save()) {
			return $model;
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	/**
	 * @SWG\Delete(
	 *     path="/site/{site_id}/ip-address/{id}",
	 *     summary="Delete site ip address by Site ID and ID",
	 *     operationId="SiteIpAddressDelete",
	 *     tags={"site-ip-address"},
	 *     @SWG\Parameter(
	 *         name="api_key",
	 *         in="query",
	 *         description="Api Key",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="site_id",
	 *         in="path",
	 *         description="Site ID",
	 *         required=true,
	 *         type="integer"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="id",
	 *         in="path",
	 *         description="ID",
	 *         required=true,
	 *         type="integer"
	 *     ),
	 *     @SWG\Response(
	 *         response=204,
	 *         description="Success"
	 *     ),
	 *     @SWG\Response(
	 *         response=402,
	 *         description="Bad request"
	 *     )
	 * )
	 */
	public function actionDelete($site_id, $id)
	{
		$request = Yii::$app->request;
		$model = $this->findModel($site_id, $id);

		if ($model->delete()) {
			throw new HttpException(204);
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	protected function findModel($site_id, $id)
	{
		if (($model = SiteIpAddress::findOne(['site_id' => $site_id, 'id' => $id])) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException(Yii::t('api.controller', 'IP not found.'));
		}
	}

	protected function findSiteModel($id)
	{
		if (($model = Site::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException(Yii::t('api.controller', 'Site not found.'));
		}
	}
}
