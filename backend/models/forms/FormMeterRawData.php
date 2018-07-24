<?php

namespace backend\models\forms;

use common\components\i18n\Formatter;
use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\debug\models\timeline\DataProvider;
use yii\helpers\VarDumper;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 05.07.2017
 * Time: 6:58
 */
abstract class FormMeterRawData extends Model
{
    const PAGE_SIZE = 100;
    const PAGE_PARAM = 'page';

    const SCENARIO_CREATE = 'create';
    const SCENARIO_EDIT = 'edit';

    protected $_meter_id;
    protected $_channel_id;
    protected $_from_date;
    protected $_to_date;
    protected $_avg_data;
    protected $_direction = true;
    protected $_data_provider;

    public $consumption;
    public $readings;
    public function rules() {
        return [
            [['consumption'], function ($attribute, $params) {
                $consumption = (array)$this->$attribute;
                foreach($consumption as $date => $values) {
                    foreach($values as $key => $value) {
                        if($value != null) {
                            if(!preg_match('/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/', $value)) {
                                $this->addError($attribute . '[' . $date . '][' . $key . ']',
                                    Yii::t('backend.meter', '{attribute} for {date} must be a number.', [
                                        'attribute' => $this->getAttributeLabel($attribute),
                                        'date' => $key,
                                    ]));
                            }
                            else {
                                if($value < 0) {
                                    $this->addError($attribute . '[' . $date . '][' . $key . ']',
                                        Yii::t('backend.meter',
                                            '{attribute} for {date} must be no less than 0.',
                                            [
                                                'attribute' => $this->getAttributeLabel($attribute),
                                                'date' => $key,
                                            ]));
                                }
                            }
                        }
                    }
                }
            }],
        ];
    }

    public function calculateSum(&$container, &$avg_readings, $base_date, $date, $direction, $new_readings, $old_readings) {
        $container['shefel'] = ($shefel =
            $new_readings[$base_date]['shefel'] + $direction * $old_readings[$date]['shefel']) >= 0 ? $shefel :
            0;
        $container['geva'] = ($geva =
            $new_readings[$base_date]['geva'] + $direction * $old_readings[$date]['geva']) >= 0 ? $geva : 0;
        $container['pisga'] = ($pisga =
            $new_readings[$base_date]['pisga'] + $direction * $old_readings[$date]['pisga']) >= 0 ? $pisga : 0;
        $container['export_shefel'] = ($export_shefel =
            $new_readings[$base_date]['export_shefel'] + $direction * $old_readings[$date]['export_shefel']) >=
        0 ? $export_shefel : 0;
        $container['export_geva'] = ($export_geva =
            $new_readings[$base_date]['export_geva'] + $direction * $old_readings[$date]['export_geva']) >= 0 ?
            $export_geva : 0;
        $container['export_pisga'] = ($export_pisga =
            $new_readings[$base_date]['export_pisga'] + $direction * $old_readings[$date]['export_pisga']) >=
        0 ?
            $export_pisga : 0;
        $avg_readings[$date] = [
            'shefel' => $shefel,
            'geva' => $geva,
            'pisga' => $pisga,
            'export_shefel' => $export_shefel,
            'export_geva' => $export_geva,
            'export_pisga' => $export_pisga,
        ];
    }

    public function calculateAirSum(&$container, &$avg_readings, $base_date, $date, $direction, $new_readings, $old_readings) {
        $container['kilowatt_hour'] = ($kilowatt_hour =
            $new_readings[$base_date]['kilowatt_hour'] + $direction * $old_readings[$date]['kilowatt_hour']) >= 0 ? $kilowatt_hour :
            0;
        $container['cubic_meter'] = ($cubic_meter =
            $new_readings[$base_date]['cubic_meter'] + $direction * $old_readings[$date]['cubic_meter']) >= 0 ? $cubic_meter : 0;
        $container['kilowatt'] = ($kilowatt =
            $new_readings[$base_date]['kilowatt'] + $direction * $old_readings[$date]['kilowatt']) >= 0 ? $kilowatt : 0;
        $container['cubic_meter_hour'] = ($cubic_meter_hour =
            $new_readings[$base_date]['cubic_meter_hour'] + $direction * $old_readings[$date]['cubic_meter_hour']) >=
        0 ? $cubic_meter_hour : 0;
        $container['incoming_temp'] = ($incoming_temp =
            $new_readings[$base_date]['incoming_temp'] + $direction * $old_readings[$date]['incoming_temp']) >= 0 ?
            $incoming_temp : 0;
        $container['outgoing_temp'] = ($outgoing_temp =
            $new_readings[$base_date]['outgoing_temp'] + $direction * $old_readings[$date]['outgoing_temp']) >=
        0 ?
            $outgoing_temp : 0;
        $avg_readings[$date] = [
            'kilowatt_hour' => $kilowatt_hour,
            'cubic_meter' => $cubic_meter,
            'kilowatt' => $kilowatt,
            'cubic_meter_hour' => $cubic_meter_hour,
            'incoming_temp' => $incoming_temp,
            'outgoing_temp' => $outgoing_temp,
        ];
    }

    public function setReadings($readings) {
        $this->readings = $readings;
    }


    /**
     * @return DataProvider
     */
    public function getDataProvider() {
        if($this->_data_provider == null) {
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
            for($i = $from_date; $i <= $to_date; $i = $i + 86400) {
                $this->calculateData($i, $rows, $apply_avg, $avg_data, $avg_readings, $data, $sql_date_format, $avg_readings);
            }
        }
        else {
            for($i = $to_date; $i >= $from_date; $i = $i - 86400) {
                $this->calculateData($i, $rows, $apply_avg, $avg_data, $avg_readings, $data, $sql_date_format, -1);
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

    abstract public function getQueryRows();
    abstract public function calculateData($i, $rows, $apply_avg, $avg_data, &$avg_readings, &$data, $sql_date_format, $direction = 1);

    public function loadAttributes($model) {
        $this->_meter_id = $model->relationMeter->name;
        $this->_channel_id = $model->channel;
    }


    public function loadFilters($form) {
        $this->_from_date = $form->from_date;
        $this->_to_date = $form->to_date;
    }


    public function loadAvgData($form) {
        $this->_avg_data = $form->getAvgData();
        $this->_direction = $form->direction;
    }


    protected function getDateFromPeriod() {
        if($this->_from_date != null) {
            $date = new DateTime($this->_from_date);
            return $date;
        }
    }


    protected function getDateToPeriod() {
        if($this->_to_date != null) {
            $date = new DateTime($this->_to_date);
            return $date;
        }
    }
}
