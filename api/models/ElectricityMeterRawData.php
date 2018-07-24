<?php
namespace api\models;

use Yii;

/**
 * ElectricityMeterRawData is the class for the table "meter_raw_data".
 */
class ElectricityMeterRawData extends \common\models\ElectricityMeterRawData
{
	public function fields()
	{
		return [
			'meter_id',
			'channel_id',
			'date' => function($model){
				return Yii::$app->formatter->asDatetime($model->date);
			},
		];
	}
}
