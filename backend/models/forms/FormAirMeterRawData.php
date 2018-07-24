<?php

namespace backend\models\forms;

use common\models\AirMeterRawData;
use api\models\forms\FormMeterRawDataSingle;
use common\components\i18n\Formatter;
use common\models\ElectricityMeterRawData;
use DateTime;
use Exception;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;

/**
 * FormElectricityMeterRawData is the class for meter raw data create/edit.
 */
class FormAirMeterRawData extends FormMeterRawData
{



    public function rules() {
        return [
            [['readings'], 'validateReadings']
        ];
    }

    public function validateReadings($attribute) {
        foreach ($this->readings as $date => $reading) {
            foreach ($reading as $key => $value) {
                if (!empty($value) && $key != 'datetime') {
                    if (!is_numeric($value)) {
                        $this->addError($attribute . '[' . $date . '][' . $key . ']',
                            Yii::t('backend.meter', '{attribute} for {date} must be a number.', [
                                'attribute' => $this->getAttributeLabel($attribute),
                                'date' => $date,
                            ]));
                    } elseif ($value < 0) {
                        $this->addError($attribute . '[' . $date . '][' . $key . ']',
                            Yii::t('backend.meter',
                                '{attribute} for {date} must be no less than 0.',
                                [
                                    'attribute' => $this->getAttributeLabel($attribute),
                                    'date' => $date,
                                ]));

                    }
                }
            }
        }
    }

    public function attributeLabels() {
        return [
            'date' => Yii::t('backend.meter', 'Reading date'),
            'kilowatt_hour' => Yii::t('backend.meter', 'Shefel'),
            'cubic_meter' => Yii::t('backend.meter', 'AVG'),
            'kilowatt' => Yii::t('backend.meter', 'Max shefel'),
            'cubic_meter_hour' => Yii::t('backend.meter', 'Export shefel'),
            'incoming_temp' => Yii::t('backend.meter', 'Geva'),
            'outgoing_temp' => Yii::t('backend.meter', 'AVG')
        ];
    }


    public function getDataProvider() {
        if(!isset($this->_data_provider) || $this->_data_provider == null) {
            $this->_data_provider = $this->generateDataProvider();
        }
        return $this->_data_provider;
    }

    protected function generateDataProvider() {
        $data = [];
        $from_date = $this->getDateFromPeriod()->getTimestamp();
        $to_date = $this->getDateToPeriod()->getTimestamp();
        $rows = $this->getQueryRows();
        $sql_date_format = Formatter::SQL_DATE_FORMAT;
        $avg_readings = [];
        $avg_data = $this->_avg_data;
        $direction = $this->_direction;
        $apply_avg = Yii::$app->request->getQueryParam('avg');
        if($direction) {
            $this->calculateData($from_date, $rows, $apply_avg, $avg_data, $avg_readings, $data, $sql_date_format, $direction, true);
            for($i = $from_date + FormMeterRawDataSingle::_1_HOUR; $i <= $to_date; $i = $i + FormMeterRawDataSingle::_1_HOUR) {
                $this->calculateData($i, $rows, $apply_avg, $avg_data, $avg_readings, $data, $sql_date_format, $direction);
            }
        }
        else {
            $this->calculateData($to_date, $rows, $apply_avg, $avg_data, $avg_readings, $data, $sql_date_format, $direction, true);
            for($i = $to_date - FormMeterRawDataSingle::_1_HOUR; $i >= $from_date; $i = $i - FormMeterRawDataSingle::_1_HOUR) {
                $this->calculateData($i, $rows, $apply_avg, $avg_data, $avg_readings, $data, $sql_date_format, $direction);
            }
            uasort($data, function ($a, $b) {
                return $a['timestamp'] - $b['timestamp'];
            });
        }

        return new ArrayDataProvider([
                                         'allModels' => $data,
                                         'pagination' => [
                                             'pageParam' => self::PAGE_PARAM,
                                             'defaultPageSize' => self::PAGE_SIZE,
                                             'pageSizeLimit' => [
                                                 1,
                                                 self::PAGE_SIZE,
                                             ],
                                         ],
                                     ]);
    }



    public function calculateData($i, $rows, $apply_avg, $avg_data, &$avg_readings, &$data, $sql_date_format, $direction = 1, $first = false) {
        $date = Yii::$app->formatter->asDatetime($i);
        $prev_date = ($direction == 1) ? Yii::$app->formatter->asDatetime($i - 3600) : Yii::$app->formatter->asDatetime($i + 3600);
        $data[$date] = [
            'date' => $date,
            'timestamp' => $i,
            'meter_id' => $this->_meter_id,
            'channel_id' => $this->_channel_id,
            'class' => 'danger',
            'input_class' => 'form-control-default',
            'datetime' => isset($rows[$date]['datetime']) ? $rows[$date]['datetime'] : date('Y-m-d G:i:s', $i)
        ];
        if (!$first)
        {
            if(isset($rows[$date])) {
                $data[$date] = ArrayHelper::merge($rows[$date], $data[$date]);
                if(empty($data[$date])) {
                    $data[$date] = [];
                }
                $this->readings[$date]['kilowatt_hour'] = $rows[$date]['kilowatt_hour'];
                $this->readings[$date]['cubic_meter'] = $rows[$date]['cubic_meter'];
                $this->readings[$date]['kilowatt'] = $rows[$date]['kilowatt'];
                $this->readings[$date]['cubic_meter_hour'] = $rows[$date]['cubic_meter_hour'];
                $this->readings[$date]['incoming_temp'] = $rows[$date]['incoming_temp'];
                $this->readings[$date]['outgoing_temp'] = $rows[$date]['outgoing_temp'];
                $this->readings[$date]['datetime'] = $rows[$date]['datetime'];
                if(!empty($rows[$date]['created_by']) || !empty($rows[$date]['modified_by'])) {
                    $data[$date]['class'] = 'warning';
                }
                else {
                    $data[$date]['class'] = 'default';
                }
            }
            else {
                if ($apply_avg){
                    if ($direction == 1)
                    {
                        $this->readings[$date]['kilowatt_hour'] =  $this->readings[$prev_date]['kilowatt_hour'] + $avg_data[$date]['kilowatt_hour'];
                        $this->readings[$date]['cubic_meter'] = $avg_data[$date]['cubic_meter'];
                        $this->readings[$date]['kilowatt'] = $avg_data[$date]['kilowatt'];
                        $this->readings[$date]['cubic_meter_hour'] = $avg_data[$date]['cubic_meter_hour'];
                        $this->readings[$date]['incoming_temp'] = $avg_data[$date]['incoming_temp'];
                        $this->readings[$date]['outgoing_temp'] = $avg_data[$date]['outgoing_temp'];
                        $this->readings[$date]['datetime'] = $rows[$date]['datetime'];
                    }
                    else
                    {
                        $this->readings[$date]['kilowatt_hour'] =  $this->readings[$prev_date]['kilowatt_hour'] - $avg_data[$date]['kilowatt_hour'];
                        $this->readings[$date]['cubic_meter'] = $avg_data[$date]['cubic_meter'];
                        $this->readings[$date]['kilowatt'] = $avg_data[$date]['kilowatt'];
                        $this->readings[$date]['cubic_meter_hour'] = $avg_data[$date]['cubic_meter_hour'];
                        $this->readings[$date]['incoming_temp'] = $avg_data[$date]['incoming_temp'];
                        $this->readings[$date]['outgoing_temp'] = $avg_data[$date]['outgoing_temp'];
                        $this->readings[$date]['datetime'] = $rows[$date]['datetime'];
                    }
                }
            }
        }
        else
        {
            if ($apply_avg && !isset($rows[$date]))
            {
                $prev_date = ($direction == 1) ? Yii::$app->formatter->asDatetime($i - 3600, 'Y-MM-dd HH:m:s') : Yii::$app->formatter->asDatetime($i + 3600, 'Y-MM-dd HH:m:s');
                $prev_data = (new Query())
                    ->select('kilowatt_hour, cubic_meter, kilowatt, cubic_meter_hour, incoming_temp, outgoing_temp')
                    ->from(AirMeterRawData::tableName() . ' t')
                    ->andWhere([
                        't.meter_id' => $this->_meter_id,
                        't.channel_id' => $this->_channel_id,
                    ])
                    ->andWhere('datetime = :date', [
                        'date' => $prev_date
                    ])
                    ->one();

                if ($direction == 1)
                {
                    $this->readings[$date]['kilowatt_hour'] =  $prev_data['kilowatt_hour'] + $avg_data[$date]['kilowatt_hour'];
                    $this->readings[$date]['cubic_meter'] = $avg_data[$date]['cubic_meter'];
                    $this->readings[$date]['kilowatt'] = $avg_data[$date]['kilowatt'];
                    $this->readings[$date]['cubic_meter_hour'] = $avg_data[$date]['cubic_meter_hour'];
                    $this->readings[$date]['incoming_temp'] = $avg_data[$date]['incoming_temp'];
                    $this->readings[$date]['outgoing_temp'] = $avg_data[$date]['outgoing_temp'];
                }
                else
                {
                    $this->readings[$date]['kilowatt_hour'] =  $prev_data['kilowatt_hour'] - $avg_data[$date]['kilowatt_hour'];
                    $this->readings[$date]['cubic_meter'] = $avg_data[$date]['cubic_meter'];
                    $this->readings[$date]['kilowatt'] = $avg_data[$date]['kilowatt'];
                    $this->readings[$date]['cubic_meter_hour'] = $avg_data[$date]['cubic_meter_hour'];
                    $this->readings[$date]['incoming_temp'] = $avg_data[$date]['incoming_temp'];
                    $this->readings[$date]['outgoing_temp'] = $avg_data[$date]['outgoing_temp'];
                }
            }
            else
            {
                $this->readings[$date]['kilowatt_hour'] = $rows[$date]['kilowatt_hour'];
                $this->readings[$date]['cubic_meter'] = $rows[$date]['cubic_meter'];
                $this->readings[$date]['kilowatt'] = $rows[$date]['kilowatt'];
                $this->readings[$date]['cubic_meter_hour'] = $rows[$date]['cubic_meter_hour'];
                $this->readings[$date]['incoming_temp'] = $rows[$date]['incoming_temp'];
                $this->readings[$date]['outgoing_temp'] = $rows[$date]['outgoing_temp'];
                $this->readings[$date]['datetime'] = $rows[$date]['datetime'];
            }
        }

    }



    public function getQueryRows() {
        $from_date = $this->getDateFromPeriod()->format('Y-m-d H:i:s');
        $to_date = $this->getDateToPeriod()->format('Y-m-d H:i:s');
        $query = (new Query())->from(AirMeterRawData::tableName() . ' t')
                              ->andWhere([
                                             't.meter_id' => $this->_meter_id,
                                             't.channel_id' => $this->_channel_id,
                                         ])->andWhere('datetime >= :from_date AND datetime <= :to_date', [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ]);
        $result = $query->orderBy(['t.datetime' => SORT_ASC])->all();
        return ArrayHelper::map($result, function ($model) {
            return Yii::$app->formatter->asDatetime($model['datetime']);
        }, function ($model) {
            return $model;
        });
    }


    public function getApplyAvg()
    {
        return Yii::$app->request->getQueryParam('avg');
    }


    public function save() {

        if(!$this->validate()) return false;

        if($this->readings == null) return true;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach($this->readings as $date => $values) {
                $model = AirMeterRawData::find()
                    ->where([
                        'meter_id' => $this->_meter_id,
                        'channel_id' => $this->_channel_id,
                    ])
                    ->andWhere([
                        'datetime' => $values['datetime']
                    ])
                    ->one();

                if(is_null($model)) {
                    $model = new AirMeterRawData();
                    $model->meter_id = $this->_meter_id;
                    $model->channel_id = $this->_channel_id;
                    $model->datetime = $values['datetime'];
                    unset($values['datetime']);
                    $values = array_filter($values, function ($item) {
                        return ($item != null);
                    });
                }

                if($values != null) {
                    $model->attributes = $values;
                    if($model->oldAttributes != $model->attributes) {
                        if(!$model->save()) {
                            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
                        }
                    }
                }
            }
            $transaction->commit();
            return true;
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new BadRequestHttpException($e->getMessage());
        }
    }



}

