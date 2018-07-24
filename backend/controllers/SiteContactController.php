<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\SiteContact;
use common\widgets\Alert;
use common\components\rbac\Role;
use backend\models\forms\FormSiteContact;
use backend\models\searches\SearchSiteContact;
use common\models\events\logs\EventLogSiteContact;
use backend\models\forms\FormSiteContacts;

/**
 * SiteContactController
 */
class SiteContactController extends \backend\components\Controller
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
							$id_site = Yii::$app->request->getQueryParam('id');
							$model_site = $this->loadSite($id_site);
							return Yii::$app->user->can('SiteContactController.actionCreate') ||
									Yii::$app->user->can('SiteContactController.actionCreateOwner', ['model' => $model_site]) ||
									Yii::$app->user->can('SiteContactController.actionCreateSiteOwner', ['model' => $model_site]);
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
							$model_site = $this->loadSiteBySiteContact($id_contact);
							return Yii::$app->user->can('SiteContactController.actionEdit') ||
									Yii::$app->user->can('SiteContactController.actionEditOwner', ['model' => $model_site]) ||
									Yii::$app->user->can('SiteContactController.actionEditSiteOwner', ['model' => $model_site]);
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
							$model_site = $this->loadSiteBySiteContact($id_contact);
							return Yii::$app->user->can('SiteContactController.actionDelete') ||
									Yii::$app->user->can('SiteContactController.actionDeleteOwner', ['model' => $model_site]) ||
									Yii::$app->user->can('SiteContactController.actionDeleteSiteOwner', ['model' => $model_site]);
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
							$model_site = $this->loadSiteBySiteContact($id_contact);
							return Yii::$app->user->can('SiteContactController.actionView') ||
									Yii::$app->user->can('SiteContactController.actionViewOwner', ['model' => $model_site]) ||
									Yii::$app->user->can('SiteContactController.actionViewSiteOwner', ['model' => $model_site]);
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
							$id_site = Yii::$app->request->getQueryParam('id');
							$model_site = $this->loadSite($id_site);
							return Yii::$app->user->can('SiteContactController.actionList') ||
									Yii::$app->user->can('SiteContactController.actionListOwner', ['model' => $model_site]) ||
									Yii::$app->user->can('SiteContactController.actionListSiteOwner', ['model' => $model_site]);
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
		$model = $this->loadSite($id);
		$form = new FormSiteContact();
		$form->loadAttributes(FormSiteContact::SCENARIO_CREATE, $model);

		if ($form->load(Yii::$app->request->post()) && $model_contact = $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Contact have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site-contact/list', 'id' => $model_contact->site_id]));
		}

		return $this->render('create', [
			'form' => $form,
			'model' => $model,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadSiteContact($id);
		$form = new FormSiteContact();
		$form->loadAttributes(FormSiteContact::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $model_contact = $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Contact have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site-contact/list', 'id' => $model_contact->site_id]));
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionView($id)
	{
		$model = $this->loadSiteContact($id);

		return $this->render('view', [
			'model' => $model,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->loadSiteContact($id);

		$event = new EventLogSiteContact();
		$event->model = $model;
		$model->on(EventLogSiteContact::EVENT_BEFORE_DELETE, [$event, EventLogSiteContact::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Contact have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionList($id)
	{
		$model = $this->loadSite($id);
		$search = new SearchSiteContact();
		$data_provider = $search->search();
		$data_provider->query->andWhere([SiteContact::tableName(). '.site_id' => $model->id]);
		$filter_model = $search->filter();

		$form_contacts = new FormSiteContacts();

		if ($form_contacts->load(Yii::$app->request->get()) && $form_contacts->save()) {
			return $this->redirect(['/site-contact/list', 'id' => $model->id]);
		}

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'model' => $model,
			'form_contacts' => $form_contacts,
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

	private function loadSiteContact($id)
	{
		$model = SiteContact::find()->andWhere([
			SiteContact::tableName(). '.id' => $id,
		])->andWhere(['in', SiteContact::tableName(). '.status', [
			SiteContact::STATUS_INACTIVE,
			SiteContact::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Contact not found'));
		}

		return $model;
	}
	
	private function loadSiteBySiteContact($id) 
	{
		$model_contact = $this->loadSiteContact($id);
		$id_site = $model_contact->relationSite->id;
		$model = $this->loadSite($id_site);
		return $model;
	}
}
