<?php

namespace api\models;

use Yii;


/**
 * AirMeterRawData is the class for the table "air_meter_raw_data".
 */
class AirMeterRawData extends \common\models\AirMeterRawData
{
    public function fields() {
        return [
            'meter_id',
            'channel_id',
            'kilowatt_hour',
            'kilowatt_hour',
            'cubic_meter',
            'cop',
            'delta_t',
            'kilowatt',
            'cubic_meter_hour',
            'incoming_temp',
            'outgoing_temp',
            'datetime',
        ];
    }
}
