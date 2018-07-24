<?php namespace api\models\forms;

use Yii;
use common\models\Meter;

/**
 * FormMeterRawDataSingle is the class for meter raw data single create/edit.
 */
class FormAirMeterRawDataSingle extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	public $meter_id;
	public $channel_id;
	public $datetime;
	public $kilowatt_hour;
	public $cubic_meter;
	public $cop;
	public $delta_t;
	public $kilowatt;
	public $cubic_meter_hour;
	public $incoming_temp;
	public $outgoing_temp;


	public function rules()
	{
		return [
			[['meter_id', 'channel_id', 'datetime'], 'required'],
			['meter_id', 'match', 'pattern' => Meter::NAME_VALIDATION_PATTERN],
			[['channel_id'], 'integer'],
			['datetime', 'date', 'format' => "php:Y-m-d H:i:s"],
            [['kilowatt_hour', 'cubic_meter', 'kilowatt', 'cubic_meter_hour', 'incoming_temp', 'outgoing_temp', 'cop', 'delta_t'], 'number'],
		];
	}



	public function attributeLabels()
	{
		return [
			'meter_id' => Yii::t('api.meter', 'Meter ID'),
			'channel_id' => Yii::t('api.meter', 'Channel ID'),
			'datetime' => Yii::t('api.meter', 'Reading date'),
		];
	}
}
