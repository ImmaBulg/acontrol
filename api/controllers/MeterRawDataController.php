<?php
namespace api\controllers;

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
use api\models\ElectricityMeterRawData;
use api\models\forms\FormMeterRawData;
use api\models\searches\SearchMeterRawData;

/**
 * @SWG\Tag(
 *   name="meter-raw-data",
 *   description="Operations about meter raw data"
 * )
 */
class MeterRawDataController extends Controller
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
	 *     path="/meter-raw-data",
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
		$form = new FormMeterRawData();
		$form->attributes = $request->bodyParams;
		
		if ($models = $form->save()) {
			return $models;
		} else {
			throw new BadRequestHttpException(implode(' ', $form->getFirstErrors()));
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
		$search = new SearchMeterRawData();
		$data_provider = $search->search();
		$search->filter();
		return $data_provider->prepareList();
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

		$data_usage_method = (new Query())
		->select('t.data_usage_method')
		->from(Meter::tableName(). ' t')
		->andWhere(['t.name' => $meter_id])->scalar();

		if ($subchannels != null) {
			$criteria = [];

			foreach ($subchannels as $subchannel) {
				$query = (new Query())
				->from(ElectricityMeterRawData::tableName(). ' t')
				->andWhere([
					't.meter_id' => $meter_id,
					't.channel_id' => $subchannel,
				]);

				switch ($data_usage_method) {
					case Meter::DATA_USAGE_METHOD_EXPORT:
						$query->andWhere([
							'and',
							't.export_shefel IS NOT NULL',
							't.export_geva IS NOT NULL',
							't.export_pisga IS NOT NULL',
						]);
						break;
					
					case Meter::DATA_USAGE_METHOD_IMPORT_PLUS_EXPORT:
					case Meter::DATA_USAGE_METHOD_IMPORT_MINUS_EXPORT:
						$query->andWhere([
							'and',
							[
								'or',
								't.shefel IS NOT NULL',
								't.reading_shefel IS NOT NULL',
							],
							[
								'or',
								't.geva IS NOT NULL',
								't.reading_geva IS NOT NULL',
							],
							[
								'or',
								't.pisga IS NOT NULL',
								't.reading_pisga IS NOT NULL',
							],
						]);
						$query->andWhere([
							'and',
							't.export_shefel IS NOT NULL',
							't.export_geva IS NOT NULL',
							't.export_pisga IS NOT NULL',
						]);
						break;

					case Meter::DATA_USAGE_METHOD_IMPORT:
					default:
						$query->andWhere([
							'and',
							[
								'or',
								't.shefel IS NOT NULL',
								't.reading_shefel IS NOT NULL',
							],
							[
								'or',
								't.geva IS NOT NULL',
								't.reading_geva IS NOT NULL',
							],
							[
								'or',
								't.pisga IS NOT NULL',
								't.reading_pisga IS NOT NULL',
							],
						]);
						break;
				}

				$date = $query->max('t.date');

				if ($date != null) {
					$criteria[] = ArrayHelper::getValue((new Query())
					->select('t.id')
					->from(ElectricityMeterRawData::tableName(). ' t')
					->andWhere([
						't.meter_id' => $meter_id,
						't.channel_id' => $subchannel,
						't.date' => $date,
					])->one(), 'id');
				}
			}

			$models = ElectricityMeterRawData::find()->where(['in', 'id', $criteria])->all();

			if ($models != null) {
				return $models;
			}
		}

		throw new NotFoundHttpException(Yii::t('api.controller', 'Meter raw data not found'));
	}
}
