<?php

namespace backend\controllers;

use backend\models\forms\FormAirMeterRawData;
use backend\models\forms\FormAirMeterRawDataAvg;
use common\models\AirMeterRawData;
use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterSubchannel;
use common\models\ElectricityMeterRawData;
use common\widgets\Alert;
use backend\models\forms\FormElectricityMeterRawData;
use backend\models\forms\FormMeterRawDataAvg;
use backend\models\forms\FormMeterRawDataAvgSingle;
use backend\models\forms\FormMeterRawDataFilter;

/**
 * MeterRawDataController
 */
class MeterRawDataController extends \backend\components\Controller
{
	public $enableCsrfValidation = false;

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
						'roles' => ['MeterRawDataController.actionList'],
					],
				],
			],
			'accessAvg' => [
				'class' => AccessControl::className(),
				'only' => ['avg'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['MeterRawDataController.actionAvg'],
					],
				],
			],
		]);
	}

	public function actionAvg($meter_id, $channel_id, $rule, $from_date, $to_date)
	{

		$model = $this->loadMeterSubchannel($meter_id, $channel_id);
		$form_avg = new FormMeterRawDataAvgSingle();
		$form_avg->loadAttributes($model);
		$form_avg->from_date = $from_date;
		$form_avg->to_date = $to_date;
		$form_avg->rule = $rule;
		if ($form_avg->save()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter raw data have been autocompleted based on rule: {rule}.', [
				'rule' => $form_avg->getAliasRule(),
			]));
		} else {
			Yii::$app->session->setFlash(Alert::ALERT_DANGER, implode("\r\n", $form_avg->getFirstErrors()));			
		}

		return $this->goBackReferrer();
	}

	public function actionList($meter_id, $channel_id)
	{
	    $showAvg = false;
		$model = $this->loadMeterSubchannel($meter_id, $channel_id);

		$meter = $model->getRelationMeter()->one();
		if(!$meter instanceof Meter) {
            throw new NotFoundHttpException('Meter not found');
        }
        if($meter->type == Meter::TYPE_ELECTRICITY) {
            $form = new FormElectricityMeterRawData();
        }
        else {
		    $form = new FormAirMeterRawData();
        }

		$form->loadAttributes($model);
        if($meter->type == Meter::TYPE_ELECTRICITY) {
            $form_avg = new FormMeterRawDataAvg();
        }
        else {
            $form_avg = new FormAirMeterRawDataAvg();
        }
		$form_avg->loadAttributes($model);
		$form_filter = new FormMeterRawDataFilter();

		if ($form_filter->load(Yii::$app->request->get()) && $form_filter->save()) {
//			if ($form_filter->save()) {
				$form->loadFilters($form_filter);
				$form_avg->loadFilters($form_filter);
//			}
		} else {
			$form_filter->loadDefaultAttributes();
			$form->loadFilters($form_filter);
			$form_avg->loadFilters($form_filter);
		}

		if ($form_avg->load(Yii::$app->request->get())) {
			$form_avg->validate();
			$showAvg = true;
        }
		else {

            $form_avg->loadDefaultAttributes();
        }
		
		$form->loadAvgData($form_avg);
		$data_provider = $form->getDataProvider();

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Channel raw data have been updated.'));
            return $this->refresh();
        }

		return $this->render('list', [
			'model' => $model,
			'form' => &$form,
			'form_filter' => $form_filter,
			'form_avg' => $form_avg,
			'data_provider' => $data_provider,
            'showAvg' => $showAvg,
		]);
	}

	public function actionDelete($meter_id, $channel_id)
	{
		$model = $this->loadMeterSubchannel($meter_id, $channel_id);
		ElectricityMeterRawData::deleteAll(['meter_id' => $meter_id, 'channel_id' => $channel_id]);
		ElectricityMeterRawData::deleteCacheValue(["meter_raw_data:{$model->relationMeter->name}_{$model->channel}"]);

		Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter raw data have been deleted.'));
		return $this->goBackReferrer();
	}

	public function actionDeleteRow($id, $type = Meter::TYPE_ELECTRICITY)
	{
		$model = $this->loadMeterRowData($id, $type);
		$meter_name = $model->meter_id;
		$meter_channel_name = $model->channel_id;

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter raw data have been deleted.'));
			ElectricityMeterRawData::deleteCacheValue(["meter_raw_data:{$meter_name}_{$meter_channel_name}"]);
		}

		return $this->goBackReferrer();
	}

	public function actionDeleteAll($meter_id, $channel_id = null)
	{
		if ($channel_id != null) {
			$models = $this->loadMeterSubchannels($meter_id, $channel_id);

			foreach ($models as $model) {
				ElectricityMeterRawData::deleteAll(['meter_id' => $meter_id, 'channel_id' => $model->channel]);
			}
		} else {
			$model = $this->loadMeter($meter_id);
			ElectricityMeterRawData::deleteAll(['meter_id' => $meter_id]);
		}

		ElectricityMeterRawData::deleteCacheValue(["meter_raw_data:$meter_id"]);

		Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Meter raw data have been deleted.'));
		return $this->goBackReferrer();
	}

	private function loadMeter($id)
	{
		$model = Meter::find()->where([
			'name' => $id,
		])->andWhere(['in', 'status', [
			Meter::STATUS_INACTIVE,
			Meter::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Meter not found'));
		}

		return $model;
	}


    /**
     * @param $meter_id
     * @param $channel_id
     * @return array|null|\yii\db\ActiveRecord| MeterSubchannel
     * @throws NotFoundHttpException
     */
    private function loadMeterSubchannel($meter_id, $channel_id)
	{
		$model = MeterSubchannel::find()
		->innerJoin(Meter::tableName(). ' meter', 'meter.id = ' .MeterSubchannel::tableName(). '.meter_id')
		->andWhere([
			'meter.name' => $meter_id,
			MeterSubchannel::tableName(). '.channel' => $channel_id,
		])
		->andWhere(['in', MeterSubchannel::tableName(). '.status', [
			MeterSubchannel::STATUS_INACTIVE,
			MeterSubchannel::STATUS_ACTIVE,
		]])
		->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Meter channel not found'));
		}

		return $model;	
	}

	private function loadMeterSubchannels($meter_id, $channel_id)
	{
		$models = MeterSubchannel::find()
		->innerJoin(Meter::tableName(). ' meter', 'meter.id = ' .MeterSubchannel::tableName(). '.meter_id')
		->innerJoin(MeterChannel::tableName(). ' channel', 'channel.id = ' .MeterSubchannel::tableName(). '.channel_id')
		->andWhere([
			'meter.name' => $meter_id,
			'channel.channel' => $channel_id,
		])
		->andWhere(['in', MeterSubchannel::tableName(). '.status', [
			MeterSubchannel::STATUS_INACTIVE,
			MeterSubchannel::STATUS_ACTIVE,
		]])
		->all();

		if ($models == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Meter channels not found'));
		}

		return $models;	
	}

	private function loadMeterRowData($id, $type)
	{

		$model = $type == Meter::TYPE_AIR ? AirMeterRawData::findOne($id) : ElectricityMeterRawData::findOne($id);

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Meter raw data not found'));
		}
		return $model;		
	}
}
