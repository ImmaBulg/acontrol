<?php

namespace backend\controllers;

use Yii;
use yii\caching\Cache;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

use common\widgets\Alert;
use common\components\actions\ErrorAction;
use backend\models\forms\FormSearch;

/**
 * DashboardController
 */
class DashboardController extends \backend\components\Controller
{
	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		return [
			'error' => [
				'class' => ErrorAction::className(),
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'actions' => ['error'],
						'allow' => true,
					],
					[
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
			'accessIndex' => [
				'class' => AccessControl::className(),
				'only' => ['index'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['DashboardWidget.actionIndex'],
					],
				],
			],
			'accessSearch' => [
				'class' => AccessControl::className(),
				'only' => ['search'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['DashboardController.actionSearch'],
					],
				],
			],
			'accessFlushCache' => [
				'class' => AccessControl::className(),
				'only' => ['flush-cache'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['DashboardController.actionFlushCache'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'flush-cache' => ['post'],
				],
			],
		]);
	}

	public function actionIndex()
	{
		return $this->render('index');
	}

	public function actionSearch($type, $q = null)
	{
		$form = new FormSearch();
		$form->type = $type;
		$form->q = $q;
	
		if (!$form->validate()) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Page not found'));
		}

		$result = $form->search();

		return $this->render('search', [
			'form' => $form,
			'result' => $result,
		]);
	}

	public function actionFlushCache()
	{
		$components = Yii::$app->getComponents();

		foreach ($components as $name => $component) {
			if ($component instanceof Cache) {
				$caches[$name] = get_class($component);
			} elseif (is_array($component) && isset($component['class']) && is_subclass_of($component['class'], Cache::className())) {
				$caches[$name] = $component['class'];
			} elseif (is_string($component) && is_subclass_of($component, Cache::className())) {
				$caches[$name] = $component;
			}
		}

		foreach ($caches as $name => $class) {
			Yii::$app->get($name)->flush();
		}

		Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Cache have been flushed.'));
		return Yii::$app->getResponse()->redirect(Yii::$app->request->referrer);
	}
}