<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\models\UserContact;
use common\widgets\Alert;
use common\components\rbac\Role;
use backend\models\forms\FormUserContact;
use backend\models\searches\SearchUserContact;
use common\models\events\logs\EventLogUserContact;

/**
 * ClientContactController
 */
class ClientContactController extends \backend\components\Controller
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
                        'matchCallback' => function ($rule, $action) {
							$id_client = Yii::$app->request->getQueryParam('id');
							$model_client = $this->loadClient($id_client);
							return Yii::$app->user->can('ClientContactController.actionCreate') ||
									Yii::$app->user->can('ClientContactController.actionCreateOwner', ['model' => $model_client]);
						},
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
							$id_client_contact = Yii::$app->request->getQueryParam('id');
							$model_client = $this->loadClientByClientContact($id_client_contact);
							return Yii::$app->user->can('ClientContactController.actionEdit') ||
									Yii::$app->user->can('ClientContactController.actionEditOwner', ['model' => $model_client]);
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
							$id_client_contact = Yii::$app->request->getQueryParam('id');
							$model_client = $this->loadClientByClientContact($id_client_contact);
							return Yii::$app->user->can('ClientContactController.actionDelete') ||
									Yii::$app->user->can('ClientContactController.actionDeleteOwner', ['model' => $model_client]);
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
							$id_client_contact = Yii::$app->request->getQueryParam('id');
							$model_client = $this->loadClientByClientContact($id_client_contact);
							return Yii::$app->user->can('ClientContactController.actionView') ||
									Yii::$app->user->can('ClientContactController.actionViewOwner', ['model' => $model_client]);
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
                        'matchCallback' => function ($rule, $action) {
							$id_client = Yii::$app->request->getQueryParam('id');
							$model_client = $this->loadClient($id_client);
							return Yii::$app->user->can('ClientContactController.actionList') ||
									Yii::$app->user->can('ClientContactController.actionListOwner', ['model' => $model_client]);
						},
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

	public function actionCreate($id)
	{
		$model = $this->loadClient($id);
		$form = new FormUserContact();
		$form->loadAttributes(FormUserContact::SCENARIO_CREATE, $model);

		if ($form->load(Yii::$app->request->post()) && $model_contact = $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Contact have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/client-contact/list', 'id' => $model_contact->user_id]));
		}

		return $this->render('create', [
			'form' => $form,
			'model' => $model,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadClientContact($id);
		$form = new FormUserContact();
		$form->loadAttributes(FormUserContact::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $model_contact = $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Contact have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/client-contact/list', 'id' => $model_contact->user_id]));
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionView($id)
	{
		$model = $this->loadClientContact($id);

		return $this->render('view', [
			'model' => $model,
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->loadClientContact($id);

		$event = new EventLogUserContact();
		$event->model = $model;
		$model->on(EventLogUserContact::EVENT_BEFORE_DELETE, [$event, EventLogUserContact::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Contact have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionList($id)
	{
		$model = $this->loadClient($id);
		$search = new SearchUserContact();
		$data_provider = $search->search();
		$data_provider->query->andWhere([UserContact::tableName(). '.user_id' => $model->id]);
		$filter_model = $search->filter();

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'model' => $model,
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

	private function loadClientContact($id)
	{
		$model = UserContact::find()->andWhere([
			UserContact::tableName(). '.id' => $id,
		])->andWhere(['in', UserContact::tableName(). '.status', [
			UserContact::STATUS_INACTIVE,
			UserContact::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Contact not found'));
		}

		return $model;
	}
    
    private function loadClientByClientContact($id) 
	{
		$model_contact = $this->loadClientContact($id);
        $id_client = $model_contact->user_id;
        $model = $this->loadClient($id_client);
		return $model;
	}
}
