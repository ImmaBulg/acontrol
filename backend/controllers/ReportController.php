<?php
namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\helpers\Html;
use common\helpers\FileHelper;
use common\models\Report;
use common\models\ReportFile;
use common\models\Site;
use common\models\UserOwnerSite;
use common\widgets\Alert;
use backend\models\forms\FormReport;
use backend\models\forms\FormReports;
use backend\models\searches\SearchReport;
use common\models\events\logs\EventLogReport;

/**
 * ReportController
 */
class ReportController extends \backend\components\Controller
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
						'roles' => ['ReportController.actionCreate', 'ReportController.actionCreateOwner'],
					],
				],
			],
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['ReportController.actionDelete', 'ReportController.actionDeleteOwner'],
					],
				],
			],
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['ReportController.actionList', 'ReportController.actionListOwner'],
					],
				],
			],
			'accessPublish' => [
				'class' => AccessControl::className(),
				'only' => ['publish'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['ReportController.actionPublish'],
					],
				],
			],
			'accessUnpublish' => [
				'class' => AccessControl::className(),
				'only' => ['unpublish'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['ReportController.actionUnpublish'],
					],
				],
			],
			'accessToggleLanguage' => [
				'class' => AccessControl::className(),
				'only' => ['toggle-language'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['ReportController.actionToggleLanguage'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'delete' => ['post'],
					'toggle-language' => ['post'],
					'create-separate-reports' => ['post'],
				],
			],
		]);
	}

	public function actionCreate()
	{
		$form = new FormReport();
		$form->level = Report::LEVEL_SITE;
		$form->skip_errors = Yii::$app->request->getQueryParam('skip_errors', false);

		$session = Yii::$app->session;

		if ($session->has('issue_from_date')) {
			$form->from_date = $session->get('issue_from_date');
		}
		if ($session->has('issue_to_date')) {
			$form->to_date = $session->get('issue_to_date');
		}

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Report have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/report/list']));
		}

		return $this->render('create', [
			'form' => $form,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->loadReport($id);
		$model_files = $model->relationReportFiles;

		if ($model_files != null) {
			foreach ($model_files as $model_file) {
				FileHelper::delete($model_file->file);
			}
		}

		$event = new EventLogReport();
		$event->model = $model;
		$model->on(EventLogReport::EVENT_BEFORE_DELETE, [$event, EventLogReport::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Report have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionToggleLanguage($value)
	{
		Report::setReportLanguage($value);
		return $this->goBackReferrer();
	}

	public function actionList()
	{
		$search = new SearchReport();
		$data_provider = $search->search();
		$form_reports = new FormReports();

		if ($form_reports->load(Yii::$app->request->get()) && $form_reports->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Reports have been deleted.'));
			return $this->redirect(['/report/list', 'Report[level]' => Report::LEVEL_SITE]);
		}

		// If current user is client then enable filter of provider with its conditions
		if(!Yii::$app->user->can('ReportController.actionList')) {
			if(Yii::$app->user->can('ReportController.actionListOwner')) {
				$users_model = Yii::$app->user->identity->relationUserOwners;
				$user_ids = ArrayHelper::getColumn($users_model, 'user_id'); // Add sub site
				array_unshift($user_ids, Yii::$app->user->id); // Add site of owner
				$data_provider->query->andWhere([Site::tableName(). '.user_id' => $user_ids]);
			} elseif(Yii::$app->user->can('ReportController.actionListSiteOwner')) {
				$sites = UserOwnerSite::find()->where(['user_owner_id' => Yii::$app->user->id])->all();
				$sites_ids = ArrayHelper::getColumn($sites, 'site_id');
				$data_provider->query->andWhere([Site::tableName(). '.id' => $sites_ids]);
			}
		}

		$filter_model = $search->filter();

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'form_reports' => $form_reports,
		]);
	}

	public function actionPublish($id)
	{
		$model = $this->loadReport($id);
		$model->is_public = Report::YES;

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		// Generate separate reports
		if ($model->level == Report::LEVEL_SITE && $model->type == Report::TYPE_TENANT_BILLS && ($tenant_reports = $model->relationTenantReports) != null) {
			if ($separate_reports = Report::find()->andWhere(['parent_id' => $model->id])->column()) {
				Yii::$app->db->createCommand()->update(Report::tableName(), ['is_public' => true], ['in', 'id', $separate_reports])->execute();
				Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Report has been published successfully.'));
			} else {
				$separate_reports = [];
				$file_types = $model->getRelationReportFiles()->select(['file_type'])->column();

				foreach ($tenant_reports as $tenant_report) {
					$tenant = $tenant_report->relationTenant;
					$form = new FormReport();
					$form->site_owner_id = $model->site_owner_id;
					$form->site_id = $model->site_id;
					$form->level = Report::LEVEL_TENANT;
					$form->tenants_id[$tenant->id] = $tenant->id;
					$form->type = $model->type;
					$form->from_date = Yii::$app->formatter->asDate($model->from_date);
					$form->to_date = Yii::$app->formatter->asDate($model->to_date);
					$form->is_public = $model->is_public;
					$form->parent_id = $model->id;

					if (in_array(ReportFile::FILE_TYPE_PDF, $file_types)) {
						$form->format_pdf = true;
					}
					if (in_array(ReportFile::FILE_TYPE_EXCEL, $file_types)) {
						$form->format_excel = true;
					}
					if (in_array(ReportFile::FILE_TYPE_DAT, $file_types)) {
						$form->format_dat = true;
					}

					if ($model_report = $form->save()) {
						$separate_reports[] = $model_report->id;
					} else {
						Yii::$app->session->setFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to generate report for tenant {name}: {errors}', [
							'name' => $tenant->name,
							'errors' => implode(' ', $form->getFirstErrors()),
						]));
					}
				}

				Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Report has been published successfully and separate reports have been generated successfully. {btn}', [
					'btn' => "<br/>" . Html::a(Yii::t('backend.controller', 'View the newly generated reports '), ['/report/list', 'Report[id]' => implode(',', $separate_reports)], ['class' => 'btn btn-sm btn-default']),
				]));
			}
		} else {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Report has been published successfully.'));
		}

		return $this->goBackReferrer();
	}

	public function actionUnpublish($id)
	{
		$model = $this->loadReport($id);
		$model->is_public = Report::NO;

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		if ($separate_reports = Report::find()->andWhere(['parent_id' => $model->id])->column()) {
			Yii::$app->db->createCommand()->update(Report::tableName(), ['is_public' => false], ['in', 'id', $separate_reports])->execute();
		}

		Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Report has been unpublished successfully.'));

		return $this->goBackReferrer();
	}

	public function actionCreateSeparateReports($id)
	{
		$model = $this->loadReport($id);
		Yii::$app->db->createCommand()->delete(Report::tableName(), ['parent_id' => $model->id])->execute();

		if ($model->level == Report::LEVEL_SITE && $model->type == Report::TYPE_TENANT_BILLS && ($tenant_reports = $model->relationTenantReports) != null) {
			$separate_reports = [];
			$file_types = $model->getRelationReportFiles()->select(['file_type'])->column();

			foreach ($tenant_reports as $tenant_report) {
				$tenant = $tenant_report->relationTenant;
				$form = new FormReport();
				$form->site_owner_id = $model->site_owner_id;
				$form->site_id = $model->site_id;
				$form->level = Report::LEVEL_TENANT;
				$form->tenants_id[$tenant->id] = $tenant->id;
				$form->type = $model->type;
				$form->from_date = Yii::$app->formatter->asDate($model->from_date);
				$form->to_date = Yii::$app->formatter->asDate($model->to_date);
				$form->is_public = $model->is_public;
				$form->parent_id = $model->id;

				if (in_array(ReportFile::FILE_TYPE_PDF, $file_types)) {
					$form->format_pdf = true;
				}
				if (in_array(ReportFile::FILE_TYPE_EXCEL, $file_types)) {
					$form->format_excel = true;
				}
				if (in_array(ReportFile::FILE_TYPE_DAT, $file_types)) {
					$form->format_dat = true;
				}

				if ($model_report = $form->save()) {
					$separate_reports[] = $model_report->id;
				} else {
					Yii::$app->session->setFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', 'Unable to generate report for tenant {name}: {errors}', [
						'name' => $tenant->name,
						'errors' => implode(' ', $form->getFirstErrors()),
					]));
				}
			}
			
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Reports has been generated successfully. {btn}', [
				'btn' => "<br/>" . Html::a(Yii::t('backend.controller', 'View the newly generated reports '), ['/report/list', 'Report[id]' => implode(',', $separate_reports)], ['class' => 'btn btn-sm btn-default']),
			]));
		}
	
		return $this->goBackReferrer();
	}

	private function loadReport($id)
	{
		$model = Report::find()->andWhere([
			Report::tableName(). '.id' => $id,
		])->andWhere(['in', Report::tableName(). '.status', [
			Report::STATUS_INACTIVE,
			Report::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Report not found'));
		}

		return $model;	
	}
}
