<?php
namespace api\models;

use Yii;

/**
 * Task is the class for the table "task".
 */
class Task extends \common\models\Task
{
	public function fields()
	{
		return [
			'user_id',
			'site_id',
			'site_contact_id',
			'meter_id' => function($model){
				if ($meter = $model->relationMeter) {
					return $meter->name;
				}
			},
			'channel_id' => function($model){
				if ($channel = $model->relationMeterChannel) {
					return $channel->channel;
				}
			},
			'description',
			'date' => function($model){
				return Yii::$app->formatter->asDateTime($model->date);
			},
			'urgency',
			'status',
			'color',
		];
	}
}
