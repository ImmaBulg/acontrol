<?php

namespace common\models;

use common\models\helpers\reports\ReportGenerator;
use \DateTime;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use common\components\db\ActiveRecord;
use common\components\i18n\Formatter;
use common\components\behaviors\UserIdBehavior;
use common\components\behaviors\ToTimestampBehavior;
use yii\helpers\VarDumper;

/**
 * MeterRawData is the class for the table "meter_raw_data".
 */
class MeterRawData extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    const SEASON_WINTER = 'winter';
    const SEASON_SPRING = 'spring';
    const SEASON_SUMMER = 'summer';
    const SEASON_FALL = 'fall';

    const CONSUMPTION_SHEFEL = 'shefel';
    const CONSUMPTION_GEVA = 'geva';
    const CONSUMPTION_PISGA = 'pisga';
    const CONSUMPTION_EXPORT_SHEFEL = 'export_shefel';
    const CONSUMPTION_EXPORT_GEVA = 'export_geva';
    const CONSUMPTION_EXPORT_PISGA = 'export_pisga';

    const RULE_LAST_60_DAYS_OR_UP_UNTIL_SEASON_CHANGE = 1;
    const RULE_LAST_60_DAYS_WITHOUT_SEASONS = 2;
    const RULE_LAST_14_DAYS = 3;
    const RULE_SAME_PERIOD_LAST_YEAR = 4;


    public static function tableName() {
        return 'meter_raw_data';
    }


    public function rules() {
        return [
            [['meter_id', 'channel_id'], 'filter', 'filter' => 'strip_tags'],
            [['meter_id', 'channel_id'], 'filter', 'filter' => 'trim'],
            [['meter_id', 'channel_id'], 'required'],
            [['meter_id', 'channel_id'], 'string', 'max' => 255],
            [['shefel', 'geva', 'pisga'], 'number'],
            [['reading_shefel', 'reading_geva', 'reading_pisga'], 'number'],
            [['max_shefel', 'max_geva', 'max_pisga'], 'number'],
            [['export_shefel', 'export_geva', 'export_pisga'], 'number'],
            [['kvar_shefel', 'kvar_geva', 'kvar_pisga'], 'number'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
            [['date'], 'safe'],
        ];
    }


    public function attributeLabels() {
        return [
            'id' => Yii::t('common.meter', 'ID'),
            'meter_id' => Yii::t('common.meter', 'Meter ID'),
            'channel_id' => Yii::t('common.meter', 'Channel ID'),
            'date' => Yii::t('common.meter', 'Reading date'),
            'shefel' => Yii::t('common.meter', 'Shefel'),
            'geva' => Yii::t('common.meter', 'Geva'),
            'pisga' => Yii::t('common.meter', 'Pisga'),
            'reading_shefel' => Yii::t('common.meter', 'Reading shefel'),
            'reading_geva' => Yii::t('common.meter', 'Reading geva'),
            'reading_pisga' => Yii::t('common.meter', 'Reading pisga'),
            'max_shefel' => Yii::t('common.meter', 'Max shefel'),
            'max_geva' => Yii::t('common.meter', 'Max geva'),
            'max_pisga' => Yii::t('common.meter', 'Max pisga'),
            'export_shefel' => Yii::t('common.meter', 'Export shefel'),
            'export_geva' => Yii::t('common.meter', 'Export geva'),
            'export_pisga' => Yii::t('common.meter', 'Export pisga'),
            'kvar_shefel' => Yii::t('common.meter', 'KVAR shefel'),
            'kvar_geva' => Yii::t('common.meter', 'KVAR geva'),
            'kvar_pisga' => Yii::t('common.meter', 'KVAR pisga'),
            'status' => Yii::t('common.meter', 'Status'),
            'created_at' => Yii::t('common.meter', 'Created at'),
            'modified_at' => Yii::t('common.meter', 'Modified at'),
            'created_by' => Yii::t('common.meter', 'Created by'),
            'modified_by' => Yii::t('common.meter', 'Modified by'),
        ];
    }


    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'modified_at',
            ],
            [
                'class' => UserIdBehavior::className(),
                'createdByAttribute' => 'created_by',
                'modifiedByAttribute' => 'modified_by',
            ],
            [
                'class' => ToTimestampBehavior::className(),
                'attributes' => [
                    'date',
                ],
            ],
        ];
    }


    public static function getMaxConsumptionWithinDateRangeQuery($from_date, $to_date, $meter_name, $subchannels_ids) {
        /**
         * @var $meter Meter
         */
        $meter = Meter::find()->where(['name' => $meter_name])->one();
        $query = (new Query())
            ->from(MeterRawData::tableName() . ' t')
            ->andWhere(['t.meter_id' => $meter_name])
            ->andWhere(['in', 't.channel_id', $subchannels_ids])
            ->andWhere('t.date > :from_date AND t.date <= :to_date', [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ]);
        if($meter->relationMeterType->is_summarize_max_demand) {
            $query->select([
                               'GREATEST(SUM(`t`.`max_shefel`),SUM(`t`.`max_geva`),SUM(`t`.`max_pisga`)) as max_demand',
                           ])->groupBy('date')->orderBy(['max_demand'=>SORT_DESC]);
        }
        else {
            $query->select([
                               'MAX(GREATEST(`t`.`max_shefel`,`t`.`max_geva`,`t`.`max_pisga`))',
                           ]);
        }
        return $query;
    }


    public static function getMaxConsumptionWithinDateRange($from_date, $to_date, $meter_id, $subchannels_ids) {
        $query = self::getMaxConsumptionWithinDateRangeQuery($from_date, $to_date, $meter_id, $subchannels_ids);
        $max_consumption = Yii::$app->db->cache(function ($db) use ($query) {
            return $query->scalar();
        }, ReportGenerator::CACHE_DURATION);
        return $max_consumption;
    }


    public function getRelationMeter() {
        return $this->hasOne(Meter::className(), ['name' => 'meter_id']);
    }


    public function getRelationMeterChannel() {
        return $this->hasOne(MeterChannel::className(), ['channel' => 'channel_id']);
    }


    public static function getListStatuses() {
        return [
            self::STATUS_INACTIVE => Yii::t('common.meter', 'Inactive'),
            self::STATUS_ACTIVE => Yii::t('common.meter', 'Active'),
        ];
    }


    public function getAliasStatus() {
        $list = self::getListStatuses();
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }


    public static function getSeason($date) {
        $date = new DateTime('@' . Yii::$app->formatter->asTimestamp($date));
        $month = $date->format('n');
        if($month >= 3 && $month <= 5) {
            return self::SEASON_SPRING;
        }
        else {
            if($month >= 6 && $month <= 8) {
                return self::SEASON_SUMMER;
            }
            else {
                if($month >= 9 && $month <= 11) {
                    return self::SEASON_FALL;
                }
                else {
                    return self::SEASON_WINTER;
                }
            }
        }
    }


    public static function getListSeasonAvgConstants() {
        return [
            self::SEASON_WINTER => [
                self::CONSUMPTION_SHEFEL => 0.66,
                self::CONSUMPTION_GEVA => 0.08,
                self::CONSUMPTION_PISGA => 0.25,
                self::CONSUMPTION_EXPORT_SHEFEL => 0.66,
                self::CONSUMPTION_EXPORT_GEVA => 0.08,
                self::CONSUMPTION_EXPORT_PISGA => 0.25,
            ],
            self::SEASON_SPRING => [
                self::CONSUMPTION_SHEFEL => 0.34,
                self::CONSUMPTION_GEVA => 0.08,
                self::CONSUMPTION_PISGA => 0.58,
                self::CONSUMPTION_EXPORT_SHEFEL => 0.34,
                self::CONSUMPTION_EXPORT_GEVA => 0.08,
                self::CONSUMPTION_EXPORT_PISGA => 0.58,
            ],
            self::SEASON_SUMMER => [
                self::CONSUMPTION_SHEFEL => 0.42,
                self::CONSUMPTION_GEVA => 0.28,
                self::CONSUMPTION_PISGA => 0.30,
                self::CONSUMPTION_EXPORT_SHEFEL => 0.42,
                self::CONSUMPTION_EXPORT_GEVA => 0.28,
                self::CONSUMPTION_EXPORT_PISGA => 0.30,
            ],
            self::SEASON_FALL => [
                self::CONSUMPTION_SHEFEL => 0.34,
                self::CONSUMPTION_GEVA => 0.08,
                self::CONSUMPTION_PISGA => 0.58,
                self::CONSUMPTION_EXPORT_SHEFEL => 0.34,
                self::CONSUMPTION_EXPORT_GEVA => 0.08,
                self::CONSUMPTION_EXPORT_PISGA => 0.58,
            ],
        ];
    }


    public static function getAliasSeasonAvgConstant($season) {
        $list = self::getListSeasonAvgConstants();
        return (isset($list[$season])) ? $list[$season] : [];
    }


    public static function getListRulePeriods() {
        return [
            self::RULE_LAST_60_DAYS_OR_UP_UNTIL_SEASON_CHANGE => Yii::t('common.meter',
                                                                        'Last 60 days or up until season change'),
            self::RULE_LAST_60_DAYS_WITHOUT_SEASONS => Yii::t('common.meter', 'Last 60 days without seasons'),
            self::RULE_LAST_14_DAYS => Yii::t('common.meter', 'Last 14 days'),
            self::RULE_SAME_PERIOD_LAST_YEAR => Yii::t('common.meter', 'Same period last year'),
        ];
    }


    public static function getRulePeriodRange($rule, $from_date, $to_date) {
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


    /**
     * Generate autocomplete AVG data for consumption based on period
     * @param string $meter_id
     * @param string $channel_id
     * @param string|integer $from_date
     * @param string|integer $to_date
     */
    public static function getAvgData($meter_id, $channel_id, $period_from, $period_to, $from_date, $to_date,
                                      $with_season = false)
    {
        $data = [];
        $from_date = Yii::$app->formatter->asTimestamp($from_date);
        $to_date = Yii::$app->formatter->asTimestamp($to_date);
        $period_from = Yii::$app->formatter->asTimestamp($period_from);
        $period_to = Yii::$app->formatter->asTimestamp($period_to);
        $consumption = [
            self::CONSUMPTION_SHEFEL => 0,
            self::CONSUMPTION_GEVA => 0,
            self::CONSUMPTION_PISGA => 0,
        ];

        $shefel_from_date = (new Query())
            ->select('reading_shefel')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('date = :from_date',[
                'from_date' => $period_from,
            ])
            ->scalar();
        $shefel_to_date = (new Query())
            ->select('reading_shefel')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('date = :to_date',[
                'to_date' => $period_to,
            ])
            ->scalar();
        $shefel_count = (new Query())
            ->select('COUNT(`reading_shefel`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])
            ->scalar();
        if ($shefel_count)
            $consumption[self::CONSUMPTION_SHEFEL] = ($shefel_to_date - $shefel_from_date) / $shefel_count;

        $geva_from_date = (new Query())
            ->select('reading_geva')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('date = :from_date', [
                'from_date' => $period_from,
            ])
            ->scalar();
        $geva_to_date = (new Query())
            ->select('reading_geva')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('date = :to_date', [
                'to_date' => $period_to,
            ])
            ->scalar();
        $geva_count = (new Query())
            ->select('COUNT(`reading_geva`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])
            ->scalar();
        if ($geva_count)
            $consumption[self::CONSUMPTION_GEVA] = ($geva_to_date - $geva_from_date) / $geva_count;

        $pisga_from_date = (new Query())
            ->select('reading_pisga')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('date = :from_date', [
                'from_date' => $period_from,
            ])
            ->scalar();
        $pisga_to_date = (new Query())
            ->select('reading_pisga')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('date = :to_date', [
                'to_date' => $period_to,
            ])
            ->scalar();
        $pisga_count = (new Query())
            ->select('COUNT(`reading_pisga`)')
            ->from(self::tableName() . ' t')
            ->andWhere([
                't.meter_id' => $meter_id,
                't.channel_id' => $channel_id,
            ])
            ->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])
            ->scalar();
        if ($pisga_count)
            $consumption[self::CONSUMPTION_PISGA] = ($pisga_to_date - $pisga_from_date) / $pisga_count;

        for ($i = $from_date; $i <= $to_date; $i += 3600)
        {
            $date = Yii::$app->formatter->asDatetime($i);
            $data[$date] = [
                self::CONSUMPTION_SHEFEL => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_SHEFEL], 3),
                self::CONSUMPTION_GEVA => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_GEVA], 3),
                self::CONSUMPTION_PISGA => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_PISGA], 3),
            ];
        }

        return $data;
    }

   /* public static function getAvgData($meter_id, $channel_id, $period_from, $period_to, $from_date, $to_date,
                                      $with_season = false) {
        $data = [];
        $from_date = Yii::$app->formatter->asTimestamp($from_date);
        $to_date = Yii::$app->formatter->asTimestamp($to_date);
        $period_from = Yii::$app->formatter->asTimestamp($period_from);
        $period_to = Yii::$app->formatter->asTimestamp($period_to);
        $consumption = [
            self::CONSUMPTION_SHEFEL => 0,
            self::CONSUMPTION_GEVA => 0,
            self::CONSUMPTION_PISGA => 0,
            self::CONSUMPTION_EXPORT_SHEFEL => 0,
            self::CONSUMPTION_EXPORT_GEVA => 0,
            self::CONSUMPTION_EXPORT_PISGA => 0,
        ];
        /**
         * Shefel

        $shefel_min = (new Query())
            ->select('MIN(IFNULL(`shefel`, `reading_shefel`))')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.shefel IS NOT NULL OR t.reading_shefel IS NOT NULL')->scalar();
        $shefel_max = (new Query())
            ->select('MAX(IFNULL(`shefel`, `reading_shefel`))')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.shefel IS NOT NULL OR t.reading_shefel IS NOT NULL')->scalar();
        if($shefel_min && $shefel_max && ($shefel_max != $shefel_min)) {
            $shefel_min_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere([
                               'or',
                               ['shefel' => $shefel_min],
                               ['reading_shefel' => $shefel_min],
                           ])
                ->andWhere('t.shefel IS NOT NULL OR t.reading_shefel IS NOT NULL')->scalar();
            $shefel_max_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere([
                               'or',
                               ['shefel' => $shefel_max],
                               ['reading_shefel' => $shefel_max],
                           ])
                ->andWhere('t.shefel IS NOT NULL OR t.reading_shefel IS NOT NULL')->scalar();
            $shefel_count = round(($shefel_max_date - $shefel_min_date) / 86400);
            $consumption[self::CONSUMPTION_SHEFEL] = ($shefel_max - $shefel_min) / $shefel_count;
        }
        /**
         * Export Shefel

        $export_shefel_min = (new Query())
            ->select('MIN(`export_shefel`)')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.export_shefel IS NOT NULL')->scalar();
        $export_shefel_max = (new Query())
            ->select('MAX(`export_shefel`)')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.export_shefel IS NOT NULL')->scalar();
        if($export_shefel_min && $export_shefel_max && ($export_shefel_max != $export_shefel_min)) {
            $export_shefel_min_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere(['export_shefel' => $export_shefel_min])
                ->andWhere('t.export_shefel IS NOT NULL')->scalar();
            $export_shefel_max_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere(['export_shefel' => $export_shefel_max])
                ->andWhere('t.export_shefel IS NOT NULL')->scalar();
            $export_shefel_count = round(($export_shefel_max_date - $export_shefel_min_date) / 86400);
            $consumption[self::CONSUMPTION_EXPORT_SHEFEL] =
                ($export_shefel_max - $export_shefel_min) / $export_shefel_count;
        }
        /**
         * Geva

        $geva_min = (new Query())
            ->select('MIN(IFNULL(`geva`, `reading_geva`))')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.geva IS NOT NULL OR t.reading_geva IS NOT NULL')->scalar();
        $geva_max = (new Query())
            ->select('MAX(IFNULL(`geva`, `reading_geva`))')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.geva IS NOT NULL OR t.reading_geva IS NOT NULL')->scalar();
        $geva_count = (new Query())->from(self::tableName() . ' t')->andWhere([
                                                                                  't.meter_id' => $meter_id,
                                                                                  't.channel_id' => $channel_id,
                                                                              ])
                                   ->andWhere('date >= :from_date AND date <= :to_date', [
                                       'from_date' => $period_from,
                                       'to_date' => $period_to,
                                   ])->andWhere('t.geva IS NOT NULL OR t.reading_geva IS NOT NULL')->count();
        if($geva_min && $geva_max && ($geva_max != $geva_min)) {
            $geva_min_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere([
                               'or',
                               ['geva' => $geva_min],
                               ['reading_geva' => $geva_min],
                           ])
                ->andWhere('t.geva IS NOT NULL OR t.reading_geva IS NOT NULL')->scalar();
            $geva_max_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere([
                               'or',
                               ['geva' => $geva_max],
                               ['reading_geva' => $geva_max],
                           ])
                ->andWhere('t.geva IS NOT NULL OR t.reading_geva IS NOT NULL')->scalar();
            $geva_count = round(($geva_max_date - $geva_min_date) / 86400);
            $consumption[self::CONSUMPTION_GEVA] = ($geva_max - $geva_min) / $geva_count;
        }
        /**
         * Export Geva

        $export_geva_min = (new Query())
            ->select('MIN(`export_geva`)')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.export_geva IS NOT NULL')->scalar();
        $export_geva_max = (new Query())
            ->select('MAX(`export_geva`)')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.export_geva IS NOT NULL')->scalar();
        if($export_geva_min && $export_geva_max && ($export_geva_max != $export_geva_min)) {
            $export_geva_min_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere(['export_geva' => $export_geva_min])
                ->andWhere('t.export_geva IS NOT NULL')->scalar();
            $export_geva_max_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere(['export_geva' => $export_geva_max])
                ->andWhere('t.export_geva IS NOT NULL')->scalar();
            $export_geva_count = round(($export_geva_max_date - $export_geva_min_date) / 86400);
            $consumption[self::CONSUMPTION_EXPORT_GEVA] = ($export_geva_max - $export_geva_min) / $export_geva_count;
        }
        /**
         * Pisga

        $pisga_min = (new Query())
            ->select('MIN(IFNULL(`pisga`, `reading_pisga`))')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.pisga IS NOT NULL OR t.reading_pisga IS NOT NULL')->scalar();
        $pisga_max = (new Query())
            ->select('MAX(IFNULL(`pisga`, `reading_pisga`))')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.pisga IS NOT NULL OR t.reading_pisga IS NOT NULL')->scalar();
        $pisga_count = (new Query())->from(self::tableName() . ' t')->andWhere([
                                                                                   't.meter_id' => $meter_id,
                                                                                   't.channel_id' => $channel_id,
                                                                               ])
                                    ->andWhere('date >= :from_date AND date <= :to_date', [
                                        'from_date' => $period_from,
                                        'to_date' => $period_to,
                                    ])->andWhere('t.pisga IS NOT NULL OR t.reading_pisga IS NOT NULL')->count();
        if($pisga_min && $pisga_max && ($pisga_max != $pisga_min)) {
            $pisga_min_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere([
                               'or',
                               ['pisga' => $pisga_min],
                               ['reading_pisga' => $pisga_min],
                           ])
                ->andWhere('t.pisga IS NOT NULL OR t.reading_pisga IS NOT NULL')->scalar();
            $pisga_max_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere([
                               'or',
                               ['pisga' => $pisga_max],
                               ['reading_pisga' => $pisga_max],
                           ])
                ->andWhere('t.pisga IS NOT NULL OR t.reading_pisga IS NOT NULL')->scalar();
            $pisga_count = round(($pisga_max_date - $pisga_min_date) / 86400);
            $consumption[self::CONSUMPTION_PISGA] = ($pisga_max - $pisga_min) / $pisga_count;
        }
        /**
         * Export Pisga

        $export_pisga_min = (new Query())
            ->select('MIN(`export_pisga`)')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.export_pisga IS NOT NULL')->scalar();
        $export_pisga_max = (new Query())
            ->select('MAX(`export_pisga`)')
            ->from(self::tableName() . ' t')->andWhere([
                                                           't.meter_id' => $meter_id,
                                                           't.channel_id' => $channel_id,
                                                       ])->andWhere('date >= :from_date AND date <= :to_date', [
                'from_date' => $period_from,
                'to_date' => $period_to,
            ])->andWhere('t.export_pisga IS NOT NULL')->scalar();
        if($export_pisga_min && $export_pisga_max && ($export_pisga_max != $export_pisga_min)) {
            $export_pisga_min_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere(['export_pisga' => $export_pisga_min])
                ->andWhere('t.export_pisga IS NOT NULL')->scalar();
            $export_pisga_max_date = (new Query())
                ->select('t.date')
                ->from(self::tableName() . ' t')->andWhere([
                                                               't.meter_id' => $meter_id,
                                                               't.channel_id' => $channel_id,
                                                           ])->andWhere('date >= :from_date AND date <= :to_date', [
                    'from_date' => $period_from,
                    'to_date' => $period_to,
                ])
                ->andWhere(['export_pisga' => $export_pisga_max])
                ->andWhere('t.export_pisga IS NOT NULL')->scalar();
            $export_pisga_count = round(($export_pisga_max_date - $export_pisga_min_date) / 86400);
            $consumption[self::CONSUMPTION_EXPORT_PISGA] =
                ($export_pisga_max - $export_pisga_min) / $export_pisga_count;
        }
        if($with_season) {
            for($i = $from_date; $i < $to_date; $i = $i + 86400) {
                $date = Yii::$app->formatter->asDate($i);
                $season = self::getSeason($i);
                $season_constant = self::getAliasSeasonAvgConstant($season);
                $data[$date] = [
                    self::CONSUMPTION_SHEFEL => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_SHEFEL] *
                                                                              $season_constant[self::CONSUMPTION_SHEFEL],
                                                                              3),
                    self::CONSUMPTION_GEVA => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_GEVA] *
                                                                            $season_constant[self::CONSUMPTION_GEVA],
                                                                            3),
                    self::CONSUMPTION_PISGA => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_PISGA] *
                                                                             $season_constant[self::CONSUMPTION_PISGA],
                                                                             3),
                    self::CONSUMPTION_EXPORT_SHEFEL => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_EXPORT_SHEFEL] *
                                                                                     $season_constant[self::CONSUMPTION_EXPORT_SHEFEL],
                                                                                     3),
                    self::CONSUMPTION_EXPORT_GEVA => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_EXPORT_GEVA] *
                                                                                   $season_constant[self::CONSUMPTION_EXPORT_GEVA],
                                                                                   3),
                    self::CONSUMPTION_EXPORT_PISGA => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_EXPORT_PISGA] *
                                                                                    $season_constant[self::CONSUMPTION_EXPORT_PISGA],
                                                                                    3),
                ];
            }
        }
        else {
            for($i = $from_date; $i < $to_date; $i = $i + 86400) {
                $date = Yii::$app->formatter->asDate($i);
                $data[$date] = [
                    self::CONSUMPTION_SHEFEL => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_SHEFEL],
                                                                              3),
                    self::CONSUMPTION_GEVA => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_GEVA], 3),
                    self::CONSUMPTION_PISGA => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_PISGA], 3),
                    self::CONSUMPTION_EXPORT_SHEFEL => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_EXPORT_SHEFEL],
                                                                                     3),
                    self::CONSUMPTION_EXPORT_GEVA => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_EXPORT_GEVA],
                                                                                   3),
                    self::CONSUMPTION_EXPORT_PISGA => Yii::$app->formatter->asRound($consumption[self::CONSUMPTION_EXPORT_PISGA],
                                                                                    3),
                ];
            }
        }
        return $data;
    }*/


    public static function getReadings($meter_name, $meter_channel_name, $date,
                                       $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT) {
        if(!$data_usage_method || $data_usage_method == Meter::DATA_USAGE_METHOD_DEFAULT) {
            $data_usage_method = (new Query())
                ->select('t.data_usage_method')
                ->from(Meter::tableName() . ' t')
                ->andWhere(['t.name' => $meter_name])
                ->scalar();
        }
        $date = Yii::$app->formatter->asDate($date, Formatter::PHP_DATE_FORMAT);
        $cache_key = "meter_raw_data:{$meter_name}_{$meter_channel_name}_{$data_usage_method}_{$date}";
        $cache_tags = [
            "meter_raw_data",
            "meter_raw_data:{$meter_name}",
            "meter_raw_data:{$meter_name}_{$meter_channel_name}",
            "meter_raw_data:{$meter_name}_{$meter_channel_name}_{$data_usage_method}",
        ];
        $cache_value = static::getCacheValue($cache_key);
        if($cache_value != null) {
            return $cache_value;
        }
        $sql_date_format = Formatter::SQL_DATE_FORMAT;
        switch($data_usage_method) {
            case Meter::DATA_USAGE_METHOD_IMPORT_PLUS_EXPORT:
                $query = (new Query())
                    ->select('IFNULL(`shefel`, `reading_shefel`) as shefel, IFNULL(`geva`, `reading_geva`) as geva, IFNULL(`pisga`, `reading_pisga`) as pisga')
                    ->from(MeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                        'date' => $date,
                    ]);
                $import_data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $query = (new Query())
                    ->select('export_shefel as shefel, export_geva as geva, export_pisga as pisga')
                    ->from(MeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                        'date' => $date,
                    ]);
                $export_data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $query = (new Query())
                    ->select('kvar_shefel, kvar_shefel, kvar_pisga')
                    ->from(MeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                        'date' => $date,
                    ]);
                $kvar_data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $readings = [
                    'shefel' => (ArrayHelper::getValue($import_data, 'shefel', null) !== null ||
                                 ArrayHelper::getValue($import_data, 'shefel', null) !== null) ?
                        ArrayHelper::getValue($import_data, 'shefel', null) +
                        ArrayHelper::getValue($export_data, 'shefel', null) : null,
                    'geva' => (ArrayHelper::getValue($import_data, 'geva', null) !== null ||
                               ArrayHelper::getValue($import_data, 'geva', null) !== null) ?
                        ArrayHelper::getValue($import_data, 'geva', null) +
                        ArrayHelper::getValue($export_data, 'geva', null) : null,
                    'pisga' => (ArrayHelper::getValue($import_data, 'pisga', null) !== null ||
                                ArrayHelper::getValue($import_data, 'pisga', null) !== null) ?
                        ArrayHelper::getValue($import_data, 'pisga', null) +
                        ArrayHelper::getValue($export_data, 'pisga', null) : null,
                    'kvar_shefel' => (ArrayHelper::getValue($kvar_data, 'kvar_shefel', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_shefel', null) : null,
                    'kvar_geva' => (ArrayHelper::getValue($kvar_data, 'kvar_geva', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_geva', null) : null,
                    'kvar_pisga' => (ArrayHelper::getValue($kvar_data, 'kvar_pisga', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_pisga', null) : null,
                ];
                break;
            case Meter::DATA_USAGE_METHOD_IMPORT_MINUS_EXPORT:
                $query = (new Query())
                    ->select('IFNULL(`shefel`, `reading_shefel`) as shefel, IFNULL(`geva`, `reading_geva`) as geva, IFNULL(`pisga`, `reading_pisga`) as pisga')
                    ->from(MeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                        'date' => $date,
                    ]);
                $import_data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $query = (new Query())
                    ->select('export_shefel as shefel, export_geva as geva, export_pisga as pisga')
                    ->from(MeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                        'date' => $date,
                    ]);
                $export_data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $query = (new Query())
                    ->select('kvar_shefel, kvar_shefel, kvar_pisga')
                    ->from(MeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                        'date' => $date,
                    ]);
                $kvar_data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $readings = [
                    'shefel' => (ArrayHelper::getValue($import_data, 'shefel', null) !== null ||
                                 ArrayHelper::getValue($import_data, 'shefel', null) !== null) ?
                        ArrayHelper::getValue($import_data, 'shefel', null) -
                        ArrayHelper::getValue($export_data, 'shefel', null) : null,
                    'geva' => (ArrayHelper::getValue($import_data, 'geva', null) !== null ||
                               ArrayHelper::getValue($import_data, 'geva', null) !== null) ?
                        ArrayHelper::getValue($import_data, 'geva', null) -
                        ArrayHelper::getValue($export_data, 'geva', null) : null,
                    'pisga' => (ArrayHelper::getValue($import_data, 'pisga', null) !== null ||
                                ArrayHelper::getValue($import_data, 'pisga', null) !== null) ?
                        ArrayHelper::getValue($import_data, 'pisga', null) -
                        ArrayHelper::getValue($export_data, 'pisga', null) : null,
                    'kvar_shefel' => (ArrayHelper::getValue($kvar_data, 'kvar_shefel', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_shefel', null) : null,
                    'kvar_geva' => (ArrayHelper::getValue($kvar_data, 'kvar_geva', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_geva', null) : null,
                    'kvar_pisga' => (ArrayHelper::getValue($kvar_data, 'kvar_pisga', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_pisga', null) : null,
                ];
                break;
            case Meter::DATA_USAGE_METHOD_EXPORT:
                $query = (new Query())
                    ->select('export_shefel as shefel, export_geva as geva, export_pisga as pisga')
                    ->from(MeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                        'date' => $date,
                    ]);
                $export_data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $query = (new Query())
                    ->select('kvar_shefel, kvar_shefel, kvar_pisga')
                    ->from(MeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                        'date' => $date,
                    ]);
                $kvar_data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $readings = [
                    'shefel' => ArrayHelper::getValue($export_data, 'shefel', null),
                    'geva' => ArrayHelper::getValue($export_data, 'geva', null),
                    'pisga' => ArrayHelper::getValue($export_data, 'pisga', null),
                    'kvar_shefel' => (ArrayHelper::getValue($kvar_data, 'kvar_shefel', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_shefel', null) : null,
                    'kvar_geva' => (ArrayHelper::getValue($kvar_data, 'kvar_geva', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_geva', null) : null,
                    'kvar_pisga' => (ArrayHelper::getValue($kvar_data, 'kvar_pisga', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_pisga', null) : null,
                ];
                break;
            case Meter::DATA_USAGE_METHOD_IMPORT:
            default:
                $query = (new Query())
                    ->select('IFNULL(`shefel`, `reading_shefel`) as shefel, IFNULL(`geva`, `reading_geva`) as geva, IFNULL(`pisga`, `reading_pisga`) as pisga')
                    ->from(MeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                        'date' => $date,
                    ]);
                $import_data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $query = (new Query())
                    ->select('kvar_shefel, kvar_shefel, kvar_pisga')
                    ->from(MeterRawData::tableName() . ' t')
                    ->andWhere([
                                   't.meter_id' => $meter_name,
                                   't.channel_id' => $meter_channel_name,
                               ])
                    ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                        'date' => $date,
                    ]);
                $kvar_data = Yii::$app->db->cache(function ($db) use ($query) {
                    return $query->createCommand($db)->queryOne();
                }, static::CACHE_DURATION);
                $readings = [
                    'shefel' => ArrayHelper::getValue($import_data, 'shefel', null),
                    'geva' => ArrayHelper::getValue($import_data, 'geva', null),
                    'pisga' => ArrayHelper::getValue($import_data, 'pisga', null),
                    'kvar_shefel' => (ArrayHelper::getValue($kvar_data, 'kvar_shefel', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_shefel', null) : null,
                    'kvar_geva' => (ArrayHelper::getValue($kvar_data, 'kvar_geva', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_geva', null) : null,
                    'kvar_pisga' => (ArrayHelper::getValue($kvar_data, 'kvar_pisga', null) !== null) ?
                        ArrayHelper::getValue($kvar_data, 'kvar_pisga', null) : null,
                ];
                break;
        }
        static::setCacheValue($cache_key, $readings, $cache_tags);
        return $readings;
    }


    public static function getKvarReadings($meter_name, $subchannel, $date) {
        $sql_date_format = Formatter::SQL_DATE_FORMAT;
        $query = (new Query())
            ->select(['kvar_shefel', 'kvar_geva', 'kvar_pisga'])
            ->from(MeterRawData::tableName() . ' t')
            ->andWhere([
                           't.meter_id' => $meter_name,
                           't.channel_id' => $subchannel,
                       ])
            ->andWhere("DATE_FORMAT(FROM_UNIXTIME(t.date), '$sql_date_format') = :date", [
                'date' => Yii::$app->formatter->asDate($date, Formatter::PHP_DATE_FORMAT),
            ]);
        $kvar_reading = Yii::$app->db->cache(function ($db) use ($query) {
            return $query->createCommand($db)->queryOne();
        }, static::CACHE_DURATION);
        return $kvar_reading;
    }


    public static function getPowerFactor($meter_name, array $meter_channel_names, $from_date, $to_date,
                                          $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT) {
        $readings_from = 0;
        $readings_to = 0;
        $kvar_from = 0;
        $kvar_to = 0;
        foreach($meter_channel_names as $meter_channel_name) {
            $meter_readings_from =
                static::getReadings($meter_name, $meter_channel_name, $from_date, $data_usage_method);
            $meter_readings_to = static::getReadings($meter_name, $meter_channel_name, $to_date, $data_usage_method);
            $meter_kvar_from = self::getKvarReadings($meter_name, $meter_channel_name, $from_date);
            $meter_kvar_to = self::getKvarReadings($meter_name, $meter_channel_name, $to_date);
            unset($meter_readings_to['kvar_shefel']);
            unset($meter_readings_to['kvar_geva']);
            unset($meter_readings_to['kvar_pisga']);
            unset($meter_readings_from['kvar_shefel']);
            unset($meter_readings_from['kvar_geva']);
            unset($meter_readings_from['kvar_pisga']);
            $readings_from += array_sum((array)$meter_readings_from);
            $readings_to += array_sum((array)$meter_readings_to);
            $kvar_from += array_sum((array)$meter_kvar_from);
            $kvar_to += array_sum((array)$meter_kvar_to);
        }
        $readings_diff = $readings_to - $readings_from;
        $kvar_diff = $kvar_to - $kvar_from;
        $power_factor = self::calculatePowerFactor($readings_diff, $kvar_diff);
        return $power_factor;
    }


    public static function calculatePowerFactor($readings_diff, $kvar_diff) {
        $sqrt = sqrt(pow(($readings_diff), 2) + pow(($kvar_diff), 2));
        if($sqrt > 0) {
            $power_factor = $readings_diff / $sqrt;
        }
        else $power_factor = 0;
        return $power_factor;
    }


    public static function getPowerFactorAdditionalPercent($power_factor, $rate_level = RateType::LEVEL_LOW) {
        $value = 0;
        $ranges = [
            RateType::LEVEL_LOW => [
                [
                    'min_range' => 0,
                    'max_range' => 0.7,
                    'percent' => 0.15,
                ],
                [
                    'min_range' => 0.7,
                    'max_range' => 0.8,
                    'percent' => 0.125,
                ],
                [
                    'min_range' => 0.8,
                    'max_range' => 0.92,
                    'percent' => 0.1,
                ],
            ],
            RateType::LEVEL_HIGH => [
                [
                    'min_range' => 0,
                    'max_range' => 0.68,
                    'percent' => 0.15,
                ],
                [
                    'min_range' => 0.68,
                    'max_range' => 0.78,
                    'percent' => 0.125,
                ],
                [
                    'min_range' => 0.78,
                    'max_range' => 0.9,
                    'percent' => 0.1,
                ],
            ],
            RateType::LEVEL_SUPREME => [
                [
                    'min_range' => 0,
                    'max_range' => 0.65,
                    'percent' => 0.15,
                ],
                [
                    'min_range' => 0.65,
                    'max_range' => 0.75,
                    'percent' => 0.125,
                ],
                [
                    'min_range' => 0.75,
                    'max_range' => 0.87,
                    'percent' => 0.1,
                ],
            ],
        ];
        $ranges_max_value = [
            RateType::LEVEL_LOW => 0.92,
            RateType::LEVEL_HIGH => 0.90,
            RateType::LEVEL_SUPREME => 0.87,
        ];
        if(($rate_ranges = ArrayHelper::getValue($ranges, $rate_level)) != null) {
            foreach($rate_ranges as $rate_range) {
                if($power_factor >= $rate_range['min_range'] && $power_factor < $rate_range['max_range']) {
                    $value = ((ArrayHelper::getValue($ranges_max_value, $rate_level, 0) - $power_factor) / 0.001) *
                             $rate_range['percent'];
                    break;
                }
            }
        }
        return $value;
    }
}
