<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Task;
use common\models\TaskComment;
use common\widgets\Alert;
use common\components\rbac\Role;
use backend\models\forms\FormTask;
use backend\models\forms\FormTasks;
use backend\models\forms\FormTaskComment;
use backend\models\searches\SearchTask;
use backend\models\searches\SearchTaskComment;
use common\models\events\logs\EventLogTask;

/**
 * TaskController
 */
class TaskController extends \backend\components\Controller
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
						'roles' => ['TaskController.actionCreate'],
					],
				],
			],
			'accessEdit' => [
				'class' => AccessControl::className(),
				'only' => ['edit'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['TaskController.actionEdit'],
					],
				],
			],
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['TaskController.actionDelete'],
					],
				],
			],
			'accessView' => [
				'class' => AccessControl::className(),
				'only' => ['view'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['TaskController.actionView'],
					],
				],
			],
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['TaskController.actionList'],
					],
				],
			],
			'accessToggleAssignee' => [
				'class' => AccessControl::className(),
				'only' => ['toggle-assignee'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['TaskController.actionToggleAssignee'],
					],
				],
			],
		]);
	}

	public function actionCreate()
	{
		$form = new FormTask();
		$form->scenario = FormTask::SCENARIO_CREATE;
		$form->role = Task::getAssigneeRole();
		$form->user_id = Task::getAssigneeId();
		$form->date = time();
		$form->time = '00:00';

		if ($form->load(Yii::$app->request->post()) && $model_task = $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Task have been added.'));
			return $this->redirect(['/task/view', 'id' => $model_task->id]);
		}

		return $this->render('create', [
			'form' => $form,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadTask($id);
		$form = new FormTask();
		$form->scenario = FormTask::SCENARIO_EDIT;
		$form->loadAttributes(FormTask::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post())) {
			$params = ArrayHelper::getValue(Yii::$app->request->post(), $form->formName());
			$form->site_contact_id = ArrayHelper::getValue($params, 'site_contact_id');
			$form->meter_id = ArrayHelper::getValue($params, 'meter_id');
			$form->channel_id = ArrayHelper::getValue($params, 'channel_id');

			if ($model_task = $form->edit()) {
				Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Task have been updated.'));

				if (Yii::$app->request->isAjax) {
					return $this->goBackReferrer();
				} else {
					return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/task/list']));
				}
			}
		}

		return $this->renderDependence('edit', [
			'model' => $model,
			'form' => $form,
		]);	
	}

	public function actionView($id)
	{
		$model = $this->loadTask($id);
		$form = new FormTaskComment();
		$form->loadAttributes(FormTaskComment::SCENARIO_CREATE, $model);

		if ($form->load(Yii::$app->request->post()) && $model_comment = $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Comment have been updated.'));
		}

		$search = new SearchTaskComment();
		$data_provider = $search->search();
		$data_provider->query->andWhere([TaskComment::tableName(). '.task_id' => $model->id]);

		return $this->render('view', [
			'model' => $model,
			'form' => $form,
			'data_provider' => $data_provider,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->loadTask($id);

		$event = new EventLogTask();
		$event->model = $model;
		$model->on(EventLogTask::EVENT_BEFORE_DELETE, [$event, EventLogTask::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Task have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionList()
	{
		$session = Yii::$app->session;
		$search = new SearchTask();
		$data_provider = $search->search();

		$form_tasks = new FormTasks();

		if ($form_tasks->load(Yii::$app->request->get()) && $form_tasks->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Alerts/Helpdesk have been deleted.'));
			return $this->redirect(['/task/list']);
		}

		$from_date = Yii::$app->request->getQueryParam('from_date', Yii::$app->formatter->asDate(strtotime('-30 days')));
		$to_date = Yii::$app->request->getQueryParam('to_date', Yii::$app->formatter->asDate(time()));

		if ($from_date != null) {
			$data_provider->query->andWhere(['>=', Task::tableName(). '.date', Yii::$app->formatter->modifyTimestamp($from_date, 'midnight')]);
		}
		if ($to_date != null) {
			$data_provider->query->andWhere(['<=', Task::tableName(). '.date', Yii::$app->formatter->modifyTimestamp($to_date, 'tomorrow') - 1]);
		}

		$filter_model = $search->filter();

		return $this->render('list', [
			'form_tasks' => $form_tasks,
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'from_date' => $from_date,
			'to_date' => $to_date,
		]);
	}

	public function actionToggleAssignee($value)
	{
		Task::setAssigneeId($value);
		return $this->goBackReferrer();
	}

	private function loadTask($id)
	{
		$model = Task::find()->andWhere([
			Task::tableName(). '.id' => $id,
		])->andWhere(['in', Task::tableName(). '.status', [
			Task::STATUS_INACTIVE,
			Task::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Task not found'));
		}

		return $model;	
	}
}
