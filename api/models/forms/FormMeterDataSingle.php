<?php

namespace api\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\Meter;
use common\models\MeterType;
use common\models\Site;
use common\components\i18n\Formatter;

/**
 * FormMeterDataSingle is the class for site meter data single create/edit.
 */
class FormMeterDataSingle extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	public $meter_id;
	public $site_id;
	public $type_id;
	public $breaker_name;
	public $communication_type;
	public $data_usage_method;
	public $physical_location;
	public $start_date;
	public $status;

	public function rules()
	{
		return [
			[['site_id', 'meter_id', 'type_id'], 'required'],
			['meter_id', 'match', 'pattern' => Meter::NAME_VALIDATION_PATTERN],
			[['type_id', 'site_id'], 'integer'],
			['site_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Site', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					Site::STATUS_INACTIVE,
					Site::STATUS_ACTIVE,
				]]);
			}],
			['type_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\MeterType', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					MeterType::STATUS_INACTIVE,
					MeterType::STATUS_ACTIVE,
				]]);
			}],
			['meter_id', 'unique', 'targetClass' => '\common\models\Meter', 'targetAttribute' => 'name', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					Meter::STATUS_INACTIVE,
					Meter::STATUS_ACTIVE,
				]]);
			}],

			[['breaker_name', 'physical_location'], 'filter', 'filter' => 'strip_tags'],
			[['breaker_name', 'physical_location'], 'filter', 'filter' => 'trim'],
			[['breaker_name'], 'string', 'max' => 255],
			[['physical_location'], 'string'],
			['communication_type', 'default', 'value' => Meter::COMMUNICATION_TYPE_PLC],
			['communication_type', 'in', 'range' => array_keys(Meter::getListCommunicationTypes()), 'skipOnEmpty' => true],
			['data_usage_method', 'default', 'value' => Meter::DATA_USAGE_METHOD_IMPORT_PLUS_EXPORT],
			['data_usage_method', 'in', 'range' => array_keys(Meter::getListDataUsageMethods()), 'skipOnEmpty' => true],
			['start_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['status', 'in', 'range' => array_keys(Meter::getListStatuses())],
		];
	}


	public function attributeLabels()
	{
		return [
			'site_id' => Yii::t('api.meter', 'Site ID'),
			'meter_id' => Yii::t('api.meter', 'Meter ID'),
			'type_id' => Yii::t('api.meter', 'Type ID'),
			'breaker_name' => Yii::t('api.meter', 'Breaker name'),
			'communication_type' => Yii::t('api.meter', 'Communication type'),
			'data_usage_method' => Yii::t('api.meter', 'Data usage method'),
			'physical_location' => Yii::t('api.meter', 'Phisical location on site'),
			'start_date' => Yii::t('api.meter', 'Start date'),
			'status' => Yii::t('api.meter', 'Status'),
		];
	}
}
