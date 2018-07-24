<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Log;
use common\widgets\Alert;
use backend\models\searches\SearchLog;

/**
 * LogController
 */
class LogController extends \backend\components\Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['LogController.actionList'],
					],
				],
			],
		]);
	}
	
	public function actionList()
	{
		$search = new SearchLog();
		$data_provider = $search->search();
		$filter_model = $search->filter();

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
		]);
	}

	private function loadLog($id)
	{
		$model = Log::find()->where([
			'id' => $id,
		])->andWhere(['in', 'status', [
			Log::STATUS_INACTIVE,
			Log::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Log not found'));
		}

		return $model;
	}
}
