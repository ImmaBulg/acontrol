<?php
namespace api\models;

use Yii;

/**
 * Meter is the class for the table "meter".
 */
class Meter extends \common\models\Meter
{
	public function fields()
	{
		return [
			'meter_id' => 'name',
			'site_id',
			'type_id',
			'communication_type',
			'data_usage_method',
			'physical_location',
			'breaker_name',
			'start_date' => function($model){
				if ($model->start_date != null) {
					return Yii::$app->formatter->asDate($model->start_date);
				}
			},
			'status',
		];
	}
}
