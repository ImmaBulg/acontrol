<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\widgets\Alert;
use common\components\rbac\Role;
use backend\models\forms\FormUser;
use backend\models\forms\FormUsers;
use backend\models\forms\FormUserPasswordChange;
use backend\models\searches\SearchUser;
use common\models\events\logs\EventLogUser;

/**
 * UserController
 */
class UserController extends \backend\components\Controller
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
						'roles' => ['UserController.actionCreate'],
					],
				],
			],
			'accessEdit' => [
				'class' => AccessControl::className(),
				'only' => ['edit'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['UserController.actionEdit'],
					],
				],
			],
			'accessPasswordChange' => [
				'class' => AccessControl::className(),
				'only' => ['password-change'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['UserController.actionEdit'],
					],
				],
			],
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['UserController.actionDelete'],
					],
				],
			],
			'accessView' => [
				'class' => AccessControl::className(),
				'only' => ['view'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['UserController.actionView'],
					],
				],
			],
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['UserController.actionList'],
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
		$form = new FormUser();
		$form->scenario = FormUser::SCENARIO_CREATE;

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'User have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/user/list']));
		}

		return $this->render('create', [
			'form' => $form,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadUser($id);
		$form = new FormUser();
		$form->scenario = FormUser::SCENARIO_EDIT;
		$form->loadAttributes(FormUser::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'User have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/user/list']));
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionPasswordChange($id)
	{
		$model = $this->loadUser($id);
		$form = new FormUserPasswordChange();
		$form->loadAttributes($model);

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Password have been updated.'));
			return $this->redirect(['/user/list']);
		}

		return $this->render('password-change', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionView($id)
	{
		$model = $this->loadUser($id);

		return $this->render('view', [
			'model' => $model,
		]);
	}

	public function actionDelete($id)
	{
		$user = Yii::$app->user->identity;
		$model = $this->loadUser($id);

		if ($user->id != $model->id) {
			$event = new EventLogUser();
			$event->model = $model;
			$model->on(EventLogUser::EVENT_BEFORE_DELETE, [$event, EventLogUser::METHOD_DELETE]);

			if (!$model->delete()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'User have been deleted.'));
		}

		return $this->goBackReferrer();
	}

	public function actionList()
	{
		$search = new SearchUser();
		$data_provider = $search->search();
		$filter_model = $search->filter();

		$form_users = new FormUsers();

		if ($form_users->load(Yii::$app->request->get()) && $form_users->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Users have been updated.'));
			return $this->redirect(['/user/list']);
		}

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'form_users' => $form_users,
		]);
	}

	private function loadUser($id)
	{
		$model = User::find()->where([
			'id' => $id,
		])->andWhere(['in', 'status', [
			User::STATUS_INACTIVE,
			User::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'User not found'));
		}

		return $model;
	}
}
