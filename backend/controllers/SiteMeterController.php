<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Meter;
use common\models\MeterChannel;
use common\models\Site;
use common\models\SiteMeterTree;
use common\widgets\Alert;
use backend\models\forms\FormSiteMeter;
use backend\models\forms\FormSiteMeterTree;

/**
 * SiteMeterController
 */
class SiteMeterController extends \backend\components\Controller
{
	public $enableCsrfValidation = false;

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'accessCreate' => [
				'class' => AccessControl::className(),
				'only' => ['create'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['SiteMeterController.actionCreate', 'SiteMeterController.actionCreateOwner', 'SiteMeterController.actionCreateSiteOwner'],
					],
				],
			],
			'accessEdit' => [
				'class' => AccessControl::className(),
				'only' => ['edit'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['SiteMeterController.actionEdit'],
					],
				],
			],
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['SiteMeterController.actionDelete'],
					],
				],
			],
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'matchCallback' => function ($rule, $action) {
							$id_site = Yii::$app->request->getQueryParam('id');
							$model_site = $this->loadSite($id_site);
							return Yii::$app->user->can('SiteMeterController.actionList') ||
									Yii::$app->user->can('SiteMeterController.actionListOwner', ['model' => $model_site]) ||
									Yii::$app->user->can('SiteMeterController.actionListSiteOwner', ['model' => $model_site]);
						},
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'delete' => ['post'],
				],
			],
		]);
	}

	public function actionCreate($id, $meter_id = null)
	{
		$model = $this->loadSite($id);
		$form = new FormSiteMeter();
		$form->meter_id = $meter_id;
		$form->loadAttributes(FormSiteMeter::SCENARIO_CREATE, $model);

		if ($form->load(Yii::$app->request->post()) && $model_meter = $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter association have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site-meter/list', 'id' => $model_meter->site_id]));
		}

		return $this->render('create', [
			'form' => $form,
			'model' => $model,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadMeter($id);
		$form = new FormSiteMeter();
		$form->loadAttributes(FormSiteMeter::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $model_meter = $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter association have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site-meter/list', 'id' => $model_meter->site_id]));
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionList($id)
	{
		$model = $this->loadSite($id);
		$form = new FormSiteMeterTree();
		$form->loadAttributes($model);

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Tree have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site-meter/list', 'id' => $model->id]));
		}

		$tree = MeterChannel::find()
		->joinWith([
			'relationMeter',
			'relationMeter.relationMeterType',
			'relationSiteMeterTree',
		], 'LEFT JOIN')
		->andWhere([Meter::tableName(). '.site_id' => $model->id])
		->andWhere(SiteMeterTree::tableName(). '.parent_meter_channel_id IS NULL')
		->all();

		return $this->render('list', [
			'model' => $model,
			'form' => $form,
			'tree' => $tree,
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

	private function loadMeter($id)
	{
		$model = Meter::find()->andWhere([
			Meter::tableName(). '.id' => $id,
		])->andWhere(['in', Meter::tableName(). '.status', [
			Meter::STATUS_INACTIVE,
			Meter::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Site meter not found'));
		}

		return $model;
	}
}
