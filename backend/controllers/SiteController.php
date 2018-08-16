<?php
namespace backend\controllers;

use backend\models\forms\FormSiteIrregularHours;
use backend\models\forms\FormSiteIrregulatHours;
use common\models\Log;
use common\models\SiteBillingSetting;
use common\models\SiteIrregularHours;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use common\models\User;
use common\models\Site;
use common\models\Tenant;
use common\models\TenantGroup;
use common\models\MeterChannelGroup;
use common\models\Report;
use common\models\UserOwnerSite;
use common\models\UserOwnerTenant;
use common\widgets\Alert;
use common\components\rbac\Role;
use backend\models\forms\FormSite;
use backend\models\forms\FormTenant;
use backend\models\forms\FormTenants;
use backend\models\forms\FormTenantsUsers;
use backend\models\forms\FormTenantGroup;
use backend\models\forms\FormMeterChannelGroup;
use backend\models\forms\FormReport;
use backend\models\searches\SearchSite;
use backend\models\searches\SearchTenant;
use backend\models\searches\SearchTenantGroup;
use backend\models\searches\SearchMeterChannelGroup;
use backend\models\searches\SearchReport;
use common\models\events\logs\EventLogSite;
use yii\web\Response;

/**
 * SiteController
 */
class SiteController extends \backend\components\Controller
{
    public $enableCsrfValidation = false;


    /**
     * @inheritdoc
     */
    public function behaviors() {
        return array_merge(parent::behaviors(), [
            'accessCreate' => [
                'class' => AccessControl::className(),
                'only' => ['create'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['SiteController.actionCreate', 'SiteController.actionCreateOwner'],
                    ],
                ],
            ],
            'accessEdit' => [
                'class' => AccessControl::className(),
                'only' => ['edit', 'generate-users-password'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $id_site = Yii::$app->request->getQueryParam('id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('SiteController.actionEdit') ||
                                   Yii::$app->user->can('SiteController.actionEditOwner', ['model' => $model_site]) ||
                                   Yii::$app->user->can('SiteController.actionEditSiteOwner', ['model' => $model_site]);
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
                            $id_site = Yii::$app->request->getQueryParam('id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('SiteController.actionDelete') ||
                                   Yii::$app->user->can('SiteController.actionDeleteOwner', ['model' => $model_site]);
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
                            $id_site = Yii::$app->request->getQueryParam('id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('SiteController.actionView') ||
                                   Yii::$app->user->can('SiteController.actionViewOwner', ['model' => $model_site]) ||
                                   Yii::$app->user->can('SiteController.actionViewSiteOwner', ['model' => $model_site]);
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
                        'roles' => ['SiteController.actionList', 'SiteController.actionListOwner',
                                    'SiteController.actionListSiteOwner'],
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
                            $id_site = Yii::$app->request->getQueryParam('id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('SiteController.actionTenantCreate') ||
                                   Yii::$app->user->can('SiteController.actionTenantCreateOwner',
                                                        ['model' => $model_site]) ||
                                   Yii::$app->user->can('SiteController.actionTenantCreateSiteOwner',
                                                        ['model' => $model_site]);
                        },
                    ],
                ],
            ],
            'accessTenants' => [
                'class' => AccessControl::className(),
                'only' => ['tenants', 'tenants-users'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $id_site = Yii::$app->request->getQueryParam('id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('SiteController.actionTenants') ||
                                   Yii::$app->user->can('SiteController.actionTenantsOwner',
                                                        ['model' => $model_site]) ||
                                   Yii::$app->user->can('SiteController.actionTenantsSiteOwner',
                                                        ['model' => $model_site]);
                        },
                    ],
                ],
            ],
            'accessTenantGroupCreate' => [
                'class' => AccessControl::className(),
                'only' => ['tenant-group-create'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $id_site = Yii::$app->request->getQueryParam('id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('SiteController.actionTenantGroupCreate') ||
                                   Yii::$app->user->can('SiteController.actionTenantGroupCreateOwner',
                                                        ['model' => $model_site]) ||
                                   Yii::$app->user->can('SiteController.actionTenantGroupCreateSiteOwner',
                                                        ['model' => $model_site]);
                        },
                    ],
                ],
            ],
            'accessTenantGroups' => [
                'class' => AccessControl::className(),
                'only' => ['tenant-groups'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $id_site = Yii::$app->request->getQueryParam('id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('SiteController.actionTenantGroups') ||
                                   Yii::$app->user->can('SiteController.actionTenantGroupsOwner',
                                                        ['model' => $model_site]) ||
                                   Yii::$app->user->can('SiteController.actionTenantGroupsSiteOwner',
                                                        ['model' => $model_site]);
                        },
                    ],
                ],
            ],
            'accessMeterChannelGroupCreate' => [
                'class' => AccessControl::className(),
                'only' => ['meter-channel-group-create'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $id_site = Yii::$app->request->getQueryParam('id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('SiteController.actionMeterChannelGroupCreate') ||
                                   Yii::$app->user->can('SiteController.actionMeterChannelGroupCreateOwner',
                                                        ['model' => $model_site]) ||
                                   Yii::$app->user->can('SiteController.actionMeterChannelGroupCreateSiteOwner',
                                                        ['model' => $model_site]);
                        },
                    ],
                ],
            ],
            'accessMeterChannelGroups' => [
                'class' => AccessControl::className(),
                'only' => ['meter-channel-groups'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $id_site = Yii::$app->request->getQueryParam('id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('SiteController.actionMeterChannelGroups') ||
                                   Yii::$app->user->can('SiteController.actionMeterChannelGroupsOwner',
                                                        ['model' => $model_site]) ||
                                   Yii::$app->user->can('SiteController.actionMeterChannelGroupsSiteOwner',
                                                        ['model' => $model_site]);
                        },
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
                            $id_site = Yii::$app->request->getQueryParam('id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('SiteController.actionReports') ||
                                   Yii::$app->user->can('SiteController.actionReportsOwner',
                                                        ['model' => $model_site]) ||
                                   Yii::$app->user->can('SiteController.actionReportsSiteOwner',
                                                        ['model' => $model_site]);
                        },
                    ],
                ],
            ],
            'accessIssueReports' => [
                'class' => AccessControl::className(),
                'only' => ['issue-reports'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['SiteController.actionIssueReports'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'tenants-users' => ['post'],
                    'generate-users-password' => ['post'],
                ],
            ],
        ]);
    }


    public function actionCreate() {
        $form = new FormSite();
        $form->auto_issue_reports = array_keys(Report::getAutoIssueListTypes());
        if($form->load(Yii::$app->request->post()) && $model_site = $form->save()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Site have been added.'));
            return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site/list']));
        }
        return $this->render('site-form', [
            'form' => $form,
        ]);
    }


    public function actionEdit($id) {
        $model = $this->loadSite($id);
        $form = new FormSite();
        $form->loadAttributes(FormSite::SCENARIO_EDIT, $model);
        if($form->load(Yii::$app->request->post()) && $model_site = $form->edit()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Site have been updated.'));
            return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(),
                                                      ['/client/sites', 'id' => $model_site->user_id]));
        }
        return $this->render('site-form', [
            'form' => $form,
            'model' => $model,
            'irregular_data' => [
                'days_of_week' => SiteIrregularHours::getDays(),
                'model_data' => SiteIrregularHours::find()->where(['site_id' => $id])->asArray()->all(),
                'language' => [
                    'hours_from_text' => (new SiteIrregularHours())->getAttributeLabel('hours_from'),
                    'hours_to_text' => (new SiteIrregularHours())->getAttributeLabel('hours_to'),
                    'delete_text' => Yii::t('backend.tenant', 'Delete row'),
                    'add_text' => Yii::t('backend.tenant', 'Add row'),
                    'update_text' => Yii::t('backend.tenant', 'Update'),
                    'success_text' => Yii::t('backend.tenant', 'Data was successfully updated')
                ],
                'site_id' => $id
            ],
            'irregular_hour' => [
                'model_data' => SiteBillingSetting::find()->where(['site_id' => $id])->asArray()->one(),
                'site_irregular_hours_from' => $model->relationSiteBillingSetting->irregular_hours_from,
                'site_irregular_hours_to' => $model->relationSiteBillingSetting->irregular_hours_to,
                'site_irregular_additional_percent' => $model->relationSiteBillingSetting->irregular_additional_percent,
                'language' => [
                    'from_text' => Yii::t('backend.tenant', 'Irregular Hours From'),
                    'to_text' => Yii::t('backend.tenant', 'Irregular Hours To'),
                    'percent_text' => Yii::t('backend.tenant', 'Penalty Percent'),
                    'update_text' => Yii::t('backend.tenant', 'Update'),
                    'delete_text' => Yii::t('backend.tenant', 'Delete'),
                    'success_text' => Yii::t('backend.tenant', 'Data was successfully updated')
                ],
                'site_id' => $id
            ],
        ]);
    }

    public function actionSaveIrregularHours() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new FormSiteIrregularHours();
        $data = Yii::$app->request->post();

        if ($form->load($data, '') && $form->save()) {
            return SiteIrregularHours::find()->where(['site_id' => $data['site_id']])->asArray()->all();
        }

        return $form->getFirstErrors();
    }

    public function actionSaveIrregularHour() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = Yii::$app->request->post();

        $model = SiteBillingSetting::find()->where(['site_id' => $data['site_id']])->one();
        $model->attributes = $data;
        if (!$model->save()) {
        } else {
            return $data;
        }
    }

    public function actionDelIrregularHour() {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();
        $model = SiteBillingSetting::find()->where(['site_id' => $data['site_id']])->one();
        $data['irregular_additional_percent'] = null;
        $model->attributes = $data;
        if (!$model->save()) {
            throw new BadRequestHttpException();
        } else {
            return $data;
        }
    }

    public function actionView($id) {
        $model = $this->loadSite($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }


    public function actionDelete($id) {
        $model = $this->loadSite($id);
        $event = new EventLogSite();
        $event->model = $model;
        $model->on(EventLogSite::EVENT_BEFORE_DELETE, [$event, EventLogSite::METHOD_DELETE]);
        if($model->delete()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Site have been deleted.'));
            return $this->goBackReferrer();
        }
        else {
            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
        }
    }


    public function actionList() {
        $search = new SearchSite();
        $data_provider = $search->search();
        $filter_model = $search->filter();
        // If current user is client then enable filter of provider with its conditions
        if(!Yii::$app->user->can('SiteController.actionList')) {
            if(Yii::$app->user->can('SiteController.actionListOwner')) {
                $users_model = Yii::$app->user->identity->relationUserOwners;
                $user_ids = ArrayHelper::getColumn($users_model, 'user_id'); // Add sub site
                array_unshift($user_ids, Yii::$app->user->id); // Add site of owner
                $data_provider->query->andWhere([Site::tableName() . '.user_id' => $user_ids]);
            }
            elseif(Yii::$app->user->can('SiteController.actionListSiteOwner')) {
                $sites = UserOwnerSite::find()->where(['user_owner_id' => Yii::$app->user->id])->all();
                $sites_ids = ArrayHelper::getColumn($sites, 'site_id');
                $data_provider->query->andWhere([Site::tableName() . '.id' => $sites_ids]);
            }
        }
        return $this->render('list', [
            'data_provider' => $data_provider,
            'filter_model' => $filter_model,
        ]);
    }


    public function actionTenantCreate($id) {
        $model = $this->loadSite($id);
        $form = new FormTenant();
        $form->site_id = $model->id;
        if($form->load(Yii::$app->request->post()) && $form->save()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Tenant have been added.'));
            return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(),
                                                      ['/site/tenants', 'id' => $model->id]));
        }
        return $this->render('/tenant/tenant-form', [
            'form' => $form,
            'site_model' => $model,
        ]);
    }


    public function actionTenants($id, $expired = false) {
        $model = $this->loadSite($id);
        $search = new SearchTenant();
        $data_provider = $search->search();
        $data_provider->query->andWhere([Tenant::tableName() . '.site_id' => $model->id]);
        if($expired) {
            $data_provider->query->andWhere(['<', Tenant::tableName() . '.exit_date', strtotime('midnight')]);
        }
        else {
            $data_provider->query->andWhere([
                                                'or',
                                                Tenant::tableName() . '.exit_date IS NULL',
                                                ['>=', Tenant::tableName() . '.exit_date', strtotime('midnight')],
                                            ]);
        }
        $filter_model = $search->filter();
        $form_tenants = new FormTenants();
        if($form_tenants->load(Yii::$app->request->get()) && $form_tenants->save()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS,
                                         Yii::t('backend.controller', 'Tenants have been updated.'));
            return $this->redirect(['/site/tenants', 'id' => $model->id]);
        }
        return $this->render('tenants', [
            'model' => $model,
            'data_provider' => $data_provider,
            'filter_model' => $filter_model,
            'form_tenants' => $form_tenants,
            'expired' => $expired,
        ]);
    }


    public function actionTenantsUsers($id) {
        $model = $this->loadSite($id);
        $form = new FormTenantsUsers();
        $form->loadAttributes($model);
        $form->save();
        return $this->goBackReferrer();
    }


    public function actionTenantGroupCreate($id) {
        $model = $this->loadSite($id);
        $form = new FormTenantGroup();
        $form->site_id = $model->id;
        if($form->load(Yii::$app->request->post()) && $form->save()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Group have been added.'));
            return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(),
                                                      ['/site/tenant-groups', 'id' => $model->id]));
        }
        return $this->render('tenant-group-create', [
            'form' => $form,
            'model' => $model,
        ]);
    }


    public function actionTenantGroups($id) {
        $model = $this->loadSite($id);
        $search = new SearchTenantGroup();
        $data_provider = $search->search();
        $data_provider->query->andWhere([TenantGroup::tableName() . '.site_id' => $model->id]);
        $filter_model = $search->filter();
        return $this->render('tenant-groups', [
            'model' => $model,
            'data_provider' => $data_provider,
            'filter_model' => $filter_model,
        ]);
    }


    public function actionMeterChannelGroupCreate($id) {
        $model = $this->loadSite($id);
        $form = new FormMeterChannelGroup();
        $form->user_id = $model->user_id;
        $form->site_id = $model->id;
        if($form->load(Yii::$app->request->post()) && $form->save()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Group have been added.'));
            return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(),
                                                      ['/site/meter-channel-groups', 'id' => $model->id]));
        }
        return $this->render('meter-channel-group-create', [
            'form' => $form,
            'model' => $model,
        ]);
    }


    public function actionMeterChannelGroups($id) {
        $model = $this->loadSite($id);
        $search = new SearchMeterChannelGroup();
        $data_provider = $search->search();
        $data_provider->query->andWhere([MeterChannelGroup::tableName() . '.site_id' => $model->id]);
        $filter_model = $search->filter();
        return $this->render('meter-channel-groups', [
            'model' => $model,
            'data_provider' => $data_provider,
            'filter_model' => $filter_model,
        ]);
    }


    public function actionReports($id) {
        $model = $this->loadSite($id);
        $model_site_setting = $model->relationSiteBillingSetting;
        $form = new FormReport();
        $form->level = Report::LEVEL_SITE;
        $form->site_owner_id = $model->user_id;
        $form->site_id = $model->id;
        if($model_site_setting != null && $model_site_setting->billing_day != null) {
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
        if($session->has('issue_from_date')) {
            $form->from_date = $session->get('issue_from_date');
        }
        if($session->has('issue_to_date')) {
            $form->to_date = $session->get('issue_to_date');
        }
        $form->skip_errors = Yii::$app->request->getQueryParam('skip_errors', false);
        if($form->load(Yii::$app->request->get()) && $form->save()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Report have been added.'));
            return $this->redirect(['/site/reports', 'id' => $model->id]);
        }
        $search = new SearchReport();
        $data_provider = $search->search();
        $data_provider->query->andWhere([
                                            Report::tableName() . '.site_id' => $model->id,
                                            Report::tableName() . '.level' => Report::LEVEL_SITE,
                                        ]);
        $filter_model = $search->filter();
        return $this->render('reports', [
            'form' => $form,
            'model' => $model,
            'data_provider' => $data_provider,
            'filter_model' => $filter_model,
        ]);
    }


    public function actionIssueReports() {
        if(($sites_id = Yii::$app->request->getBodyParam('sites')) != null) {
            $reports = [];
            $errors = [];
            $db = Yii::$app->db;
            $billing_day = Yii::$app->formatter->asDate(time(), 'php:j');
            $sites = Site::find()->where(['in', Site::tableName() . '.status', [
                Site::STATUS_ACTIVE,
            ]])->andWhere(['in', Site::tableName() . '.to_issue', [
                Site::TO_ISSUE_MANUAL,
                Site::TO_ISSUE_AUTOMATIC,
            ]])
                         ->joinWith([
                                        'relationSiteBillingSetting',
                                        'relationTenantsToIssued',
                                    ], 'INNER JOIN')
                         ->andWhere([
                                        'and',
                                        Site::tableName() . '.auto_issue_reports IS NOT NULL',
                                        // [SiteBillingSetting::tableName(). '.billing_day' => $billing_day],
                                        ['in', Site::tableName() . '.id', $sites_id],
                                    ])
                         ->groupBy([Site::tableName() . '.id'])
                         ->all();
            if($sites != null) {
                /**
                 * Admins
                 */
                // $users = $db->createCommand('
                // 	SELECT *
                // 	FROM {{user}} t
                // 	WHERE t.role = :role_admin AND t.status = :status
                // ')
                // ->bindValues([
                // 	':status' => User::STATUS_ACTIVE,
                // 	':role_admin' => Role::ROLE_ADMIN,
                // ])
                // ->queryAll();
                /**
                 * Sites to issue
                 */
                foreach($sites as $site) {
                    $site_setting = $site->relationSiteBillingSetting;
                    $auto_issue_reports = $site->getAutoIssueReports();
                    if($auto_issue_reports != null) {
                        $from_date = new \DateTime();
                        $from_date->modify("first day of -1 month midnight");
                        $from_date = $from_date->getTimestamp() + (($site_setting->billing_day - 1) * 86400);
                        $to_date = new \DateTime();
                        $to_date->modify("first day of this month midnight");
                        $to_date = $to_date->getTimestamp() + (($site_setting->billing_day - 2) * 86400);
                        //This part is responsible for handling auto issue of reports
                        foreach($auto_issue_reports as $auto_issue_report) {
                            $report_exists = Report::find()
                                                   ->andWhere([
                                                                  'site_id' => $site->id,
                                                                  'level' => Report::LEVEL_SITE,
                                                                  'type' => $auto_issue_report,
                                                                  'is_automatically_generated' => true,
                                                              ])
                                                   ->andWhere(['from_date' => $from_date, 'to_date' => $to_date])
                                                   ->exists();
                            if($report_exists == null) {
                                $form = new FormReport();
                                $form->level = Report::LEVEL_SITE;
                                $form->site_owner_id = $site->user_id;
                                $form->site_id = $site->id;
                                $form->from_date = Yii::$app->formatter->asDate($from_date);
                                $form->to_date = Yii::$app->formatter->asDate($to_date);
                                $form->type = $auto_issue_report;
                                $form->is_automatically_generated = true;
                                //$form->power_factor = false;
                                switch($auto_issue_report) {
                                    case Report::TYPE_NIS:
                                    case Report::TYPE_KWH:
                                    case Report::TYPE_NIS_KWH:
                                        $form->format_pdf = true;
                                        $form->format_excel = true;
                                        break;
                                    default:
                                        $form->format_pdf = true;
                                        break;
                                }
                                if(!$form->save()) {
                                    $errors[] = implode(' ', $form->getFirstErrors());
                                }
                            }
                        }
                    }
                    Log::add(Log::TYPE_UPDATE, 'Reports for Site "{site_id} have been issued manually',
                             ['site_id' => $site->id]);
                }
                /**
                 * Send mail
                 */
                // if ($reports != null && $users != null) {
                // 	foreach ($users as $user) {
                // 		$mailer = Yii::$app->mailer
                // 		->compose('issue-alert', [
                // 			'user' => $user,
                // 			'reports' => $reports,
                // 		])
                // 		->setFrom([Yii::$app->params['emailFrom'] => Yii::$app->name])
                // 		->setTo([$user['email']])
                // 		->setSubject(Yii::t('backend.mail', 'Automatically issued reports'));
                // 		$mailer->send();
                // 	}
                // }
            }
            /**
             * Mark sites as latest cronjob date
             */
            if($errors != null) {
                Yii::$app->session->setFlash(Alert::ALERT_DANGER, implode("<br>", $errors));
            }
            else {
                Yii::$app->session->setFlash(Alert::ALERT_SUCCESS,
                                             Yii::t('backend.controller', 'Issue engine have been run succesfully.'));
            }
        }
        else {
            Yii::$app->session->setFlash(Alert::ALERT_DANGER, Yii::t('backend.controller',
                                                                     'There are no sites selected for the issue engine.'));
        }
        return $this->goHome();
    }


    public function actionGenerateUsersPassword($id) {
        $model = $this->loadSite($id);
        $tenants = (new Query)->select(['id'])->from(Tenant::tableName())
                              ->andWhere([
                                             'site_id' => $model->id,
                                             'status' => Tenant::STATUS_ACTIVE,
                                         ])->column();
        $users = User::find()->joinWith(['relationUserOwnerSites', 'relationUserOwnerTenants'])
                     ->andWhere([
                                    'and',
                                    [User::tableName() . '.status' => User::STATUS_ACTIVE],
                                    [
                                        'or',
                                        [UserOwnerSite::tableName() . '.site_id' => $model->id],
                                        ['in', UserOwnerTenant::tableName() . '.tenant_id', $tenants],
                                    ],
                                ])
                     ->all();
        if($users != null) {
            foreach($users as $user) {
                $password = Yii::$app->getSecurity()->generateRandomString(10);
                $user->generatePassword($password);
                $user->generateAuthKey();
                if(!$user->save()) {
                    Yii::$app->session->setFlash(Alert::ALERT_DANGER,
                                                 Yii::t('backend.controller', 'Unable to save user: {errors}', [
                                                     'errors' => implode(' ', $user->getFirstErrors()),
                                                 ]));
                    return $this->goBackReferrer();
                }
                $mailer = Yii::$app->mailer
                    ->compose('new-user-credentials', [
                        'user' => $user,
                        'password' => $password,
                    ])
                    ->setFrom([Yii::$app->params['emailFrom'] => Yii::$app->name])
                    ->setTo([$user->email])
                    ->setSubject(Yii::t('backend.controller', 'New Credentials'));
                $mailer->send();
            }
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller',
                                                                      'Password generation and email sending succesfully done.'));
        }
        else {
            Yii::$app->session->setFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'No users found.'));
        }
        return $this->goBackReferrer();
    }


    private function loadSite($id) {
        $model = Site::find()->andWhere([
                                            'id' => $id,
                                        ])->andWhere(['in', 'status', [
            Site::STATUS_INACTIVE,
            Site::STATUS_ACTIVE,
        ]])->one();
        if($model == null) {
            throw new NotFoundHttpException(Yii::t('backend.controller', 'Site not found'));
        }
        return $model;
    }
}
