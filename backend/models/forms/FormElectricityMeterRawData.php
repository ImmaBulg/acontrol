<?php

namespace backend\models\forms;

use api\models\forms\FormMeterDataSingle;
use api\models\forms\FormMeterRawDataSingle;
use common\components\i18n\Formatter;
use common\models\ElectricityMeterRawData;
use DateTime;
use Exception;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Request;

/**
 * FormElectricityMeterRawData is the class for meter raw data create/edit.
 */
class FormElectricityMeterRawData extends FormMeterRawData
{

    public $test = 'no test';
    public $r = [];

    public function attributeLabels()
    {
        return [
            'date' => Yii::t('backend.meter', 'Reading date'),
            'shefel' => Yii::t('backend.meter', 'Shefel'),
            'shefel_avg' => Yii::t('backend.meter', 'AVG'),
            'max_shefel' => Yii::t('backend.meter', 'Max shefel'),
            'export_shefel' => Yii::t('backend.meter', 'Export shefel'),
            'geva' => Yii::t('backend.meter', 'Geva'),
            'geva_avg' => Yii::t('backend.meter', 'AVG'),
            'max_geva' => Yii::t('backend.meter', 'Max geva'),
            'export_geva' => Yii::t('backend.meter', 'Export geva'),
            'pisga' => Yii::t('backend.meter', 'Pisga'),
            'pisga_avg' => Yii::t('backend.meter', 'AVG'),
            'max_pisga' => Yii::t('backend.meter', 'Max pisga'),
            'export_pisga' => Yii::t('backend.meter', 'Export pisga'),
        ];
    }

    public function calculateData($i, $rows, $apply_avg, $avg_date, &$avg_readings, &$data, $sql_date_fromat, $direction = 1, $first = false)
    {
        $date = Yii::$app->formatter->asDatetime($i);
        $prev_date = Yii::$app->formatter->asDatetime($i - $direction * 3600);

        $data[$date] = [
            'date' => $date,
            'timestamp' => isset($rows[$date]['timestamp']) ? $rows[$date]['timestamp'] : $i,
            'meter_id' => $this->_meter_id,
            'channel_id' => $this->_channel_id,
            'class' => 'danger',
            'input_class' => 'form-control-default',
        ];
        if (!$first) {
            if (isset($rows[$date])) {
                if (!$apply_avg) {
                    $data[$date] = ArrayHelper::merge($rows[$date], $data[$date]);
                    if (empty($data[$date])) {
                        $data[$date] = [];
                    }
                    $this->consumption[$date]['shefel'] = $rows[$date]['shefel'];
                    $this->consumption[$date]['geva'] = $rows[$date]['geva'];
                    $this->consumption[$date]['pisga'] = $rows[$date]['pisga'];
                    $this->consumption[$date]['max_shefel'] = $rows[$date]['max_shefel'];
                    $this->consumption[$date]['max_geva'] = $rows[$date]['max_geva'];
                    $this->consumption[$date]['max_pisga'] = $rows[$date]['max_pisga'];
                    $this->consumption[$date]['export_shefel'] = $rows[$date]['export_shefel'];
                    $this->consumption[$date]['export_geva'] = $rows[$date]['export_geva'];
                    $this->consumption[$date]['export_pisga'] = $rows[$date]['export_pisga'];
                    $this->consumption[$date]['kvar_shefel'] = $rows[$date]['kvar_shefel'];
                    $this->consumption[$date]['kvar_geva'] = $rows[$date]['kvar_geva'];
                    $this->consumption[$date]['kvar_pisga'] = $rows[$date]['kvar_pisga'];
                    $this->readings[$date]['shefel'] = $rows[$date]['reading_shefel'];
                    $this->readings[$date]['geva'] = $rows[$date]['reading_geva'];
                    $this->readings[$date]['pisga'] = $rows[$date]['reading_pisga'];
                    $this->readings[$date]['timestamp'] = $i;
                } else {
                    $data[$date] = ArrayHelper::merge($rows[$date], $data[$date]);
                    if (empty($data[$date])) {
                        $data[$date] = [];
                    }
                    $this->consumption[$date]['shefel'] = $rows[$date]['shefel'];
                    $this->consumption[$date]['geva'] = $rows[$date]['geva'];
                    $this->consumption[$date]['pisga'] = $rows[$date]['pisga'];
                    $this->consumption[$date]['max_shefel'] = $rows[$date]['max_shefel'];
                    $this->consumption[$date]['max_geva'] = $rows[$date]['max_geva'];
                    $this->consumption[$date]['max_pisga'] = $rows[$date]['max_pisga'];
                    $this->consumption[$date]['export_shefel'] = $rows[$date]['export_shefel'];
                    $this->consumption[$date]['export_geva'] = $rows[$date]['export_geva'];
                    $this->consumption[$date]['export_pisga'] = $rows[$date]['export_pisga'];
                    $this->consumption[$date]['kvar_shefel'] = $rows[$date]['kvar_shefel'];
                    $this->consumption[$date]['kvar_geva'] = $rows[$date]['kvar_geva'];
                    $this->consumption[$date]['kvar_pisga'] = $rows[$date]['kvar_pisga'];
                    $this->readings[$date]['shefel'] = $rows[$date]['reading_shefel'] + $direction * $avg_date[$date]['shefel'];
                    $this->readings[$date]['geva'] = $rows[$date]['reading_geva'] + $direction * $avg_date[$date]['geva'];
                    $this->readings[$date]['pisga'] = $rows[$date]['reading_pisga'] + $direction * $avg_date[$date]['pisga'];
                    $this->readings[$date]['timestamp'] = $i;

                }
            } else {
                if ($apply_avg) {
                    $this->readings[$date]['shefel'] = $this->readings[$prev_date]['shefel'] + $direction * $avg_date[$date]['shefel'];
                    $this->readings[$date]['geva'] = $this->readings[$prev_date]['geva'] + $direction * $avg_date[$date]['geva'];
                    $this->readings[$date]['pisga'] = $this->readings[$prev_date]['pisga'] + $direction * $avg_date[$date]['pisga'];
                    $this->readings[$date]['timestamp'] = $i;
                } else {
                }
            }
        } else {
            $prev_date = (new Query())
                ->select('*')
                ->from(ElectricityMeterRawData::tableName() . ' t')
                ->andWhere([
                    't.meter_id' => $this->_meter_id,
                    't.channel_id' => $this->_channel_id,
                ])
                ->andWhere('date = :prev_date', [
                    'prev_date' => Yii::$app->formatter->asDatetime($i - $direction * 3600, 'Y-MM-dd HH:m:s'),
                ])
                ->one();
            if ($apply_avg) {
                if ($prev_date !== null) {
                    $this->consumption[$date]['shefel'] = $prev_date['shefel'];
                    $this->consumption[$date]['geva'] = $prev_date['geva'];
                    $this->consumption[$date]['pisga'] = $prev_date['pisga'];
                    $this->consumption[$date]['max_shefel'] = $prev_date['max_shefel'];
                    $this->consumption[$date]['max_geva'] = $prev_date['max_geva'];
                    $this->consumption[$date]['max_pisga'] = $prev_date['max_pisga'];
                    $this->consumption[$date]['export_shefel'] = $prev_date['export_shefel'];
                    $this->consumption[$date]['export_geva'] = $prev_date['export_geva'];
                    $this->consumption[$date]['export_pisga'] = $prev_date['export_pisga'];
                    $this->consumption[$date]['kvar_shefel'] = $prev_date['kvar_shefel'];
                    $this->consumption[$date]['kvar_geva'] = $prev_date['kvar_geva'];
                    $this->consumption[$date]['kvar_pisga'] = $prev_date['kvar_pisga'];
                    $this->readings[$date]['shefel'] = $prev_date['shefel'] + $direction * $avg_date[$date]['shefel'];
                    $this->readings[$date]['geva'] = $prev_date['geva'] + $direction * $avg_date[$date]['geva'];
                    $this->readings[$date]['pisga'] = $prev_date['pisga'] + $direction * $avg_date[$date]['pisga'];
                    $this->readings[$date]['timestamp'] = $i;
                    $this->test = 'test';
                } else {
                    $this->consumption[$date]['shefel'] = $prev_date['shefel'] + $direction * $avg_date[$date]['shefel'];
                    $this->consumption[$date]['geva'] = $prev_date['geva'] + $direction * $avg_date[$date]['geva'];
                    $this->consumption[$date]['pisga'] = $prev_date['pisga'] + $direction * $avg_date[$date]['pisga'];
                    $this->readings[$date]['shefel'] = $prev_date['shefel'] + $direction * $avg_date[$date]['shefel'];
                    $this->readings[$date]['geva'] = $prev_date['geva'] + $direction * $avg_date[$date]['geva'];
                    $this->readings[$date]['pisga'] = $prev_date['pisga'] + $direction * $avg_date[$date]['pisga'];
                    $this->readings[$date]['timestamp'] = $i;
                }
            } else {
                $this->consumption[$date]['shefel'] = $rows[$date]['shefel'];
                $this->consumption[$date]['geva'] = $rows[$date]['geva'];
                $this->consumption[$date]['pisga'] = $rows[$date]['pisga'];
                $this->consumption[$date]['max_shefel'] = $rows[$date]['max_shefel'];
                $this->consumption[$date]['max_geva'] = $rows[$date]['max_geva'];
                $this->consumption[$date]['max_pisga'] = $rows[$date]['max_pisga'];
                $this->consumption[$date]['export_shefel'] = $rows[$date]['export_shefel'];
                $this->consumption[$date]['export_geva'] = $rows[$date]['export_geva'];
                $this->consumption[$date]['export_pisga'] = $rows[$date]['export_pisga'];
                $this->consumption[$date]['kvar_shefel'] = $rows[$date]['kvar_shefel'];
                $this->consumption[$date]['kvar_geva'] = $rows[$date]['kvar_geva'];
                $this->consumption[$date]['kvar_pisga'] = $rows[$date]['kvar_pisga'];
                $this->readings[$date]['shefel'] = $rows[$date]['reading_shefel'];
                $this->readings[$date]['geva'] = $rows[$date]['reading_geva'];
                $this->readings[$date]['pisga'] = $rows[$date]['reading_pisga'];
                $this->readings[$date]['timestamp'] = $i;
            }
        }
    }


    public function calculateData2($i, $rows, $apply_avg, $avg_data, &$avg_readings, &$data, $sql_date_format, $direction = 1)
    {
        $date = Yii::$app->formatter->asDatetime($i);
        $data[$date] = [
            'date' => $date,
            'timestamp' => isset($rows[$date]['timestamp']) ? $rows[$date]['timestamp'] : $i,
            'meter_id' => $this->_meter_id,
            'channel_id' => $this->_channel_id,
            'class' => 'danger',
            'input_class' => 'form-control-default',
        ];
        if (isset($rows[$date])) {
            $data[$date] = ArrayHelper::merge($rows[$date], $data[$date]);
            if (empty($data[$date])) {
                $data[$date] = [];
            }
            $this->consumption[$date]['shefel'] = $rows[$date]['shefel'];
            $this->consumption[$date]['geva'] = $rows[$date]['geva'];
            $this->consumption[$date]['pisga'] = $rows[$date]['pisga'];
            $this->consumption[$date]['max_shefel'] = $rows[$date]['max_shefel'];
            $this->consumption[$date]['max_geva'] = $rows[$date]['max_geva'];
            $this->consumption[$date]['max_pisga'] = $rows[$date]['max_pisga'];
            $this->consumption[$date]['export_shefel'] = $rows[$date]['export_shefel'];
            $this->consumption[$date]['export_geva'] = $rows[$date]['export_geva'];
            $this->consumption[$date]['export_pisga'] = $rows[$date]['export_pisga'];
            $this->consumption[$date]['kvar_shefel'] = $rows[$date]['kvar_shefel'];
            $this->consumption[$date]['kvar_geva'] = $rows[$date]['kvar_geva'];
            $this->consumption[$date]['kvar_pisga'] = $rows[$date]['kvar_pisga'];
            $this->readings[$date]['shefel'] = $rows[$date]['reading_shefel'];
            $this->readings[$date]['geva'] = $rows[$date]['reading_geva'];
            $this->readings[$date]['pisga'] = $rows[$date]['reading_pisga'];
            $this->readings[$date]['timestamp'] = isset($rows[$date]['timestamp']) ? $rows[$date]['timestamp'] : $i;
            $avg_readings[$date] = [
                'shefel' => ($reading_shefel = ArrayHelper::getValue($rows[$date], 'shefel', 0)) ? $reading_shefel :
                    ArrayHelper::getValue($rows[$date], 'reading_shefel', 0),
                'geva' => ($reading_geva = ArrayHelper::getValue($rows[$date], 'geva', 0)) ? $reading_geva :
                    ArrayHelper::getValue($rows[$date], 'reading_geva', 0),
                'pisga' => ($reading_pisga = ArrayHelper::getValue($rows[$date], 'pisga', 0)) ? $reading_pisga :
                    ArrayHelper::getValue($rows[$date], 'reading_pisga', 0),
                'export_shefel' => ArrayHelper::getValue($rows[$date], 'export_shefel', 0),
                'export_geva' => ArrayHelper::getValue($rows[$date], 'export_geva', 0),
                'export_pisga' => ArrayHelper::getValue($rows[$date], 'export_pisga', 0),
            ];
            if (!empty($rows[$date]['created_by']) || !empty($rows[$date]['modified_by'])) {
                $data[$date]['class'] = 'warning';
            } else {
                $data[$date]['class'] = 'default';
            }
        } else {
            if (empty($data[$date])) {
                $data[$date] = [];
            }
            if ($apply_avg) {
                $base_date = Yii::$app->formatter->asDate($i + $direction * 86400);
                if (!empty($avg_readings[$base_date])) {
                    $this->calculateSum($this->consumption[$date], $avg_readings, $base_date, $date, $direction,
                        $avg_readings, $avg_data);
                    $data[$date]['class'] = 'default';
                    $data[$date]['input_class'] = 'form-control-danger';
                } else {
                    $previous_data[$base_date] = (new Query())
                        ->select('IFNULL(`shefel`, `reading_shefel`) as shefel, IFNULL(`geva`, `reading_geva`) as geva, IFNULL(`pisga`, `reading_pisga`) as pisga, export_shefel, export_geva, export_pisga')
                        ->from(ElectricityMeterRawData::tableName() . ' t')->andWhere([
                            't.meter_id' => $this->_meter_id,
                            't.channel_id' => $this->_channel_id,
                        ])
                        ->andWhere("DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format') = :date", [
                            'date' => Yii::$app->formatter->asDate($base_date, Formatter::PHP_DATE_FORMAT),
                        ])->one();
                    if ($previous_data != null) {
                        $this->calculateSum($this->consumption[$date], $avg_readings, $base_date, $date,
                            $direction, $previous_data, $avg_data);
                        $data[$date]['class'] = 'default';
                        $data[$date]['input_class'] = 'form-control-danger';
                    }
                }
            }
        }
    }

    public function getApplyAvg()
    {
        return Yii::$app->request->getQueryParam('avg');
    }

    protected function generateDataProvider()
    {
        $data = [];
        $from_date = $this->getDateFromPeriod()->getTimestamp();
        $to_date = $this->getDateToPeriod()->getTimestamp();
        $rows = $this->getQueryRows();
        $sql_date_format = Formatter::SQL_DATE_FORMAT;
        $avg_readings = [];
        $avg_data = $this->_avg_data;
        $direction = $this->_direction;
        $apply_avg = Yii::$app->request->getQueryParam('avg');
        if ($direction) {
            $this->calculateData($from_date, $rows, $apply_avg, $avg_data, $avg_readings, $data, $sql_date_format,
                1, true);
            for ($i = $from_date + 3600; $i < $to_date; $i = $i + ElectricityMeterRawData::CALCULATION_TIME) {
                $this->calculateData($i, $rows, $apply_avg, $avg_data, $avg_readings, $data, $sql_date_format,
                    1);
            }
        } else {
            $this->calculateData($to_date, $rows, $apply_avg, $avg_data, $avg_readings, $data, $sql_date_format,
                1, true);
            for ($i = $to_date + 3600; $i >= $from_date; $i = $i - ElectricityMeterRawData::CALCULATION_TIME) {
                $this->calculateData($i, $rows, $apply_avg, $avg_data, $avg_readings, $data, $sql_date_format, -1);
            }
            uasort($data, function ($a, $b) {
                return $a['timestamp'] - $b['timestamp'];
            });
        }
        $this->r = $this->readings;

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


    public function getQueryRows()
    {
        $from_date = $this->getDateFromPeriod()->getTimestamp();
        $to_date = $this->getDateToPeriod()->getTimestamp();
        $query = (new Query())->select('*, date as timepstamp')->from(ElectricityMeterRawData::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $this->_meter_id,
                't.channel_id' => $this->_channel_id,
            ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ]);
        $rows = $query->orderBy([ 't.date' => SORT_ASC])->all();
        return ArrayHelper::map($rows, function ($model) {
            return Yii::$app->formatter->asDatetime($model['date']);
        }, function ($model) {
            return $model;
        });
    }

    public function save()
    {
        if (!$this->validate()) return false;
        if ($this->consumption == null) return true;
        $transaction = Yii::$app->db->beginTransaction();
        $reading_pisga = 0;
        $reading_geva = 0;
        $reading_shefel = 0;
        $models = [];

        try {
            foreach ($this->consumption as $date => $values) {
                $model = ElectricityMeterRawData::find()
                    ->where([
                        'meter_id' => $this->_meter_id,
                        'channel_id' => $this->_channel_id,
                    ])
                    ->andWhere(['date' => $values['timestamp']])
                    ->one();

                if ($model == null) {
                    $model = new ElectricityMeterRawData();
                    $model->meter_id = $this->_meter_id;
                    $model->channel_id = $this->_channel_id;
                    $model->date = $values['timestamp'];
                    unset($values['timestamp']);
                    $values = array_filter($values, function ($item) {
                        return ($item != null);
                    });
                } elseif (!$reading_pisga && !$reading_geva && !$reading_shefel) {
                    $reading_pisga = !empty($model->reading_pisga) ? $model->reading_pisga : $reading_pisga;
                    $reading_geva = !empty($model->reading_geva) ? $model->reading_geva : $reading_geva;
                    $reading_shefel = !empty($model->reading_shefel) ? $model->reading_shefel : $reading_shefel;
                }

                $reading_pisga = $reading_pisga + $values['pisga'];
                $reading_geva = $reading_geva + $values['geva'];
                $reading_shefel = $reading_shefel + $values['shefel'];

                if ($values != null) {
                    $model->attributes = $values;
                    $model->reading_pisga = $reading_pisga;
                    $model->reading_geva = $reading_geva;
                    $model->reading_shefel = $reading_shefel;
                    if ($model->oldAttributes != $model->attributes) {
                        if (!$model->save()) {
                            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
                        }
                        $models[] = $model;
                        ElectricityMeterRawData::deleteCacheValue(["meter_raw_data:{$model->relationMeter->name}_{$model->relationMeterChannel->channel}"]);
                    }
                }
            }
            if ($this->r !== null) {
                foreach ($this->readings as $date => $values) {
                    $model = ElectricityMeterRawData::find()
                        ->where([
                            'meter_id' => $this->_meter_id,
                            'channel_id' => $this->_channel_id,
                        ])
                        ->andWhere(['date' => $values['timestamp']])
                        ->one();

                    $reading_pisga = $values['pisga'];
                    $reading_geva = $values['geva'];
                    $reading_shefel = $values['shefel'];

                    if ($model === null) {
                        $model = new ElectricityMeterRawData();
                        $model->meter_id = $this->_meter_id;
                        $model->channel_id = $this->_channel_id;
                        $model->date = $values['timestamp'];
                        unset($values['timestamp']);
                        $values = array_filter($values, function ($item) {
                            return ($item != null);
                        });
                    }

                    $model->reading_pisga = $reading_pisga;
                    $model->reading_geva = $reading_geva;
                    $model->reading_shefel = $reading_shefel;
                    if (!$model->save()) {
                        throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
                    }
                    $models[] = $model;
                    ElectricityMeterRawData::deleteCacheValue(["meter_raw_data:{$model->relationMeter->name}_{$model->relationMeterChannel->channel}"]);
                }
            }

            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollback();
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
