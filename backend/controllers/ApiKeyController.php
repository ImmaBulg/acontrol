<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\ApiKey;
use common\widgets\Alert;
use backend\models\forms\FormApiKey;
use backend\models\searches\SearchApiKey;
use common\models\events\logs\EventLogApiKey;

/**
 * ApiKeyController
 */
class ApiKeyController extends \backend\components\Controller
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
						'roles' => ['ApiKeyController.actionCreate'],
					],
				],
			],
			'accessEdit' => [
				'class' => AccessControl::className(),
				'only' => ['edit'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['ApiKeyController.actionEdit'],
					],
				],
			],
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['ApiKeyController.actionDelete'],
					],
				],
			],
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['ApiKeyController.actionList'],
					],
				],
			],
		]);
	}

	public function actionCreate()
	{
		$form = new FormApiKey();
		$form->api_key = Yii::$app->getSecurity()->generateRandomString();
		$form->status = ApiKey::STATUS_ACTIVE;

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'API key have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/api-key/list']));
		}

		return $this->render('create', [
			'form' => $form,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadApiKey($id);
		$form = new FormApiKey();
		$form->loadAttributes(FormApiKey::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'API key have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/api-key/list']));
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionDelete($id)
	{
		$model = $this->loadApiKey($id);

		$event = new EventLogApiKey();
		$event->model = $model;
		$model->on(EventLogApiKey::EVENT_BEFORE_DELETE, [$event, EventLogApiKey::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'API key have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionList()
	{
		$search = new SearchApiKey();
		$data_provider = $search->search();
		$filter_model = $search->filter();

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
		]);
	}

	private function loadApiKey($id)
	{
		$model = ApiKey::find()->andWhere([
			ApiKey::tableName(). '.id' => $id,
		])->andWhere(['in', ApiKey::tableName(). '.status', [
			ApiKey::STATUS_INACTIVE,
			ApiKey::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'API key not found'));
		}

		return $model;	
	}
}
