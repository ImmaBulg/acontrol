<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\Tenant;
use common\models\TenantContact;
use common\widgets\Alert;
use common\components\rbac\Role;
use backend\models\forms\FormTenantContact;
use backend\models\searches\SearchTenantContact;
use common\models\events\logs\EventLogTenantContact;

/**
 * TenantContactController
 */
class TenantContactController extends \backend\components\Controller
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
						'matchCallback' => function ($rule, $action) {
							$id_tenant = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenant($id_tenant);
							return Yii::$app->user->can('TenantContactController.actionCreate') ||
									Yii::$app->user->can('TenantContactController.actionCreateOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantContactController.actionCreateTenantOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantContactController.actionCreateSiteOwner', ['model' => $model_tenant]);
						},
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
							$id_contact = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenantByTenantContact($id_contact);
							return Yii::$app->user->can('TenantContactController.actionEdit') ||
									Yii::$app->user->can('TenantContactController.actionEditOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantContactController.actionEditTenantOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantContactController.actionEditSiteOwner', ['model' => $model_tenant]);
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
							$id_contact = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenantByTenantContact($id_contact);
							return Yii::$app->user->can('TenantContactController.actionDelete') ||
									Yii::$app->user->can('TenantContactController.actionDeleteOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantContactController.actionDeleteTenantOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantContactController.actionDeleteSiteOwner', ['model' => $model_tenant]);
						},
					],
				],
			],
			'accessView' => [
				'class' => AccessControl::className(),
				'only' => ['view'],
				'rules' => [
					[
						'allow' => true,
						'matchCallback' => function ($rule, $action) {
							$id_contact = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenantByTenantContact($id_contact);
							return Yii::$app->user->can('TenantContactController.actionView') ||
									Yii::$app->user->can('TenantContactController.actionViewOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantContactController.actionViewTenantOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantContactController.actionViewSiteOwner', ['model' => $model_tenant]);
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
						'matchCallback' => function ($rule, $action) {
							$id_tenant = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenant($id_tenant);
							return Yii::$app->user->can('TenantContactController.actionList') ||
									Yii::$app->user->can('TenantContactController.actionListOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantContactController.actionListTenantOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantContactController.actionListSiteOwner', ['model' => $model_tenant]);
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

	public function actionCreate($id)
	{
		$model = $this->loadTenant($id);
		$form = new FormTenantContact();
		$form->loadAttributes(FormTenantContact::SCENARIO_CREATE, $model);

		if ($form->load(Yii::$app->request->post()) && $model_contact = $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Contact have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/tenant-contact/list', 'id' => $model_contact->tenant_id]));
		}

		return $this->render('create', [
			'form' => $form,
			'model' => $model,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadTenantContact($id);
		$form = new FormTenantContact();
		$form->loadAttributes(FormTenantContact::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $model_contact = $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Contact have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/tenant-contact/list', 'id' => $model_contact->tenant_id]));
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionView($id)
	{
		$model = $this->loadTenantContact($id);

		return $this->render('view', [
			'model' => $model,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->loadTenantContact($id);

		$event = new EventLogTenantContact();
		$event->model = $model;
		$model->on(EventLogTenantContact::EVENT_BEFORE_DELETE, [$event, EventLogTenantContact::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Contact have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionList($id)
	{
		$model = $this->loadTenant($id);
		$search = new SearchTenantContact();
		$data_provider = $search->search();
		$data_provider->query->andWhere([TenantContact::tableName(). '.tenant_id' => $model->id]);
		$filter_model = $search->filter();

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'model' => $model,
		]);
	}

	private function loadTenant($id)
	{
		$model = Tenant::find()->andWhere([
			Tenant::tableName(). '.id' => $id,
		])->andWhere(['in', Tenant::tableName(). '.status', [
			Tenant::STATUS_INACTIVE,
			Tenant::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Tenant not found'));
		}

		return $model;	
	}

	private function loadTenantContact($id)
	{
		$model = TenantContact::find()->andWhere([
			TenantContact::tableName(). '.id' => $id,
		])->andWhere(['in', TenantContact::tableName(). '.status', [
			TenantContact::STATUS_INACTIVE,
			TenantContact::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Contact not found'));
		}

		return $model;
	}
	
	private function loadTenantContactByTenant($id) 
	{
		$model = TenantContact::find()->andWhere([
			TenantContact::tableName(). '.tenant_id' => $id
		])->andWhere(['in', TenantContact::tableName(). '.status', [
			TenantContact::STATUS_INACTIVE,
			TenantContact::STATUS_ACTIVE,
		]])->one();
		
		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Contact not found'));
		}

		return $model;
	}
	
	private function loadTenantByTenantContact($id) 
	{
		$model_contact = $this->loadTenantContact($id);
		$id_tenant = $model_contact->relationTenant->id;
		$model = $this->loadTenant($id_tenant);
		return $model;
	}
}
