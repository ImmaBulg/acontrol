<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\Tenant;
use common\models\RuleSingleChannel;
use common\widgets\Alert;
use backend\models\forms\FormRuleSingleChannel;
use backend\models\searches\SearchRuleSingleChannel;
use common\models\events\logs\EventLogRuleSingleChannel;

/**
 * RuleSingleChannelController
 */
class RuleSingleChannelController extends \backend\components\Controller
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
							$id_tenant = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenant($id_tenant);
							return Yii::$app->user->can('RuleSingleChannelController.actionCreate') ||
									Yii::$app->user->can('RuleSingleChannelController.actionCreateOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('RuleSingleChannelController.actionCreateSiteOwner', ['model' => $model_tenant]);
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
							$id_rule = Yii::$app->request->getQueryParam('id');
							$model_rule = $this->loadRule($id_rule);
							$model_tenant = $model_rule->relationTenant;
							return Yii::$app->user->can('RuleSingleChannelController.actionEdit') ||
									Yii::$app->user->can('RuleSingleChannelController.actionEditOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('RuleSingleChannelController.actionEditSiteOwner', ['model' => $model_tenant]);
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
							$id_rule = Yii::$app->request->getQueryParam('id');
							$model_rule = $this->loadRule($id_rule);
							$model_tenant = $model_rule->relationTenant;
							return Yii::$app->user->can('RuleSingleChannelController.actionDelete') ||
									Yii::$app->user->can('RuleSingleChannelController.actionDeleteOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('RuleSingleChannelController.actionDeleteSiteOwner', ['model' => $model_tenant]);
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
							$id_tenant = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenant($id_tenant);
							return Yii::$app->user->can('RuleSingleChannelController.actionList') ||
									Yii::$app->user->can('RuleSingleChannelController.actionListOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('RuleSingleChannelController.actionListTenantOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('RuleSingleChannelController.actionListSiteOwner', ['model' => $model_tenant]);
						},
					],
				],
			],
		]);
	}

	public function actionCreate($id)
	{
		$model = $this->loadTenant($id);
		$form = new FormRuleSingleChannel();
		$form->scenario = FormRuleSingleChannel::SCENARIO_CREATE;
		$form->loadAttributes(FormRuleSingleChannel::SCENARIO_CREATE, $model);

		if ($form->load(Yii::$app->request->post()) && $model_rule = $form->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Rule have been added.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/rule-single-channel/list', 'id' => $model_rule->tenant_id]));
		}

		return $this->render('create', [
			'form' => $form,
			'model' => $model,
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadRule($id);
		$form = new FormRuleSingleChannel();
		$form->scenario = FormRuleSingleChannel::SCENARIO_EDIT;
		$form->loadAttributes(FormRuleSingleChannel::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $model_rule = $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Rule have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/rule-single-channel/list', 'id' => $model_rule->tenant_id]));
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionDelete($id)
	{
		$model = $this->loadRule($id);

		$event = new EventLogRuleSingleChannel();
		$event->model = $model;
		$model->on(EventLogRuleSingleChannel::EVENT_BEFORE_DELETE, [$event, EventLogRuleSingleChannel::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Rule have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	public function actionList($id)
	{
		$model = $this->loadTenant($id);
		$search = new SearchRuleSingleChannel();
		$data_provider = $search->search();
		$data_provider->query->andWhere([RuleSingleChannel::tableName(). '.tenant_id' => $model->id]);
		$filter_model = $search->filter();

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'model' => $model,
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

	private function loadRule($id)
	{
		$model = RuleSingleChannel::find()->andWhere([
			RuleSingleChannel::tableName(). '.id' => $id,
		])->andWhere(['in', RuleSingleChannel::tableName(). '.status', [
			RuleSingleChannel::STATUS_INACTIVE,
			RuleSingleChannel::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Rule not found'));
		}

		return $model;	
	}
}
