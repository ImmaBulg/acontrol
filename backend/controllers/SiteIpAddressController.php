<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\widgets\Alert;
use common\models\Site;
use common\models\SiteIpAddress;
use backend\models\searches\SearchSiteIpAddress;

/**
 * SiteIpAddressController
 */
class SiteIpAddressController extends \backend\components\Controller
{
	public $enableCsrfValidation = false;

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'delete' => ['post'],
				],
			],
		]);
	}

	public function actionCreate($id)
	{
		$model = $this->loadSite($id);
		$ip_address = new SiteIpAddress();
		$ip_address->site_id = $model->id;
		$ip_address->status = SiteIpAddress::STATUS_ACTIVE;

		if ($ip_address->load(Yii::$app->request->post()) && $ip_address->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'IP have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site-ip-address/list', 'id' => $ip_address->site_id]));
		}

		return $this->render('create', [
			'ip_address' => $ip_address,
			'model' => $model,
		]);
	}

	public function actionEdit($id)
	{
		$ip_address = $this->loadSiteIpAddress($id);

		if ($ip_address->load(Yii::$app->request->post()) && $ip_address->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'IP have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site-ip-address/list', 'id' => $ip_address->site_id]));
		}

		return $this->render('edit', [
			'ip_address' => $ip_address,
		]);		
	}

	public function actionDelete($id)
	{
		$model = $this->loadSiteIpAddress($id);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'IP have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionList($id)
	{
		$model = $this->loadSite($id);
		$search = new SearchSiteIpAddress();
		$data_provider = $search->search();
		$data_provider->query->andWhere([SiteIpAddress::tableName(). '.site_id' => $model->id]);
		$filter_model = $search->filter();

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'model' => $model,
		]);
	}

	private function loadSite($id)
	{
		$model = Site::find()->andWhere([
			'id' => $id,
		])->andWhere(['in', 'status', [
			Site::STATUS_INACTIVE,
			Site::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Site not found'));
		}

		return $model;
	}

	private function loadSiteIpAddress($id)
	{
		$model = SiteIpAddress::find()->andWhere([
			SiteIpAddress::tableName(). '.id' => $id,
		])->andWhere(['in', SiteIpAddress::tableName(). '.status', [
			SiteIpAddress::STATUS_INACTIVE,
			SiteIpAddress::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'IP not found'));
		}

		return $model;
	}
}
