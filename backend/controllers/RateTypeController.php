<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\RateType;
use common\widgets\Alert;
use backend\models\forms\FormRateType;
use backend\models\searches\SearchRateType;
use common\models\events\logs\EventLogRateType;

/**
 * RateTypeController
 */
class RateTypeController extends \backend\components\Controller
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
						'roles' => ['RateTypeController.actionCreate'],
					],
				],
			],
			'accessEdit' => [
				'class' => AccessControl::className(),
				'only' => ['edit'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['RateTypeController.actionEdit'],
					],
				],
			],
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['RateTypeController.actionDelete'],
					],
				],
			],
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['RateTypeController.actionList'],
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
		$form = new FormRateType();
		$form->scenario = FormRateType::SCENARIO_CREATE;

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Rate type have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/rate-type/list']));
		}

		return $this->render('create', [
			'form' => $form,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadRateType($id);
		$form = new FormRateType();
		$form->scenario = FormRateType::SCENARIO_EDIT;
		$form->loadAttributes(FormRateType::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Rate have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/rate-type/list']));
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionDelete($id)
	{
		$model = $this->loadRateType($id);
		$count_sites = $model->getRelationSiteBillingSettings()->count();

		if (!$count_sites) {
			$event = new EventLogRateType();
			$event->model = $model;
			$model->on(EventLogRateType::EVENT_BEFORE_DELETE, [$event, EventLogRateType::METHOD_DELETE]);

			if (!$model->delete()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));				
			}

			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Rate type have been deleted.'));
		} else {
			Yii::$app->session->setFlash(Alert::ALERT_DANGER, Yii::t('backend.controller', "There {n,plural,=0{are no sites} =1{is one site} other{are # sites}} that used this rate type.", [
				'n' => $count_sites,
			]));
		}

		return $this->goBackReferrer();
	}

	public function actionList()
	{
		$search = new SearchRateType();
		$data_provider = $search->search();
		$filter_model = $search->filter();

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
		]);
	}

	private function loadRateType($id)
	{
		$model = RateType::find()->where([
			'id' => $id,
		])->andWhere(['in', 'status', [
			RateType::STATUS_INACTIVE,
			RateType::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Rate type not found'));
		}

		return $model;
	}
}
