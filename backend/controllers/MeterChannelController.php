<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelMultiplier;
use common\widgets\Alert;
use backend\models\forms\FormMeterChannel;
use backend\models\forms\FormMeterChannels;
use backend\models\searches\SearchMeterChannel;
use backend\models\searches\SearchMeterChannelMultiplier;
use common\models\events\logs\EventLogMeterChannel;

/**
 * MeterChannelController
 */
class MeterChannelController extends \backend\components\Controller
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
						'roles' => ['MeterChannelController.actionEdit'],
					],
				],
			],
			'accessList' => [
				'class' => AccessControl::className(),
				'only' => ['list'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterChannelController.actionList'],
					],
				],
			],
		]);
	}

	public function actionEdit($id)
	{
		$model = $this->loadMeterChannel($id);
		$form = new FormMeterChannel();
		$form->loadAttributes(FormMeterChannel::SCENARIO_EDIT, $model);

		if ($form->load(Yii::$app->request->post()) && $model_channel = $form->edit()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter channel have been updated.'));
			return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/meter-channel/list', 'id' => $model_channel->meter_id]));
		}

		$search = new SearchMeterChannelMultiplier();
		$data_provider = $search->search();
		$data_provider->query->andWhere([MeterChannelMultiplier::tableName(). '.channel_id' => $model->id]);
		$filter_model = $search->filter();

		return $this->render('edit', [
			'form' => $form,
			'model' => $model,
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
		]);		
	}

	public function actionList($id)
	{
		$model = $this->loadMeter($id);
		$search = new SearchMeterChannel();
		$data_provider = $search->search();
		$data_provider->query->andWhere([MeterChannel::tableName(). '.meter_id' => $model->id]);
		$filter_model = $search->filter();
		$form_channels = new FormMeterChannels();

		if ($form_channels->load(Yii::$app->request->get()) && $form_channels->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter channels have been updated.'));
			return $this->redirect(['/meter-channel/list', 'id' => $model->id]);
		}

		return $this->render('list', [
			'data_provider' => $data_provider,
			'filter_model' => $filter_model,
			'model' => $model,
			'form_channels' => $form_channels,
		]);
	}

	private function loadMeter($id)
	{
		$model = Meter::find()->andWhere([
			Meter::tableName(). '.id' => $id,
		])->andWhere(['in', Meter::tableName(). '.status', [
			Meter::STATUS_INACTIVE,
			Meter::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Meter not found'));
		}

		return $model;
	}

	private function loadMeterChannel($id)
	{
		$model = MeterChannel::find()->andWhere([
			MeterChannel::tableName(). '.id' => $id,
		])->andWhere(['in', MeterChannel::tableName(). '.status', [
			MeterChannel::STATUS_INACTIVE,
			MeterChannel::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Meter channel not found'));
		}

		return $model;	
	}
}
