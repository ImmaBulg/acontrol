<?php namespace api\controllers;

use api\models\forms\FormAirMeterRawData;
use api\models\searches\SearchAirMeterRawData;
use api\models\searches\SearchMeterRawData;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use api\components\Controller;
use api\components\filters\QueryParamAuth;
use api\models\Meter;
use api\models\MeterChannel;
use common\models\MeterSubchannel;
use api\models\AirMeterRawData;
use api\models\forms\FormMeterRawData;
/**
 * @SWG\Tag(
 *   name="meter-raw-data",
 *   description="Operations about meter raw data"
 * )
 */
class AirMeterRawDataController extends Controller
{
	/**
	 * @inheritdoc
	 */
	protected function verbs()
	{
		return [
			'create' => ['POST'],
			'list' => ['GET'],
			'latest-reading' => ['GET'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'authenticator' => [
				'class' => QueryParamAuth::className(),
			],
		]);
	}

	/**
	 * @SWG\Post(
	 *     path="/air-meter-raw-data",
	 *     summary="Create new meter raw data",
	 *     operationId="MeterRawDataCreate",
	 *     tags={"meter-raw-data"},
	 *     @SWG\Parameter(
	 *         name="api_key",
	 *         in="query",
	 *         description="Api Key",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="data",
	 *         in="formData",
	 *         description="Data",
	 *         required=true,
	 *         type="array",
	 *         items=""
	 *     ),
	 *     @SWG\Response(
	 *         response=200,
	 *         description="Success"
	 *     ),
	 *     @SWG\Response(
	 *         response=400,
	 *         description="Invalid form parameters"
	 *     )
	 * )
	 */
	public function actionCreate()
	{
		$request = Yii::$app->request;
		$form = new FormAirMeterRawData();
		if ($request->bodyParams['data']) {
            $form->data = $request->bodyParams['data'];
        }
		else {
            $form->data = $request->bodyParams;
        }

		if ($models = $form->save()) {
			return $models;
		} else {
			return $form->getErrors();
		}
	}

	/**
	 * @SWG\Get(
	 *     path="/meter-raw-data",
	 *     summary="Get meter raw data",
	 *     operationId="MeterRawDataList",
	 *     tags={"meter-raw-data"},
	 *     @SWG\Parameter(
	 *         name="api_key",
	 *         in="query",
	 *         description="Api Key",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Response(
	 *         response=200,
	 *         description="Success"
	 *     ),
	 *     @SWG\Response(
	 *         response=400,
	 *         description="Invalid form parameters"
	 *     )
	 * )
	 */
	public function actionList()
	{
		$search = new SearchAirMeterRawData();
		$data_provider = $search->search();
		$search->filter();
		return $data_provider->getModels();
	}

	/**
	 * @SWG\Get(
	 *     path="/meter-raw-data/latest-reading",
	 *     summary="Get meter raw data latest reading",
	 *     operationId="MeterRawDataList",
	 *     tags={"meter-raw-data"},
	 *     @SWG\Parameter(
	 *         name="api_key",
	 *         in="query",
	 *         description="Api Key",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         name="meter_id",
	 *         in="query",
	 *         description="Meter ID",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Response(
	 *         response=200,
	 *         description="Success"
	 *     ),
	 *     @SWG\Response(
	 *         response=400,
	 *         description="Invalid form parameters"
	 *     )
	 * )
	 */
	public function actionLatestReading($meter_id)
	{
		$subchannels = (new Query())
		->select('t.channel')
		->from(MeterSubchannel::tableName(). ' t')
		->innerJoin(Meter::tableName(). ' meter', 'meter.id = t.meter_id')
		->andWhere(['meter.name' => $meter_id])->column();

		if ($subchannels != null) {
			$criteria = [];

			foreach ($subchannels as $subchannel) {
				$query = (new Query())
				->from(AirMeterRawData::tableName(). ' t')
				->andWhere([
					't.meter_id' => $meter_id,
					't.channel_id' => $subchannel,
				]);

				$date = $query->max('t.datetime');

				if ($date != null) {
					$criteria[] = ArrayHelper::getValue((new Query())
					->select('t.id')
					->from(AirMeterRawData::tableName(). ' t')
					->andWhere([
						't.meter_id' => $meter_id,
						't.channel_id' => $subchannel,
						't.datetime' => $date,
					])->one(), 'id');
				}
			}

			$models = AirMeterRawData::find()->where(['in', 'id', $criteria])->all();

			if ($models != null) {
				return $models;
			}
		}

		throw new NotFoundHttpException(Yii::t('api.controller', 'Meter raw data not found'));
	}
}
