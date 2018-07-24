<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Meter;
use common\models\ElectricityMeterRawData;
use common\widgets\Alert;
use backend\models\forms\FormMeter;
use backend\models\forms\FormMeters;
use backend\models\forms\FormMeterImportData;
use backend\models\searches\SearchMeter;
use common\models\events\logs\EventLogMeter;
/**
 * MeterController
 */
class MeterController extends \backend\components\Controller
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
						'roles' => ['MeterController.actionCreate'],
					],
				],
			],
			'accessEdit' => [
				'class' => AccessControl::className(),
				'only' => ['edit', 'import-data'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterController.actionEdit'],
					],
				],
			],
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterController.actionDelete'],
					],
				],
			],
			'accessView' => [
				'class' => AccessControl::className(),
				'only' => ['view'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterController.actionView'],
					],
				],
			],
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterController.actionList'],
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
		$form = new FormMeter();
		$model = new Meter();
		$form->scenario = FormMeter::SCENARIO_CREATE;
		$form->start_date = strtotime('midnight');

		if ($form->load(Yii::$app->request->post()) && $model = $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter have been added.'));
			return $this->redirect(['/meter-channel/list', 'id' => $model->id]);
		}

		return $this->render('meter-form', [
			'form' => $form,
            'model' => $model
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadMeter($id);
		$form = new FormMeter();
		$form->scenario = FormMeter::SCENARIO_EDIT;
		$form->loadAttributes(FormMeter::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/meter/list']));
		}

		return $this->render('meter-form', [
			'form' => $form,
            'model' => $model
		]);		
	}

	public function actionDelete($id)
	{
		$model = $this->loadMeter($id);
		$meter_id = $model->name;
		$event = new EventLogMeter();
		$event->model = $model;
		$model->on(EventLogMeter::EVENT_BEFORE_DELETE, [$event, EventLogMeter::METHOD_DELETE]);

		if ($model->delete()) {
			ElectricityMeterRawData::deleteAll(['meter_id' => $meter_id]);
			ElectricityMeterRawData::deleteCacheValue(["meter_raw_data:$meter_id"]);
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionView($id)
	{
		$model = $this->loadMeter($id);

		return $this->render('view', [
			'model' => $model,
		]);
	}

	public function actionList()
	{
		$search = new SearchMeter();
		$data_provider = $search->search();
		$filter_model = $search->filter();

		$form_meters = new FormMeters();

		if ($form_meters->load(Yii::$app->request->get()) && $form_meters->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meters have been updated.'));
			return $this->redirect(['/meter/list']);
		}

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'form_meters' => $form_meters,
		]);
	}

	public function actionImportData($id)
	{
		$model = $this->loadMeter($id);
		$form = new FormMeterImportData();
		$form->loadAttributes($model);

		if ($form->load(Yii::$app->request->post())) {
			$rows = $form->save();
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter data have been imported (Executed rows {n}).', ['n' => $rows]));
			return $this->refresh();
		}

		return $this->render('import-data', [
			'model' => $model,
			'form' => $form,
		]);
	}

	private function loadMeter($id)
	{
		$model = Meter::find()->where([
			'id' => $id,
		])->andWhere(['in', 'status', [
			Meter::STATUS_INACTIVE,
			Meter::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Meter not found'));
		}

		return $model;
	}
}
