<?php namespace common\models;

use Carbon\Carbon;
use common\components\behaviors\UserIdBehavior;
use common\components\i18n\Formatter;
use DateTime;
use dezmont765\yii2bundle\behaviors\DateTimeBehavior;
use Yii;
use yii\base\InvalidParamException;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * AirMeterRawData is the class for the table "air_meter_raw_data".
 */
class AirMeterRawData extends BaseMeterRawData
{

    const KILOWATT_HOUR = 'kilowatt_hour';
    const CUBIC_METER = 'cubic_meter';
    const KILOWATT = 'kilowatt';
    const CUBIC_METER_HOUR = 'cubic_meter_hour';
    const INCOMING_TEMP = 'incoming_temp';
    const OUTGOING_TEMP = 'outgoing_temp';

    public static function tableName() {
        return 'air_meter_raw_data';
    }


    public function rules() {
        return ArrayHelper::merge(parent::rules(), [
            [['kilowatt_hour', 'cubic_meter', 'kilowatt', 'cubic_meter_hour', 'incoming_temp', 'outgoing_temp', 'cop',
              'delta_t'],
             'number'],
            [['datetime'], 'safe'],
            //            [['datetime'], 'date','format'=>'php:Y-m-d H:i:s'],
        ]);
    }


    public function attributeLabels() {
        return ArrayHelper::merge(parent::attributeLabels(), []);
    }


    public function behaviors() {
        return [
            [
                'class' => DateTimeBehavior::className(),
                'created_at_attribute' => 'created_at',
                'updated_at' => 'modified_at',
            ],
            [
                'class' => UserIdBehavior::className(),
                'createdByAttribute' => 'created_by',
                'modifiedByAttribute' => 'modified_by',
            ],
        ];
    }


    public static function getSeason($date) {
        //TODO: Must be created
        $date = new DateTime('@' . Yii::$app->formatter->asTimestamp($date));
        $month = $date->format('n');
        if ($month >= 3 && $month <= 5) {
            return self::SEASON_SPRING;
        }
        else {
            if ($month >= 6 && $month <= 8) {
                return self::SEASON_SUMMER;
            }
            else {
                if ($month >= 9 && $month <= 11) {
                    return self::SEASON_FALL;
                }
                else {
                    return self::SEASON_WINTER;
                }
            }
        }
    }


    public static function getListSeasonAvgConstants() {
        //TODO: Must be created
    }


    public static function getRulePeriodRange($rule, $from_date, $to_date) {
        //TODO: Must be created
        $date = Yii::$app->formatter->asTimestamp($from_date);
        switch($rule) {
            case self::RULE_LAST_60_DAYS_OR_UP_UNTIL_SEASON_CHANGE:
                $date = $from_date - 61 * 86400;
                $with_season = true;
                break;
            case self::RULE_LAST_60_DAYS_WITHOUT_SEASONS:
                $date = $from_date - 61 * 86400;
                $with_season = false;
                break;
            case self::RULE_LAST_14_DAYS:
                $date = $from_date - 15 * 86400;
                $with_season = false;
                break;
            case self::RULE_SAME_PERIOD_LAST_YEAR:
            default:
                $date = $from_date - 365 * 86400;
                $with_season = false;
                break;
        }
        return [
            'from_date' => $date,
            'to_date' => Yii::$app->formatter->asTimestamp($to_date),
            'with_season' => $with_season,
        ];
    }


    public static function getAvgData($meter_id, $channel_id, $period_from, $period_to, $from_date, $to_date,
                                      $with_season = false) {
        //TODO: Must be created
        $data = [];
        $fd = $from_date;
        $td = $to_date;
        $pf = $period_from;
        $pt = $period_to;
        $from_date = Yii::$app->formatter->asDateTime($from_date, 'Y-MM-dd HH:m');
        $to_date = Yii::$app->formatter->asDateTime($to_date, 'Y-MM-dd HH:m');
        $period_to = Yii::$app->formatter->asDateTime($period_to, 'Y-MM-dd HH:m');
        $period_from = Yii::$app->formatter->asDateTime($period_from, 'Y-MM-dd HH:m');

        $consumption = [
            self::KILOWATT_HOUR => 0,
            self::CUBIC_METER => 0,
            self::KILOWATT => 0,
            self::CUBIC_METER_HOUR => 0,
            self::INCOMING_TEMP => 0,
            self::OUTGOING_TEMP => 0,
        ];
        //killowat hour
        $kilowatt_hour_start_date = (new Query())
            ->select('kilowatt_hour')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime = :from_date', [
                'from_date' => $period_from,
            ])
            ->scalar();
        $kilowatt_hour_end_date = (new Query())
            ->select('kilowatt_hour')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime = :to_date', [
                'to_date' => $period_to,
            ])
            ->scalar();
        $kilowatt_hour_count = (new Query())
            ->select('COUNT(`kilowatt_hour`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere(
                'datetime >= :from_date AND datetime <= :to_date',
                [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ]
            )
            ->andWhere('t.kilowatt_hour IS NOT NULL')
            ->scalar();
        if ($kilowatt_hour_count ) {
            $consumption[self::KILOWATT_HOUR] = ($kilowatt_hour_end_date - $kilowatt_hour_start_date) / $kilowatt_hour_count;
        }

        //cubic meter
        $cubic_meter_summ = (new Query())
            ->select('SUM(`cubic_meter`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime >= :from_date AND datetime <= :to_date',
                [
                    'from_date' => $period_from,
                    'to_date' => $period_to
                ]
            )
            ->andWhere('t.cubic_meter IS NOT NULL')
            ->scalar();
        $cubic_meter_count = (new Query())
            ->select('COUNT(`cubic_meter`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime >= :from_date AND datetime <= :to_date',
                [
                    'from_date' => $period_from,
                    'to_date' => $period_to
                ]
            )
            ->andWhere('t.cubic_meter IS NOT NULL')
            ->scalar();

        if ($cubic_meter_summ && $cubic_meter_count) {
            $consumption[self::CUBIC_METER] = $cubic_meter_summ / $cubic_meter_count;
        }

        //kilowatt
        $kilowatt_summ = (new Query())
            ->select('SUM(`kilowatt`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime >= :from_date AND datetime <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])
            ->andWhere('t.kilowatt IS NOT NULL')
            ->scalar();

        $kilowatt_count = (new Query())
            ->select('COUNT(`kilowatt`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime >= :from_date AND datetime <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])
            ->andWhere('t.kilowatt IS NOT NULL')
            ->scalar();

        if ($kilowatt_summ && $kilowatt_count ) {
            $consumption[self::KILOWATT] = $kilowatt_summ / $kilowatt_count;
        }

        //cubic meter hour
        $cubic_meter_hour_summ = (new Query())
            ->select('SUM(`cubic_meter_hour`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime >= :from_date AND datetime <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $to_date,
            ])
            ->andWhere('t.cubic_meter_hour IS NOT NULL')
            ->scalar();

        $cubic_meter_hour_count = (new Query())
            ->select('COUNT(`cubic_meter_hour`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime >= :from_date AND datetime <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $to_date,
            ])
            ->andWhere('t.cubic_meter_hour IS NOT NULL')
            ->scalar();

        if ($cubic_meter_hour_summ && $cubic_meter_hour_count)
        {
            $consumption[self::CUBIC_METER_HOUR] = $cubic_meter_hour_summ / $cubic_meter_hour_count;
        }

        //incoming temp
        $incoming_temp_summ = (new Query())
            ->select('SUM(`incoming_temp`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime >= :from_date AND datetime <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])
            ->andWhere('t.incoming_temp IS NOT NULL')
            ->scalar();
        $incoming_temp_count = (new Query())
            ->select('COUNT(`incoming_temp`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime >= :from_date AND datetime <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])
            ->andWhere('t.incoming_temp IS NOT NULL')
            ->scalar();
        if ($incoming_temp_summ && $incoming_temp_count)
        {
            $consumption[self::INCOMING_TEMP] = $incoming_temp_summ / $incoming_temp_count;
        }

        //outgoing temp
        $outgoing_temp_summ = (new Query())
            ->select('SUM(`outgoing_temp`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime >= :from_date AND datetime <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])
            ->andWhere('t.outgoing_temp IS NOT NULL')
            ->scalar();
        $outgoing_temp_count = (new Query())
            ->select('COUNT(`outgoing_temp`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('datetime >= :from_date AND datetime <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])
            ->andWhere('t.outgoing_temp IS NOT NULL')
            ->scalar();
        if ($outgoing_temp_summ && $outgoing_temp_count)
        {
            $consumption[self::OUTGOING_TEMP] = $outgoing_temp_summ / $outgoing_temp_count;
        }
        if ($with_season) {
            $from_date = Yii::$app->formatter->asTimestamp($from_date);
            $to_date = Yii::$app->formatter->asTimestamp($to_date);
            for ($i = $from_date; $i < $to_date; $i = $i + 3600) {
                $date = Yii::$app->formatter->asDate($i);
                $season = self::getSeason($i);
                $season_constant = self::getAliasSeasonAvgConstant($season);
                $data[$date] = [
                    self::KILOWATT_HOUR => Yii::$app->formatter->asRound($consumption[self::KILOWATT_HOUR] * $season_constant[self::KILOWATT_HOUR], 3),
                    self::CUBIC_METER => Yii::$app->formatter->asRound($consumption[self::CUBIC_METER] * $season_constant[self::CUBIC_METER], 3),
                    self::KILOWATT => Yii::$app->formatter->asRound($consumption[self::KILOWATT] * $season_constant[self::KILOWATT], 3),
                    self::CUBIC_METER_HOUR => Yii::$app->formatter->asRound($consumption[self::CUBIC_METER_HOUR] * $season_constant[self::CUBIC_METER_HOUR], 3),
                    self::INCOMING_TEMP => Yii::$app->formatter->asRound($consumption[self::INCOMING_TEMP] * $season_constant[self::INCOMING_TEMP], 3),
                    self::OUTGOING_TEMP => Yii::$app->formatter->asRound($consumption[self::OUTGOING_TEMP] * $season_constant[self::OUTGOING_TEMP], 3)
                ];
            }
        }
        else {
            for ($i = $fd; $i <= $td; $i = $i + 3600) {
                $date = Yii::$app->formatter->asDatetime($i);
                $data[$date] = [
                    self::KILOWATT_HOUR => Yii::$app->formatter->asRound($consumption[self::KILOWATT_HOUR] , 3),
                    self::CUBIC_METER => Yii::$app->formatter->asRound($consumption[self::CUBIC_METER], 3),
                    self::KILOWATT => Yii::$app->formatter->asRound($consumption[self::KILOWATT], 3),
                    self::CUBIC_METER_HOUR => Yii::$app->formatter->asRound($consumption[self::CUBIC_METER_HOUR], 3),
                    self::INCOMING_TEMP => Yii::$app->formatter->asRound($consumption[self::INCOMING_TEMP], 3),
                    self::OUTGOING_TEMP => Yii::$app->formatter->asRound($consumption[self::OUTGOING_TEMP], 3)
                ];
            }
        }
        return $data;
    }

    /* backup
    else {
            $kilowatt_hour_previous = (new Query())
                ->select('kilowatt_hour')
                ->from(self:: tableName() . ' t')
                ->andWhere([
                    't.meter_id' => $meter_id,
                    't.channel_id' => $channel_id,
                ])
                ->andWhere('datetime = :date', [
                    'date' => Yii::$app->formatter->asDateTime($fd - 3600, 'Y-MM-dd HH:m'),
                ])
                ->scalar();
            $cubic_meter_previous = (new Query())
                ->select('cubic_meter')
                ->from(self:: tableName() . ' t')
                ->andWhere([
                    't.meter_id' => $meter_id,
                    't.channel_id' => $channel_id,
                ])
                ->andWhere('datetime = :date', [
                    'date' => Yii::$app->formatter->asDateTime($fd - 3600, 'Y-MM-dd HH:m'),
                ])
                ->scalar();
            $kilowatt_previous = (new Query())
                ->select('kilowatt')
                ->from(self:: tableName() . ' t')
                ->andWhere([
                    't.meter_id' => $meter_id,
                    't.channel_id' => $channel_id,
                ])
                ->andWhere('datetime = :date', [
                    'date' => Yii::$app->formatter->asDateTime($fd - 3600, 'Y-MM-dd HH:m'),
                ])
                ->scalar();
            $cubic_meter_hour_previous = (new Query())
                ->select('cubic_meter_hour')
                ->from(self:: tableName() . ' t')
                ->andWhere([
                    't.meter_id' => $meter_id,
                    't.channel_id' => $channel_id,
                ])
                ->andWhere('datetime = :date', [
                    'date' => Yii::$app->formatter->asDateTime($fd - 3600, 'Y-MM-dd HH:m'),
                ])
                ->scalar();
            $incoming_temp_previous = (new Query())
                ->select('incoming_temp')
                ->from(self:: tableName() . ' t')
                ->andWhere([
                    't.meter_id' => $meter_id,
                    't.channel_id' => $channel_id,
                ])
                ->andWhere('datetime = :date', [
                    'date' => Yii::$app->formatter->asDateTime($fd - 3600, 'Y-MM-dd HH:m'),
                ])
                ->scalar();
            $outgoing_temp_previous = (new Query())
                ->select('outgoing_temp')
                ->from(self:: tableName() . ' t')
                ->andWhere([
                    't.meter_id' => $meter_id,
                    't.channel_id' => $channel_id,
                ])
                ->andWhere('datetime = :date', [
                    'date' => Yii::$app->formatter->asDateTime($fd - 3600, 'Y-MM-dd HH:m'),
                ])
                ->scalar();
            $date = Yii::$app->formatter->asDatetime($fd);
            $data[$date] = [
                self::KILOWATT_HOUR => Yii::$app->formatter->asRound($kilowatt_hour_previous + $consumption[self::KILOWATT_HOUR] , 3),
                self::CUBIC_METER => Yii::$app->formatter->asRound($cubic_meter_previous + $consumption[self::CUBIC_METER], 3),
                self::KILOWATT => Yii::$app->formatter->asRound($kilowatt_previous + $consumption[self::KILOWATT], 3),
                self::CUBIC_METER_HOUR => Yii::$app->formatter->asRound($cubic_meter_hour_previous + $consumption[self::CUBIC_METER_HOUR], 3),
                self::INCOMING_TEMP => Yii::$app->formatter->asRound($incoming_temp_previous + $consumption[self::INCOMING_TEMP], 3),
                self::OUTGOING_TEMP => Yii::$app->formatter->asRound($outgoing_temp_previous + $consumption[self::OUTGOING_TEMP], 3)
            ];
            for ($i = $fd + 3600; $i < $td; $i = $i + 3600) {
                $date = Yii::$app->formatter->asDatetime($i);
                $prev_date = Yii::$app->formatter->asDatetime($i - 3600);
                $cur_data = (new Query())
                    ->select('kilowatt_hour, cubic_meter, kilowatt, cubic_meter_hour, incoming_temp, outgoing_temp')
                    ->from(self::tableName() . ' t')
                    ->andWhere([
                        't.meter_id' => $meter_id,
                        't.channel_id' => $channel_id,
                    ])
                    ->andWhere('datetime = :date', [
                        'date' => Yii::$app->formatter->asDateTime($i, 'Y-MM-dd HH:m'),
                    ])->all();

                $data[$date] = [
                    self::KILOWATT_HOUR => (isset($cur_data[0]['kilowatt_hour']) ? Yii::$app->formatter->asRound($cur_data[0]['kilowatt_hour'] + $consumption[self::KILOWATT_HOUR] , 3) : Yii::$app->formatter->asRound($data[$prev_date][self::KILOWATT_HOUR] + $consumption[self::KILOWATT_HOUR] , 3)),
                    self::CUBIC_METER => (isset($cur_data[0]['cubic_meter']) ? Yii::$app->formatter->asRound($cur_data[0]['cubic_meter'] + $consumption[self::CUBIC_METER] , 3) : Yii::$app->formatter->asRound($data[$prev_date][self::CUBIC_METER] + $consumption[self::CUBIC_METER] , 3)),
                    self::KILOWATT => (isset($cur_data[0]['kilowatt']) ? Yii::$app->formatter->asRound($cur_data[0]['kilowatt'] + $consumption[self::KILOWATT] , 3) : Yii::$app->formatter->asRound($data[$prev_date][self::KILOWATT] + $consumption[self::KILOWATT] , 3)),
                    self::CUBIC_METER_HOUR => (isset($cur_data[0]['cubic_meter_hour']) ? Yii::$app->formatter->asRound($cur_data[0]['cubic_meter_hour'] + $consumption[self::CUBIC_METER_HOUR] , 3) : Yii::$app->formatter->asRound($data[$prev_date][self::CUBIC_METER_HOUR] + $consumption[self::CUBIC_METER_HOUR] , 3)),
                    self::INCOMING_TEMP => (isset($cur_data[0]['incoming_temp']) ? Yii::$app->formatter->asRound($cur_data[0]['incoming_temp'] + $consumption[self::INCOMING_TEMP] , 3) : Yii::$app->formatter->asRound($data[$prev_date][self::INCOMING_TEMP] + $consumption[self::INCOMING_TEMP] , 3)),
                    self::OUTGOING_TEMP => (isset($cur_data[0]['outgoing_temp']) ? Yii::$app->formatter->asRound($cur_data[0]['outgoing_temp'] + $consumption[self::OUTGOING_TEMP] , 3) : Yii::$app->formatter->asRound($data[$prev_date][self::OUTGOING_TEMP] + $consumption[self::OUTGOING_TEMP] , 3)),
                ];
            }
        }

        return $data;
    }
    */

    public static function getAliasSeasonAvgConstant($season) {
        $list = self::getListSeasonAvgConstants();
        return (isset($list[$season])) ? $list[$season] : [];
    }


    public static function getReadings($meter_name, $meter_channel_name, $date,
                                       $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT) {
        /** @var Carbon $date */
        if(!$data_usage_method || $data_usage_method == Meter::DATA_USAGE_METHOD_DEFAULT) {
            $data_usage_method = (new Query())
                ->select('t.data_usage_method')
                ->from(Meter::tableName() . ' t')
                ->andWhere(['t.name' => $meter_name])
                ->scalar();
        }
        $start_date = $date->startOfDay()->format(Formatter::STORAGE_DATE_TIME_FORMAT);
        $end_date = $date->endOfDay()->format(Formatter::STORAGE_DATE_TIME_FORMAT);
        $cache_key = "air_meter_raw_data:{$meter_name}_{$meter_channel_name}_{$data_usage_method}_{$date}";
        $readings = [
            'kilowatt_hour' => null,
            'cubic_meter' => null,
            'kilowatt' => null,
            'cubic_meter_hour' => null,
            'incoming_temp' => null,
            'outgoing_temp' => null,
        ];
        $cache_value = static::getCacheValue($cache_key);
        if($cache_value != null) {
            return $cache_value;
        }
        switch($data_usage_method) {
            case Meter::DATA_USAGE_METHOD_IMPORT :
                $query = (new Query())
                    ->select(['SUM(round(cubic_meter,4)) as cubic_meter',
                              'SUM(round(kilowatt,4)) as kilowatt', 'SUM(round(cubic_meter_hour,4)) as cubic_meter_hour',
                              'SUM(round(incoming_temp,4)) as incoming_temp', 'SUM(round(outgoing_temp,4)) as outgoing_temp'])
                    ->from(AirMeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("datetime >= :start_date and datetime < :end_date", [
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                    ]);
                $data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $query->select(['kilowatt_hour'])->orderBy(['datetime'=>SORT_DESC]);
                $kilowatt_hours = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryScalar();
                }, static::CACHE_DURATION);
                $readings = [
                    'kilowatt_hour' => $data['kilowatt_hour'] ?? null,
                    'cubic_meter' => $data['cubic_meter'] ?? null,
                    'kilowatt' => $data['kilowatt'] ?? null,
                    'cubic_meter_hour' => $data['cubic_meter_hour'] ?? null,
                    'incoming_temp' => $data['incoming_temp'] ?? null,
                    'outgoing_temp' => $data['outgoing_temp'] ?? null,
                ];
                break;
            default :
                throw new InvalidParamException('Data usage method is not supported');
        }
        return $readings;
    }


    public static function getPowerFactor($meter_name, array $meter_channel_names, $from_date, $to_date,
                                          $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT) {
        //TODO: Must be created
    }


    public static function getPowerFactorAdditionalPercent($power_factor, $rate_level = RateType::LEVEL_LOW) {
        //TODO: Must be created
    }
}