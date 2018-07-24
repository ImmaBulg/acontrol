<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\MeterChannelMultiplier;
use common\widgets\Alert;
use backend\models\forms\FormMeterChannelMultiplier;

/**
 * MeterChannelMultiplierController
 */
class MeterChannelMultiplierController extends \backend\components\Controller
{
	public $enableCsrfValidation = false;
	
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'accessEdit' => [
				'class' => AccessControl::className(),
				'only' => ['edit'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterChannelMultiplierController.actionEdit'],
					],
				],
			],
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterChannelMultiplierController.actionDelete'],
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

	public function actionEdit($id)
	{
		$model = $this->loadMeterChannelMultiplier($id);
		$form = new FormMeterChannelMultiplier();
		$form->loadAttributes($model);

		if ($form->load(Yii::$app->request->post()) && $model_multiplier = $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter channel multiplier have been updated.'));
			return $this->redirect(['/meter-channel/edit', 'id' => $model_multiplier->channel_id]);
		}

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
		]);		
	}

	public function actionDelete($id)
	{
		$model = $this->loadMeterChannelMultiplier($id);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter channel multiplier have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	private function loadMeterChannelMultiplier($id)
	{
		$model = MeterChannelMultiplier::find()->andWhere([
			MeterChannelMultiplier::tableName(). '.id' => $id,
		])->andWhere(['in', MeterChannelMultiplier::tableName(). '.status', [
			MeterChannelMultiplier::STATUS_INACTIVE,
			MeterChannelMultiplier::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Meter channel multiplier not found'));
		}

		return $model;	
	}
}
