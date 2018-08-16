<?php

namespace backend\controllers;

use backend\components\Controller;
use backend\filters\BaseLayoutAdmin;
use backend\models\forms\FormIrregularHours;
use common\models\SiteIrregularHours;
use common\models\TenantBillingSetting;
use common\models\TenantIrregularHours;
use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\models\Site;
use common\models\Tenant;
use common\models\TenantReport;
use common\models\Report;
use common\models\UserOwnerTenant;
use common\models\UserOwnerSite;
use common\widgets\Alert;
use common\components\rbac\Role;
use backend\models\forms\FormTenant;
use backend\models\forms\FormTenants;
use backend\models\forms\FormReport;
use backend\models\searches\SearchTenant;
use backend\models\searches\SearchReport;
use common\models\events\logs\EventLogTenant;
use yii\web\Response;


/**
 * TenantController
 */
class TenantController extends Controller
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
						'roles' => ['TenantController.actionCreate', 'TenantController.actionCreateOwner', 'TenantController.actionDeleteSiteOwner'],
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
							$id_tenant = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenant($id_tenant);
							return Yii::$app->user->can('TenantController.actionEdit') ||
									Yii::$app->user->can('TenantController.actionEditOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantController.actionEditTenantOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantController.actionEditSiteOwner', ['model' => $model_tenant]);
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
						'roles' => ['TenantController.actionDelete', 'TenantController.actionDeleteOwner', 'TenantController.actionDeleteSiteOwner'],
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
							$id_tenant = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenant($id_tenant);
							return Yii::$app->user->can('TenantController.actionView') ||
									Yii::$app->user->can('TenantController.actionViewOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantController.actionViewSiteOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantController.actionViewTenantOwner', ['model' => $model_tenant]);
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
						'roles' => ['TenantController.actionList', 'TenantController.actionListOwner', 'TenantController.actionListSiteOwner', 'TenantController.actionListTenantOwner'],
					],
				],
			],
			'accessReports' => [
				'class' => AccessControl::className(),
				'only' => ['reports'],
				'rules' => [
					[
						'allow' => true,
						'matchCallback' => function ($rule, $action) {
							$id_tenant = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenant($id_tenant);
							return Yii::$app->user->can('TenantController.actionReports') ||
									Yii::$app->user->can('TenantController.actionReportsOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantController.actionReportsTenantOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('TenantController.actionReportsSiteOwner', ['model' => $model_tenant]);
						},
					],
				],
			],
		]);

	}





	public function actionCreate()
	{
		$form = new FormTenant();

		if ($form->load(Yii::$app->request->post()) && $model_tenant = $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Tenant have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/tenant/list']));
		}

		return $this->render('tenant-form', [
			'form' => $form,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadTenant($id);
		$form = new FormTenant();
		$form->loadAttributes(FormTenant::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $model_tenant = $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Tenant have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site/tenants', 'id' => $model_tenant->site_id]));
		}

		return $this->render('tenant-form', [
			'form' => $form,
			'model' => $model,
            'irregular_data' => [
                'days_of_week' => $model->overwrite_site ? TenantIrregularHours::getDays() : SiteIrregularHours::getDays(),
                'model_data' => $model->overwrite_site ? TenantIrregularHours::find()->where(['tenant_id' => $id])->asArray()->all() : SiteIrregularHours::find()->where(['site_id' => $form->site_id])->asArray()->all(),
                'irregular_additional_percent' => $model->overwrite_site ? (TenantBillingSetting::find(['tenant_id' => $id])->one())->irregular_additional_percent : $model->relationSite->relationSiteBillingSetting->irregular_additional_percent,
                'overwrite_site' => $model->overwrite_site,
                'language' => [
                    'hours_from_text' => $model->overwrite_site ? (new TenantIrregularHours())->getAttributeLabel('hours_from') : (new SiteIrregularHours())->getAttributeLabel('hours_from'),
                    'hours_to_text' => $model->overwrite_site ? (new TenantIrregularHours())->getAttributeLabel('hours_to') : (new SiteIrregularHours())->getAttributeLabel('hours_to'),
                    'delete_text' => Yii::t('backend.tenant', 'Delete row'),
                    'percent_text' => Yii::t('backend.tenant', 'Penalty Percent'),
                    'add_text' => Yii::t('backend.tenant', 'Add row'),
                    'update_text' => Yii::t('backend.tenant', 'Update'),
                    'success_text' => Yii::t('backend.tenant', 'Data was successfully updated'),
                    'overwrite_site' => Yii::t('backend.tenant', 'Overwrite site settings'),
                ],
                'tenant_id' => $id
            ],
		]);		
	}

    public function actionSaveIrregularHours()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request->post();
        $irregular_percent = $request['data'][1];
        unset($request['data'][1]);
        $request['data'] = $request['data'][0];

        $form = new FormIrregularHours();

        if ($form->load($request,'') && $form->save()) {
            $tenantBillingSettings = TenantBillingSetting::find(['tenant_id' => $request['tenant_id']])->one();
            $tenantBillingSettings->irregular_additional_percent = $irregular_percent;
            $tenantBillingSettings->save();

            return [
                'data' => TenantIrregularHours::find()->where(['tenant_id' => $form->tenant_id])->all(),
                'irregular_additional_percent' => $irregular_percent];
        }

        return $form->getFirstErrors();
    }

    public function actionSaveIrregularHour()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        $model = TenantBillingSetting::find()->where(['tenant_id' => $data['tenant_id']])->one();
        $model->attributes = $data['data'][0];
        if (!$model->save())
            throw new BadRequestHttpException();
        else
            return $data['data'][0];
    }

    public function actionDelIrregularHour()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        $model = TenantBillingSetting::find()->where(['tenant_id' => $data['tenant_id']])->one();
        $data['data'][0]["irregular_hours_from"] = '';
        $data['data'][0]["irregular_hours_to"] = '';
        $data['data'][0]["irregular_additional_percent"] = null;
        $model->attributes = $data['data'][0];
        if (!$model->save())
            throw new BadRequestHttpException();
        else
            return $data['data'][0];
    }

	public function actionView($id)
	{
		$model = $this->loadTenant($id);

		return $this->render('view', [
			'model' => $model,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->loadTenant($id);

		$event = new EventLogTenant();
		$event->model = $model;
		$model->on(EventLogTenant::EVENT_BEFORE_DELETE, [$event, EventLogTenant::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Tenant have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionList($expired = false)
	{
		$search = new SearchTenant();
		$data_provider = $search->search();

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
			return $this->redirect(['/tenant/list']);
		}

		// If current user is client then enable filter of provider with its conditions
		if(!Yii::$app->user->can('TenantController.actionList')) {
			if(Yii::$app->user->can('TenantController.actionListOwner')) {
				$users_model = Yii::$app->user->identity->relationUserOwners;
				$user_ids = ArrayHelper::getColumn($users_model, 'user_id'); // Add sub tenant
				array_unshift($user_ids, Yii::$app->user->id); // Add tenant of owner
				$data_provider->query->andWhere([Tenant::tableName(). '.user_id' => $user_ids]);
			} elseif(Yii::$app->user->can('TenantController.actionListSiteOwner')) {
				$sites = UserOwnerSite::find()->where(['user_owner_id' => Yii::$app->user->id])->all();
				$site_ids = ArrayHelper::getColumn($sites, 'site_id');
				$data_provider->query->andWhere([Tenant::tableName(). '.site_id' => $site_ids]);
			} elseif(Yii::$app->user->can('TenantController.actionListTenantOwner')) {
				$tenants = UserOwnerTenant::find()->where(['user_owner_id' => Yii::$app->user->id])->all();
				$tenant_ids = ArrayHelper::getColumn($tenants, 'tenant_id');
				$data_provider->query->andWhere([Tenant::tableName(). '.id' => $tenant_ids]);
			}
		}
		
		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'form_tenants' => $form_tenants,
			'expired' => $expired,
		]);
	}

	public function actionReports($id)
	{
		$model = $this->loadTenant($id);
		$model_site = $model->relationSite;
		$model_site_setting = $model_site->relationSiteBillingSetting; // setting Site [db site_billing_settings]




		$form = new FormReport();
		$form->level = Report::LEVEL_TENANT;
		$form->site_owner_id = $model_site->user_id;
		$form->site_id = $model_site->id;
		$form->tenants_id[$model->id] = $model->id;

		if ($model_site_setting != null && $model_site_setting->billing_day != null) {
			$from_date = new \DateTime();
			$from_date->modify("first day of -1 month midnight");
			$from_date = $from_date->getTimestamp() + (($model_site_setting->billing_day - 1) * 86400);
			$form->from_date = $from_date;

			$to_date = new \DateTime();
			$to_date->modify("first day of this month midnight");
			$to_date = $to_date->getTimestamp() + (($model_site_setting->billing_day - 2) * 86400);
			$form->to_date = $to_date;
		}

		$session = Yii::$app->session;

		if ($session->has('issue_from_date')) {
			$form->from_date = $session->get('issue_from_date');
		}
		if ($session->has('issue_to_date')) {
			$form->to_date = $session->get('issue_to_date');
		}

		$form->skip_errors = Yii::$app->request->getQueryParam('skip_errors', false);
		
		if ($form->load(Yii::$app->request->get()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Report have been added.'));
			return $this->redirect(['/tenant/reports', 'id' => $model->id]);
		}

		$search = new SearchReport();
		$data_provider = $search->search();
		$data_provider->query->andWhere([
			TenantReport::tableName(). '.tenant_id' => $model->id,
			Report::tableName(). '.level' => Report::LEVEL_TENANT,
		]);
		$filter_model = $search->filter();

		return $this->render('reports', [
			'form' => $form,
			'model' => $model,
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
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
}
