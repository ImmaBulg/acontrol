<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\models\Contact;
use common\models\Site;
use common\models\Tenant;
use common\widgets\Alert;
use common\models\Report;
use common\components\rbac\Role;
use backend\models\forms\FormSite;
use backend\models\forms\FormTenant;
use backend\models\forms\FormTenants;
use backend\models\searches\SearchUser;
use backend\models\searches\SearchSite;
use backend\models\searches\SearchTenant;

/**
 * ClientController
 */
class ClientController extends \backend\components\Controller
{
	public $enableCsrfValidation = false;

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'accessView' => [
				'class' => AccessControl::className(),
				'only' => ['view'],
				'rules' => [
					[
						'allow' => true,
                        'matchCallback' => function ($rule, $action) {
							$id_client = Yii::$app->request->getQueryParam('id');
							$model_client = $this->loadClient($id_client);
							return Yii::$app->user->can('ClientController.actionView') ||
									Yii::$app->user->can('ClientController.actionViewOwner', ['model' => $model_client]);
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
						'roles' => ['ClientController.actionList', 'ClientController.actionListOwner'],
					],
				],
			],
			'accessSiteCreate' => [
				'class' => AccessControl::className(),
				'only' => ['site-create'],
				'rules' => [
					[
						'allow' => true,
                        'matchCallback' => function ($rule, $action) {
							$id_client = Yii::$app->request->getQueryParam('id');
							$model_client = $this->loadClient($id_client);
							return Yii::$app->user->can('ClientController.actionSiteCreate') ||
									Yii::$app->user->can('ClientController.actionSiteCreateOwner', ['model' => $model_client]);
						},
					],
				],
			],
			'accessSites' => [
				'class' => AccessControl::className(),
				'only' => ['sites'],
				'rules' => [
					[
						'allow' => true,
                        'matchCallback' => function ($rule, $action) {
							$id_client = Yii::$app->request->getQueryParam('id');
							$model_client = $this->loadClient($id_client);
							return Yii::$app->user->can('ClientController.actionSites') ||
									Yii::$app->user->can('ClientController.actionSitesOwner', ['model' => $model_client]);
						},
					],
				],
			],
			'accessTenants' => [
				'class' => AccessControl::className(),
				'only' => ['tenants'],
				'rules' => [
					[
						'allow' => true,
                        'matchCallback' => function ($rule, $action) {
							$id_client = Yii::$app->request->getQueryParam('id');
							$model_client = $this->loadClient($id_client);
							return Yii::$app->user->can('ClientController.actionTenants') ||
									Yii::$app->user->can('ClientController.actionTenantsOwner', ['model' => $model_client]);
						},
					],
				],
			],
			'accessTenantCreate' => [
				'class' => AccessControl::className(),
				'only' => ['tenant-create'],
				'rules' => [
					[
						'allow' => true,
                        'matchCallback' => function ($rule, $action) {
							$id_client = Yii::$app->request->getQueryParam('id');
							$model_client = $this->loadClient($id_client);
							return Yii::$app->user->can('ClientController.actionTenantCreate') ||
									Yii::$app->user->can('ClientController.actionTenantCreateOwner', ['model' => $model_client]);
						},
					],
				],
			],
		]);
	}

	public function actionView($id)
	{
		$model = $this->loadClient($id);

		return $this->render('view', [
			'model' => $model,
		]);
	}

	public function actionList()
	{
		$search = new SearchUser();
		$data_provider = $search->search(); 
		$data_provider->query->andWhere([User::tableName(). '.role' => Role::ROLE_CLIENT]);
		// If current user is client then enable filter of provider with its conditions
		if(!Yii::$app->user->can('ClientController.actionList'))  {
			$users_model = Yii::$app->user->identity->relationUserOwners;
			$user_ids = ArrayHelper::getColumn($users_model, 'user_id'); // Add sub clients
			array_unshift($user_ids, Yii::$app->user->id); // Add owner
			$data_provider->query->andWhere([User::tableName(). '.id' => $user_ids]);
		}
		$filter_model = $search->filter();
		
		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
		]);
	}

	public function actionSiteCreate($id)
	{
		$model = $this->loadClient($id);
		$form = new FormSite();
		$form->user_id = $model->id;
		$form->auto_issue_reports = array_keys(Report::getAutoIssueListTypes());

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Site have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/client/sites', 'id' => $model->id]));
		}

		return $this->render('site-create', [
			'form' => $form,
			'model' => $model,
		]);
	}

	public function actionSites($id)
	{
		$model = $this->loadClient($id);
		$search = new SearchSite();
		$data_provider = $search->search();
		$data_provider->query->andWhere([Site::tableName(). '.user_id' => $model->id]);
		$filter_model = $search->filter();

		return $this->render('sites', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'model' => $model,
		]);
	}

	public function actionTenantCreate($id)
	{
		$model = $this->loadClient($id);
		$form = new FormTenant();

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Tenant have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/client/tenants', 'id' => $model->id]));
		}

		return $this->render('/tenant/tenant-form', [
			'form' => $form,
			'extra_model' => $model,
		]);
	}

	public function actionTenants($id, $expired = false)
	{
		$model = $this->loadClient($id);
		$search = new SearchTenant();
		$data_provider = $search->search();
		$data_provider->query->andWhere([Tenant::tableName(). '.user_id' => $model->id]);

		if ($expired) {
			$data_provider->query->andWhere(['<', Tenant::tableName(). '.exit_date', strtotime('midnight')]);
		} else {
			$data_provider->query->andWhere([
				'or',
				Tenant::tableName(). '.exit_date IS NULL',
				['>=', Tenant::tableName(). '.exit_date', strtotime('midnight')],
			]);
		}

		$filter_model = $search->filter();
		$form_tenants = new FormTenants();

		if ($form_tenants->load(Yii::$app->request->get()) && $form_tenants->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Tenants have been updated.'));
			return $this->redirect(['/client/tenants', 'id' => $model->id]);
		}

		return $this->render('tenants', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'model' => $model,
			'form_tenants' => $form_tenants,
			'expired' => $expired,
		]);
	}

	private function loadClient($id)
	{
		$model = User::find()->where([
			'id' => $id,
			'role' => Role::ROLE_CLIENT,
		])->andWhere(['in', 'status', [
			User::STATUS_INACTIVE,
			User::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Client not found'));
		}

		return $model;
	}
}
