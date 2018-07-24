<?php

namespace backend\controllers;

use Yii;
use yii\db\Query;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Tenant;
use common\models\RuleSingleChannel;
use common\models\RuleGroupLoad;
use common\models\RuleFixedLoad;
use common\models\Site;
use backend\models\searches\SearchRuleSingleChannel;
use backend\models\searches\SearchRuleGroupLoad;
use backend\models\searches\SearchRuleFixedLoad;

/**
 * AjaxGridViewController
 */
class AjaxGridViewController extends \backend\components\Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['tenant-rules'],
				'rules' => [
					[
						'allow' => true,
						'matchCallback' => function ($rule, $action) {
							$id_tenant = Yii::$app->request->getQueryParam('id');
							$model_tenant = $this->loadTenant($id_tenant);
							return Yii::$app->user->can('AjaxGridViewController.actionTenantRules') ||
									Yii::$app->user->can('AjaxGridViewController.actionTenantRulesOwner', ['model' => $model_tenant]) ||
									Yii::$app->user->can('AjaxGridViewController.actionTenantRulesSiteOwner', ['model' => $model_tenant]);
						},
					],
				],
			],
		]);
	}

	public function actionTenantRules($id)
	{
		$model = $this->loadTenant($id);

		$search_single = new SearchRuleSingleChannel();
		$data_provider_single = $search_single->search();
		$data_provider_single->query->andWhere([RuleSingleChannel::tableName(). '.tenant_id' => $model->id]);

		$search_group = new SearchRuleGroupLoad();
		$data_provider_group = $search_group->search();
		$data_provider_group->query->andWhere([RuleGroupLoad::tableName(). '.tenant_id' => $model->id]);

		$search_fixed = new SearchRuleFixedLoad();
		$data_provider_fixed = $search_fixed->search();
		$data_provider_fixed->query->andWhere([RuleFixedLoad::tableName(). '.tenant_id' => $model->id]);

		return $this->renderAjax('tenant-rules', [
			'data_provider_single' => $data_provider_single,
			'data_provider_group' => $data_provider_group,
			'data_provider_fixed' => $data_provider_fixed,
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
}
