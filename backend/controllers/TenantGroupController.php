<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\TenantGroup;
use common\widgets\Alert;
use backend\models\forms\FormTenantGroup;
use backend\models\searches\SearchTenantGroup;
use common\models\events\logs\EventLogTenantGroup;
use common\models\Site;

/**
 * TenantGroupController
 */
class TenantGroupController extends \backend\components\Controller
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
						'roles' => ['TenantGroupController.actionCreate'],
					],
				],
			],
			'accessEdit' => [
				'class' => AccessControl::className(),
				'only' => ['edit'],
				'rules' => [
					[
						'allow' => true,
						'matchCallback' => function ($rule, $action) {
							$id_tenant_group = Yii::$app->request->getQueryParam('id');
							$model_site = $this->loadSiteByTenantGroup($id_tenant_group);
							return Yii::$app->user->can('TenantGroupController.actionEdit') ||
									Yii::$app->user->can('TenantGroupController.actionEditOwner', ['model' => $model_site]) ||
									Yii::$app->user->can('TenantGroupController.actionEditSiteOwner', ['model' => $model_site]);
						},
					],
				],
			],
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'matchCallback' => function ($rule, $action) {
							$id_tenant_group = Yii::$app->request->getQueryParam('id');
							$model_site = $this->loadSiteByTenantGroup($id_tenant_group);
							return Yii::$app->user->can('TenantGroupController.actionDelete') ||
									Yii::$app->user->can('TenantGroupController.actionDeleteOwner', ['model' => $model_site]) ||
									Yii::$app->user->can('TenantGroupController.actionDeleteSiteOwner', ['model' => $model_site]);
						},
					],
				],
			],
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['TenantGroupController.actionList'],
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

	public function actionCreate()
	{
		$form = new FormTenantGroup();

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Group have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/tenant-group/list']));
		}

		return $this->render('create', [
			'form' => $form,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadTenantGroup($id);
		$form = new FormTenantGroup();
		$form->loadAttributes(FormTenantGroup::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $model_group = $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Group have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site/tenant-groups', 'id' => $model_group->site_id]));
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionDelete($id)
	{
		$model = $this->loadTenantGroup($id);

		$event = new EventLogTenantGroup();
		$event->model = $model;
		$model->on(EventLogTenantGroup::EVENT_BEFORE_DELETE, [$event, EventLogTenantGroup::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Group have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionList()
	{
		$search = new SearchTenantGroup();
		$data_provider = $search->search();
		$filter_model = $search->filter();

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
		]);
	}

	private function loadTenantGroup($id)
	{
		$model = TenantGroup::find()->andWhere([
			TenantGroup::tableName(). '.id' => $id,
		])->andWhere(['in', TenantGroup::tableName(). '.status', [
			TenantGroup::STATUS_INACTIVE,
			TenantGroup::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Group not found'));
		}

		return $model;
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
	
	private function loadSiteByTenantGroup($id)
	{
		$model_tenant_group = $this->loadTenantGroup($id);
		$id_site = $model_tenant_group->site_id;
		$model = $this->loadSite($id_site);
		return $model;
	}
}
