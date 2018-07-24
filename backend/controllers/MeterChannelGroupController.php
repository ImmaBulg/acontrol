<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\MeterChannelGroup;
use common\widgets\Alert;
use backend\models\forms\FormMeterChannelGroup;
use backend\models\searches\SearchMeterChannelGroup;
use common\models\events\logs\EventLogMeterChannelGroup;

/**
 * MeterChannelGroupController
 */
class MeterChannelGroupController extends \backend\components\Controller
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
						'roles' => ['MeterChannelGroupController.actionCreate'],
					],
				],
			],
			'accessEdit' => [
				'class' => AccessControl::className(),
				'only' => ['edit'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterChannelGroupController.actionEdit'],
					],
				],
			],
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterChannelGroupController.actionDelete'],
					],
				],
			],
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterChannelGroupController.actionList'],
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
		$form = new FormMeterChannelGroup();

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Group have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/meter-channel-group/list']));
		}
		return $this->render('create', [
			'form' => $form,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadMeterChannelGroup($id);
		$form = new FormMeterChannelGroup();
		$form->loadAttributes(FormMeterChannelGroup::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $model_group = $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Group have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/site/meter-channel-groups', 'id' => $model_group->site_id]));
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionDelete($id)
	{
		$model = $this->loadMeterChannelGroup($id);

		$event = new EventLogMeterChannelGroup();
		$event->model = $model;
		$model->on(EventLogMeterChannelGroup::EVENT_BEFORE_DELETE, [$event, EventLogMeterChannelGroup::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Group have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionList()
	{
		$search = new SearchMeterChannelGroup();
		$data_provider = $search->search();
		$filter_model = $search->filter();

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
		]);
	}

	private function loadMeterChannelGroup($id)
	{
		$model = MeterChannelGroup::find()->andWhere([
			MeterChannelGroup::tableName(). '.id' => $id,
		])->andWhere(['in', MeterChannelGroup::tableName(). '.status', [
			MeterChannelGroup::STATUS_INACTIVE,
			MeterChannelGroup::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Group not found'));
		}

		return $model;
	}
}
