<?php

namespace common\models\helpers\reports;

use Carbon\Carbon;
use common\components\i18n\Formatter;
use common\helpers\TimeManipulator;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelGroup;
use common\models\MeterChannelGroupItem;
use common\models\ElectricityMeterRawData;
use common\models\RuleSingleChannel;
use common\models\RuleGroupLoad;
use common\models\MeterChannelMultiplier;
use common\models\MeterSubchannel;
use common\models\Rate;
use common\models\RateType;
use common\models\RuleFixedLoad;
use common\models\SiteBillingSetting;
use common\models\Tenant;
use common\models\TenantGroup;
use common\models\TenantGroupItem;
use common\widgets\Alert;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

abstract class ReportGenerator implements IReportGenerator
{
    protected static $_errors = [];
    public static $data_usage_method;

    const CACHE_DURATION = 5;

    const POWER_FACTOR_SHOW_DONT_ADD_FUNDS = 3;
    const POWER_FACTOR_SHOW_ADD_FUNDS = 2;
    const POWER_FACTOR_DONT_SHOW = 1;

    const ORDER_BY_METER = 'meter';
    const ORDER_BY_TENANT = 'tenant';

    const RULE_SINGLE_CHANNEL = 'rule_single_channel';
    const RULE_GROUP_LOAD = 'rule_group_load';
    const RULE_FIXED_LOAD = 'rule_fixed_load';
    const _24_HOURS = 86400;

    private $data = [];
    private static $day_before_from_date = null;
    private static $ending_of_to_date = null;


    public static function checkForNegative($rule, $tenant, $site, $from_date, $to_date) {
        if($rule['total_bill_action'] == RuleSingleChannel::TOTAL_BILL_ACTION_PLUS &&
           (['shefel'] < 0 || $rule['geva'] < 0 || $rule['pisga'] < 0)
        ) {
            self::addTotalConsumptionIsNegativeError($tenant, $site, $from_date, $to_date);
        }
    }


    public static function addError($message, $type = Alert::ALERT_DANGER) {
        static::$_errors[] = [
            'type' => $type,
            'message' => $message,
        ];
    }


    public function addTotalConsumptionIsNegativeError($tenant, $site, $from_date, $to_date) {
        self::addError(Yii::t('common.report',
                              'The total consumption of the first rule is negative for tenant {tenant} of site {site} between dates {dates}',
                              [
                                  'tenant' => $tenant->name,
                                  'site' => $site->name,
                                  'dates' => implode(' - ', [Yii::$app->formatter->asDate($from_date),
                                                             Yii::$app->formatter->asDate($to_date)]),
                              ]), Alert::ALERT_WARNING);
    }


    public function addTotalConsumptionForTheSiteIsNegativeError($site, $from_date, $to_date) {
        self::addError(Yii::t('common.report',
                              'The total consumption is negative or equal to {n} for the site {site} during the report period ({dates}).',
                              [
                                  'n' => 0,
                                  'site' => $site->name,
                                  'dates' => implode(' - ', [Yii::$app->formatter->asDate($from_date),
                                                             Yii::$app->formatter->asDate($to_date)]),
                              ]), Alert::ALERT_WARNING);
    }


    public static function getErrors() {
        return static::$_errors;
    }


    public static function getFirstError() {
        if(($errors = static::getErrors()) != null) {
            return ArrayHelper::getValue((array)$errors, key($errors));
        }
    }





    public static function shiftReportRangeByTenantEntranceAndExitDates(&$from_date, &$to_date, Tenant $model,
                                                                        $is_strict = true) {
        $entrance_date = $model->entrance_date;
        $exit_date = $model->exit_date;
        if($entrance_date) {
            $entrance_date = TimeManipulator::getStartOfDay($entrance_date);
            // if tenant joins later then last date of report range
            if($entrance_date > $to_date) {
                throw new \InvalidArgumentException('Tenant entrance date is in future');
            }
            //if tenant joins inside report range then we shift the start of report range to joining date
            else {
                if($entrance_date > $from_date) {
                    $from_date = $entrance_date;
                }
            }
        }
        if($exit_date) {
            $exit_date = TimeManipulator::getEndOfDay($exit_date);
            //if tenant has left us earlier then report range has begun
            if($exit_date < $from_date) {
                if($is_strict) {
                    throw new \InvalidArgumentException('Tenant is already left us');
                }
            }
            // if tenant has left us inside the report range then we shift the end of report range to exit date
            else {
                if($exit_date < $to_date) {
                    $to_date = $exit_date;
                }
            }
        }
    }


    /**
     * Calculate meter channel raw data
     *
     * @param object Tenant $model
     * @param integer|array $channels_ids
     * @param integer|string|DateTime $from_date
     * @param integer|string|DateTime $to_date
     * @param integer $rate_type_id
     * @param array $percent
     * @param boolean $negative
     * @return array|bool
     */
    public static function calculateMeterRawData($model, $channels_ids, $from_date, $to_date, $rate_type_id,
                                                 $percent = [],
                                                 $negative = false) {
        $data = [];
        try {
            self::shiftReportRangeByTenantEntranceAndExitDates($from_date, $to_date, $model);
            $from_date = TimeManipulator::getStartOfDay($from_date);
            $to_date = TimeManipulator::getEndOfDay($to_date);
            $percent_shefel = ArrayHelper::getValue($percent, 'shefel', 1);
            $percent_geva = ArrayHelper::getValue($percent, 'geva', 1);
            $percent_pisga = ArrayHelper::getValue($percent, 'pisga', 1);
            $is_divided_by_1000 = MeterChannel::isDividedBy1000($channels_ids);
            $channels = MeterChannel::getActiveByIds($channels_ids);
            $max_consumptions = [0];

            if(!empty($channels)) {
                /**
                 * Rates
                 */
                $rates = Rate::getActiveWithinRangeByTypeId($from_date, $to_date, $rate_type_id);
                $rate_type = RateType::findOne($rate_type_id);
                /**
                 * Calculate data
                 */
                if(!empty($rates)) {
                    $data['total_pay'] = 0;
                    $data['max_consumption'] = 0;
                    $data['total_consumption'] = 0;
                    $data['power_factor_value'] = [];
                    $data['power_factor_percent'] = [];
                    $data['shefel'] = [
                        'reading_diff' => 0,
                        'kvar_reading_diff' => 0,
                        'total_pay' => 0,
                    ];
                    $data['geva'] = [
                        'reading_diff' => 0,
                        'kvar_reading_diff' => 0,
                        'total_pay' => 0,
                    ];
                    $data['pisga'] = [
                        'reading_diff' => 0,
                        'kvar_reading_diff' => 0,
                        'total_pay' => 0,
                    ];
                    foreach($channels as $channel_index => $channel) {
                        $query = (new Query())
                            ->select('t.channel')
                            ->from(MeterSubchannel::tableName() . ' t')
                            ->andWhere(['t.channel_id' => $channel['id']]);
                        $subchannels = Yii::$app->db->cache(function ($db) use ($query) {
                            return $query->createCommand($db)->queryColumn();
                        }, static::CACHE_DURATION);
                        $max_consumption = 0;
                        foreach($rates as $rate_key => $rate) {
                            $rate_from_date = TimeManipulator::getStartOfDay($rate->start_date);
                            $rate_to_date = TimeManipulator::getEndOfDay($rate->end_date);
//                            $day_before_rate_from_date = self::getDayBefore($rate_from_date);
                            $rate_from_date =
                                ($rate_from_date > $from_date) ? $rate_from_date :
                                    $from_date;
                            $rate_to_date =
                                ($rate_to_date > $to_date) ? $to_date : $rate_to_date;
                            $reading_from = [
                                'shefel' => 0,
                                'geva' => 0,
                                'pisga' => 0,
                                'kvar_shefel' => 0,
                                'kvar_geva' => 0,
                                'kvar_pisga' => 0,
                            ];
                            $reading_to = [
                                'shefel' => 0,
                                'geva' => 0,
                                'pisga' => 0,
                                'kvar_shefel' => 0,
                                'kvar_geva' => 0,
                                'kvar_pisga' => 0,
                            ];
                            if(!isset($data['rates'][$rate_key])) {
                                $rate_type_value = $rate_type->type;
                                $data['rates'][$rate_key]['id'] = $rate->id;
                                $data['rates'][$rate_key]['type'] = $rate->getAliasRateType();
                                $data['rates'][$rate_key]['reading_from_date']
                                    = $rate_from_date < $from_date
                                    ? $from_date
                                    : $rate_from_date;
                                $data['rates'][$rate_key]['reading_to_date']
                                    = $rate_to_date > $to_date
                                    ? $to_date
                                    : $rate_to_date;
                                switch($rate_type_value) {
                                    case RateType::TYPE_TAOZ:
                                        $data['rates'][$rate_key]['shefel']['price'] = $rate->shefel;
                                        $data['rates'][$rate_key]['geva']['price'] = $rate->geva;
                                        $data['rates'][$rate_key]['pisga']['price'] = $rate->pisga;
                                        $data['rates'][$rate_key]['shefel']['identifier'] =
                                            $rate->getIdentifier(Rate::CONSUMPTION_SHEFEL);
                                        $data['rates'][$rate_key]['geva']['identifier'] =
                                            $rate->getIdentifier(Rate::CONSUMPTION_GEVA);
                                        $data['rates'][$rate_key]['pisga']['identifier'] =
                                            $rate->getIdentifier(Rate::CONSUMPTION_PISGA);
                                        break;
                                    case RateType::TYPE_FIXED:
                                    default:
                                        $data['rates'][$rate_key]['price'] = $rate->rate;
                                        $data['rates'][$rate_key]['shefel']['price'] = $rate->rate;
                                        $data['rates'][$rate_key]['geva']['price'] = $rate->rate;
                                        $data['rates'][$rate_key]['pisga']['price'] = $rate->rate;
                                        $data['rates'][$rate_key]['identifier'] = $rate->getIdentifier();
                                        break;
                                }
                                $data['rates'][$rate_key]['shefel']['reading_from'] = 0;
                                $data['rates'][$rate_key]['geva']['reading_from'] = 0;
                                $data['rates'][$rate_key]['pisga']['reading_from'] = 0;
                                $data['rates'][$rate_key]['shefel']['reading_to'] = 0;
                                $data['rates'][$rate_key]['geva']['reading_to'] = 0;
                                $data['rates'][$rate_key]['pisga']['reading_to'] = 0;
                            }
                            $multipliers = MeterChannelMultiplier::getMultipliers($channel['id'], $rate_from_date,
                                                                                  $rate_to_date);
                            foreach($multipliers as $multiplier) {
                                if($multiplier->getStartDate() !== null &&
                                   self::getDayBefore($multiplier['start_date']) > $rate_from_date
                                ) {
                                    $multiplier_range_from_date = self::getDayBefore($multiplier['start_date']);
                                }
                                else {
                                    $multiplier_range_from_date = $rate_from_date;
                                }
                                if($multiplier->getEndDate() !== null &&
                                   self::getDayBefore($multiplier['end_date']) < $rate_to_date
                                ) {
                                    $multiplier_range_to_date = self::getDayBefore($multiplier['end_date']);
                                }
                                else {
                                    $multiplier_range_to_date = $rate_to_date;
                                }
                                $multiplier_max_consumption =
                                    ElectricityMeterRawData::getMaxConsumptionWithinDateRange($multiplier_range_from_date -
                                                                                              self::_24_HOURS,
                                                                                              $multiplier_range_to_date,
                                                                                              $channel['meter_id'],
                                                                                              $subchannels);
                                $max_consumption += $multiplier_max_consumption * $multiplier->getMeterMultiplier();
                                $multiplier_reading_from = [
                                    'shefel' => 0,
                                    'geva' => 0,
                                    'pisga' => 0,
                                    'kvar_shefel' => 0,
                                    'kvar_geva' => 0,
                                    'kvar_pisga' => 0,
                                ];
                                $multiplier_reading_to = [
                                    'shefel' => 0,
                                    'geva' => 0,
                                    'pisga' => 0,
                                    'kvar_shefel' => 0,
                                    'kvar_geva' => 0,
                                    'kvar_pisga' => 0,
                                ];
                                foreach($subchannels as $subchannel) {
                                    $reading_subchannel_from =
                                        array_filter(ElectricityMeterRawData::getReadings($channel['meter_id'],
                                                                                          $subchannel,
                                                                                          $multiplier_range_from_date -
                                                                                          self::_24_HOURS,
                                                                                          static::$data_usage_method),
                                            function ($value) {
                                                return $value !== null;
                                            });
                                    $kvar_subchannel_reading_from =
                                        ElectricityMeterRawData::getKvarReadings($channel['meter_id'], $subchannel,
                                                                                 $multiplier_range_from_date -
                                                                                 self::_24_HOURS);
                                    if($reading_subchannel_from == null) {
                                        continue;
                                    }
                                    $multiplier_reading_from['shefel'] += $reading_subchannel_from['shefel'];
                                    $multiplier_reading_from['geva'] += $reading_subchannel_from['geva'];
                                    $multiplier_reading_from['pisga'] += $reading_subchannel_from['pisga'];
                                    $multiplier_reading_from['kvar_shefel'] += $kvar_subchannel_reading_from['kvar_shefel'];
                                    $multiplier_reading_from['kvar_geva'] += $kvar_subchannel_reading_from['kvar_geva'];
                                    $multiplier_reading_from['kvar_pisga'] += $kvar_subchannel_reading_from['kvar_pisga'];
                                    $reading_subchannel_to =
                                        array_filter(ElectricityMeterRawData::getReadings($channel['meter_id'],
                                                                                          $subchannel,
                                                                                          $multiplier_range_to_date,
                                                                                          static::$data_usage_method),
                                            function ($value) {
                                                return $value !== null;
                                            });
                                    $kvar_subchannel_reading_to =
                                        ElectricityMeterRawData::getKvarReadings($channel['meter_id'], $subchannel,
                                                                                 $multiplier_range_to_date);
                                    if($reading_subchannel_to == null) {
                                        continue;
                                    }
                                    $multiplier_reading_to['shefel'] += $reading_subchannel_to['shefel'];
                                    $multiplier_reading_to['geva'] += $reading_subchannel_to['geva'];
                                    $multiplier_reading_to['pisga'] += $reading_subchannel_to['pisga'];
                                    $multiplier_reading_to['kvar_shefel'] += $kvar_subchannel_reading_to['kvar_shefel'];
                                    $multiplier_reading_to['kvar_geva'] += $kvar_subchannel_reading_to['kvar_geva'];
                                    $multiplier_reading_to['kvar_pisga'] += $kvar_subchannel_reading_to['kvar_pisga'];
                                }
                                $reading_from['shefel'] +=
                                    $multiplier_reading_from['shefel'] *
                                    $multiplier->getMeterMultiplier() *
                                    $percent_shefel;
                                $reading_from['geva'] +=
                                    $multiplier_reading_from['geva'] *
                                    $multiplier->getMeterMultiplier() *
                                    $percent_geva;
                                $reading_from['pisga'] +=
                                    $multiplier_reading_from['pisga'] *
                                    $multiplier->getMeterMultiplier() *
                                    $percent_pisga;
                                $reading_from['kvar_shefel'] += $multiplier_reading_from['kvar_shefel']
                                                                * $multiplier->getMeterMultiplier();
                                $reading_from['kvar_geva'] += $multiplier_reading_from['kvar_geva']
                                                              * $multiplier->getMeterMultiplier();
                                $reading_from['kvar_pisga'] += $multiplier_reading_from['kvar_pisga']
                                                               * $multiplier->getMeterMultiplier();
                                $reading_to['shefel'] +=
                                    $multiplier_reading_to['shefel'] *
                                    $multiplier->getMeterMultiplier() *
                                    $percent_shefel;
                                $reading_to['geva'] +=
                                    $multiplier_reading_to['geva'] *
                                    $multiplier->getMeterMultiplier() *
                                    $percent_geva;
                                $reading_to['pisga'] +=
                                    $multiplier_reading_to['pisga'] *
                                    $multiplier->getMeterMultiplier() *
                                    $percent_pisga;
                                $reading_to['kvar_shefel'] += $multiplier_reading_to['kvar_shefel']
                                                              * $multiplier->getMeterMultiplier();
                                $reading_to['kvar_geva'] += $multiplier_reading_to['kvar_geva']
                                                            * $multiplier->getMeterMultiplier();
                                $reading_to['kvar_pisga'] += $multiplier_reading_to['kvar_pisga']
                                                             * $multiplier->getMeterMultiplier();
                            }
                            $data['rates'][$rate_key]['shefel']['reading_from'] += $reading_from['shefel'];
                            $data['rates'][$rate_key]['geva']['reading_from'] += $reading_from['geva'];
                            $data['rates'][$rate_key]['pisga']['reading_from'] += $reading_from['pisga'];
                            $data['rates'][$rate_key]['shefel']['kvar_reading_from'] += $reading_from['kvar_shefel'];
                            $data['rates'][$rate_key]['geva']['kvar_reading_from'] += $reading_from['kvar_geva'];
                            $data['rates'][$rate_key]['pisga']['kvar_reading_from'] += $reading_from['kvar_pisga'];
                            $data['rates'][$rate_key]['shefel']['reading_to'] += $reading_to['shefel'];
                            $data['rates'][$rate_key]['geva']['reading_to'] += $reading_to['geva'];
                            $data['rates'][$rate_key]['pisga']['reading_to'] += $reading_to['pisga'];
                            $data['rates'][$rate_key]['shefel']['kvar_reading_to'] += $reading_to['kvar_shefel'];
                            $data['rates'][$rate_key]['geva']['kvar_reading_to'] += $reading_to['kvar_geva'];
                            $data['rates'][$rate_key]['pisga']['kvar_reading_to'] += $reading_to['kvar_pisga'];
                            if($negative) {
                                $data['rates'][$rate_key]['shefel']['reading_diff'] -= ($data['rates'][$rate_key]['shefel']['reading_to'] -
                                                                                        $data['rates'][$rate_key]['shefel']['reading_from']);
                                $data['rates'][$rate_key]['geva']['reading_diff'] -= ($data['rates'][$rate_key]['geva']['reading_to'] -
                                                                                      $data['rates'][$rate_key]['geva']['reading_from']);
                                $data['rates'][$rate_key]['pisga']['reading_diff'] -= ($data['rates'][$rate_key]['pisga']['reading_to'] -
                                                                                       $data['rates'][$rate_key]['pisga']['reading_from']);
                                $data['rates'][$rate_key]['shefel']['kvar_reading_diff'] -= ($data['rates'][$rate_key]['shefel']['kvar_reading_to'] -
                                                                                             $data['rates'][$rate_key]['shefel']['kvar_reading_from']);
                                $data['rates'][$rate_key]['geva']['kvar_reading_diff'] -= ($data['rates'][$rate_key]['geva']['kvar_reading_to'] -
                                                                                           $data['rates'][$rate_key]['geva']['kvar_reading_from']);
                                $data['rates'][$rate_key]['pisga']['kvar_reading_diff'] -= ($data['rates'][$rate_key]['pisga']['kvar_reading_to'] -
                                                                                            $data['rates'][$rate_key]['pisga']['kvar_reading_from']);
                            }
                            else {
                                $data['rates'][$rate_key]['shefel']['reading_diff'] += ($data['rates'][$rate_key]['shefel']['reading_to'] -
                                                                                        $data['rates'][$rate_key]['shefel']['reading_from']);
                                $data['rates'][$rate_key]['geva']['reading_diff'] += ($data['rates'][$rate_key]['geva']['reading_to'] -
                                                                                      $data['rates'][$rate_key]['geva']['reading_from']);
                                $data['rates'][$rate_key]['pisga']['reading_diff'] += ($data['rates'][$rate_key]['pisga']['reading_to'] -
                                                                                       $data['rates'][$rate_key]['pisga']['reading_from']);
                                $data['rates'][$rate_key]['shefel']['kvar_reading_diff'] += ($data['rates'][$rate_key]['shefel']['kvar_reading_to'] -
                                                                                             $data['rates'][$rate_key]['shefel']['kvar_reading_from']);
                                $data['rates'][$rate_key]['geva']['kvar_reading_diff'] += ($data['rates'][$rate_key]['geva']['kvar_reading_to'] -
                                                                                           $data['rates'][$rate_key]['geva']['kvar_reading_from']);
                                $data['rates'][$rate_key]['pisga']['kvar_reading_diff'] += ($data['rates'][$rate_key]['pisga']['kvar_reading_to'] -
                                                                                            $data['rates'][$rate_key]['pisga']['kvar_reading_from']);
                            }
                            $data['rates'][$rate_key]['shefel']['total_pay'] =
                                ($data['rates'][$rate_key]['shefel']['reading_diff'] *
                                 $data['rates'][$rate_key]['shefel']['price']) / 100;
                            $data['rates'][$rate_key]['geva']['total_pay'] =
                                ($data['rates'][$rate_key]['geva']['reading_diff'] *
                                 $data['rates'][$rate_key]['geva']['price']) / 100;
                            $data['rates'][$rate_key]['pisga']['total_pay'] =
                                ($data['rates'][$rate_key]['pisga']['reading_diff'] *
                                 $data['rates'][$rate_key]['pisga']['price']) / 100;
                            $data['shefel']['total_pay'] += $data['rates'][$rate_key]['shefel']['total_pay'];
                            $data['geva']['total_pay'] += $data['rates'][$rate_key]['geva']['total_pay'];
                            $data['pisga']['total_pay'] += $data['rates'][$rate_key]['pisga']['total_pay'];
                            $data['shefel']['reading_diff'] += $data['rates'][$rate_key]['shefel']['reading_diff'];
                            $data['geva']['reading_diff'] += $data['rates'][$rate_key]['geva']['reading_diff'];
                            $data['pisga']['reading_diff'] += $data['rates'][$rate_key]['pisga']['reading_diff'];
                            //
                            $data['shefel']['kvar_reading_diff'] += $data['rates'][$rate_key]['shefel']['kvar_reading_diff'];
                            $data['geva']['kvar_reading_diff'] += $data['rates'][$rate_key]['geva']['kvar_reading_diff'];
                            $data['pisga']['kvar_reading_diff'] += $data['rates'][$rate_key]['pisga']['kvar_reading_diff'];
                            //
                            $data['total_pay'] += ($data['rates'][$rate_key]['shefel']['total_pay'] +
                                                   $data['rates'][$rate_key]['geva']['total_pay'] +
                                                   $data['rates'][$rate_key]['pisga']['total_pay']);
                            $data['total_consumption'] += ($data['rates'][$rate_key]['shefel']['reading_diff'] +
                                                           $data['rates'][$rate_key]['geva']['reading_diff'] +
                                                           $data['rates'][$rate_key]['pisga']['reading_diff']);
                        }
                        if($is_divided_by_1000) {
                            $max_consumption /= 1000;
                        }
                        $max_consumptions[] = $max_consumption;
                        $x = 3;
                    }
                    $data['reading_diff'] =
                        ($data['shefel']['reading_diff'] + $data['geva']['reading_diff'] +
                         $data['pisga']['reading_diff']);
                    $data['max_consumption'] = max($max_consumptions);
                }
            }
        }
        catch(Exception $e) {
            return false;
        }
        return $data;
    }


    /**
     * Generate data based on single channel rule
     *
     * @param object Tenant $model
     * @param integer|string|DateTime $from_date
     * @param integer|string|DateTime $to_date
     * @param integer|array $channels
     * @param integer $rate_type_id
     * @param array $calculate_percent
     */
    public static function generateSingleChannelRules($model, $from_date, $to_date, $channels = [],
                                                      $rate_type_id = null, $calculate_percent = []) {
        $data = [];
        $max_consumption = [];
        $from_date = TimeManipulator::getStartOfDay($from_date);
        $to_date = TimeManipulator::getEndOfDay($to_date);
        $query = RuleSingleChannel::getActiveTenantRulesFilteredByChannels($model, $channels);
        $rules = Yii::$app->db->cache(function () use ($query) {
            return $query->all();
        }, static::CACHE_DURATION);
        if(!empty($rules)) {
            $index = 0;
            $data['shefel'] = [
                'reading_diff' => 0,
                'total_pay' => 0,
            ];
            $data['geva'] = [
                'reading_diff' => 0,
                'total_pay' => 0,
            ];
            $data['pisga'] = [
                'reading_diff' => 0,
                'total_pay' => 0,
            ];
            $data['total_pay'] = 0;
            $data['total_consumption'] = 0;
            $data['max_consumption'] = 0;
            $data['power_factor_value'] = [];
            $data['power_factor_percent'] = [];
            $rate_type_id = ($rate_type_id == null) ? $model->relationRateType->id : $rate_type_id;
            foreach($rules as $rule) {
                $channels = [];
                $data['rules'][$index]['is_negative'] =
                $negative = ($rule['total_bill_action'] == RuleSingleChannel::TOTAL_BILL_ACTION_MINUS);
                switch($rule['use_type']) {
                    case RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD:
                        $channels_query = RuleSingleChannel::getActiveTenantChannelsByTenantId($rule['percent'],
                                                                                               $rule['usage_tenant_id']);
                        $channels = Yii::$app->db->cache(function () use ($channels_query) {
                            return $channels_query->all();
                        }, static::CACHE_DURATION);
                        break;
                    case RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD:
                    default:
                        $channels[] = [
                            'channel_id' => $rule['channel_id'],
                            'percent' => $rule['percent'],
                        ];
                        break;
                }
                if(!empty($channels)) {
                    foreach($channels as $channel) {
                        $model_channel = MeterChannel::findOne($channel['channel_id']);
                        $percent = [
                            'shefel' => $channel['percent'] / 100,
                            'geva' => $channel['percent'] / 100,
                            'pisga' => $channel['percent'] / 100,
                        ];
                        if($calculate_percent != null) {
                            $percent = [
                                'shefel' => $percent['shefel'] * ArrayHelper::getValue($calculate_percent, 'shefel', 1),
                                'geva' => $percent['geva'] * ArrayHelper::getValue($calculate_percent, 'geva', 1),
                                'pisga' => $percent['pisga'] * ArrayHelper::getValue($calculate_percent, 'pisga', 1),
                            ];
                        }
                        $raw_data = self::calculateMeterRawData($model, $channel['channel_id'], $from_date, $to_date,
                                                                $rate_type_id, $percent, $negative);
                        if(!empty($raw_data)) {
                            $data['rules'][$index]['model_tenant'] = $model;
                            $data['rules'][$index]['rule'] = ArrayHelper::merge([
                                                                                    'meter_name' => $model_channel->relationMeter->name,
                                                                                    'meter_channel_name' => $model_channel->channel,
                                                                                    'type' => self::RULE_SINGLE_CHANNEL,
                                                                                ], $rule);
                            $data['rules'][$index]['tenant_name'] = $model->name;
                            $data['rules'][$index]['rule_name'] = $data['rules'][$index]['rule']['name'];
                            $data['rules'][$index]['rates'] = $raw_data['rates'];
                            $data['rules'][$index]['percent'] = [
                                'shefel' => $percent['shefel'] * 100,
                                'geva' => $percent['geva'] * 100,
                                'pisga' => $percent['pisga'] * 100,
                            ];
                            $data['rules'][$index]['shefel'] = $raw_data['shefel']['reading_diff'];
                            $data['rules'][$index]['geva'] = $raw_data['geva']['reading_diff'];
                            $data['rules'][$index]['pisga'] = $raw_data['pisga']['reading_diff'];
                            $data['rules'][$index]['shefel_total_pay'] = $raw_data['shefel']['total_pay'];
                            $data['rules'][$index]['geva_total_pay'] = $raw_data['geva']['total_pay'];
                            $data['rules'][$index]['pisga_total_pay'] = $raw_data['pisga']['total_pay'];
                            $data['rules'][$index]['max_consumption'] = $raw_data['max_consumption'];
                            $data['rules'][$index]['total_consumption'] = $raw_data['total_consumption'];
                            $data['rules'][$index]['total_pay'] = $raw_data['total_pay'];
                            $data['shefel']['reading_diff'] += $raw_data['shefel']['reading_diff'];
                            $data['geva']['reading_diff'] += $raw_data['geva']['reading_diff'];
                            $data['pisga']['reading_diff'] += $raw_data['pisga']['reading_diff'];
                            $data['shefel']['kvar_reading_diff'] += $raw_data['shefel']['kvar_reading_diff'];
                            $data['geva']['kvar_reading_diff'] += $raw_data['geva']['kvar_reading_diff'];
                            $data['pisga']['kvar_reading_diff'] += $raw_data['pisga']['kvar_reading_diff'];
                            $data['shefel']['total_pay'] += $raw_data['shefel']['total_pay'];
                            $data['geva']['total_pay'] += $raw_data['geva']['total_pay'];
                            $data['pisga']['total_pay'] += $raw_data['pisga']['total_pay'];
                            $data['total_pay'] += $raw_data['total_pay'];
                            $data['total_consumption'] += $raw_data['total_consumption'];
//                            $data['power_factor_value'] =
//                                array_merge($raw_data['power_factor_value'], $data['power_factor_value']);
//                            $data['power_factor_percent'] =
//                                array_merge($raw_data['power_factor_percent'], $data['power_factor_percent']);
                            $max_consumption[] = $raw_data['max_consumption'];
                            $index++;
                        }
                    }
                }
            }
            $reading_diff =
                $data['shefel']['reading_diff'] + $data['pisga']['reading_diff'] + $data['geva']['reading_diff'];
            $kvar_reading_diff = $data['shefel']['kvar_reading_diff'] + $data['pisga']['kvar_reading_diff'] +
                                 $data['geva']['kvar_reading_diff'];
            $power_factor_value = ElectricityMeterRawData::calculatePowerFactor($reading_diff, $kvar_reading_diff);
            $power_factor_percent =
                ElectricityMeterRawData::getPowerFactorAdditionalPercent($power_factor_value,
                                                                         $model->relationRateType->level);
            $data['power_factor_value'] = $power_factor_value;
            $data['power_factor_percent'] = $power_factor_percent;
//            foreach($data['power_factor_value'] as $key => $power_factor_value) {
//                $data['rules'][$key]['power_factor_value'] = $power_factor_value;
//            }
//            foreach($data['power_factor_percent'] as $key => $power_factor_percent) {
//                $data['rules'][$key]['power_factor_percent'] = $power_factor_percent;
//            }
            if($max_consumption != null) {
                $data['max_consumption'] = max($max_consumption);
            }
        }
        return $data;
    }


    /**
     * Generate data based on fixed load rules
     *
     * @param object Tenant $model
     * @param integer|string|DateTime $from_date
     * @param integer|string|DateTime $to_date
     */
    public static function generateFixedLoadRules($model, $from_date, $to_date) {
        $data = [];
        $site_billing = $model->relationSite->relationSiteBillingSetting;
        $query = (new Query())
            ->select('t.*')
            ->from(RuleFixedLoad::tableName() . ' t')
            ->andWhere([
                           't.tenant_id' => $model->id,
                           't.status' => RuleFixedLoad::STATUS_ACTIVE,
                       ]);
        $rules = Yii::$app->db->cache(function ($db) use ($query) {
            return $query->createCommand($db)->queryAll();
        }, static::CACHE_DURATION);
        if($site_billing->fixed_addition_type != null) {
            switch($site_billing->fixed_addition_load) {
                case SiteBillingSetting::FIXED_ADDITION_LOAD_FLAT:
                    switch($site_billing->fixed_addition_type) {
                        case SiteBillingSetting::FIXED_ADDITION_TYPE_MONEY:
                            $billing_rule = [
                                'name' => Yii::t('common.view', 'Fixed addition'),
                                'tenant_id' => $model->id,
                                'description' => $site_billing->fixed_addition_comment,
                                'use_type' => RuleFixedLoad::USE_TYPE_MONEY,
                                'value' => $site_billing->fixed_addition_value,
                            ];
                            $rules = array_merge($rules, [$billing_rule]);
                            break;
                        case SiteBillingSetting::FIXED_ADDITION_TYPE_KWH:
                            $billing_rule = [
                                'name' => Yii::t('common.view', 'Fixed addition'),
                                'tenant_id' => $model->id,
                                'description' => $site_billing->fixed_addition_comment,
                                'use_type' => RuleFixedLoad::USE_TYPE_KWH,
                                'shefel' => $site_billing->fixed_addition_value / 3,
                                'geva' => $site_billing->fixed_addition_value / 3,
                                'pisga' => $site_billing->fixed_addition_value / 3,
                            ];
                            $rules = array_merge($rules, [$billing_rule]);
                            break;
                        default:
                            break;
                    }
                    break;
                case SiteBillingSetting::FIXED_ADDITION_LOAD_PERCENTAGE:
                    $billing_rule = [
                        'name' => Yii::t('common.view', 'Fixed addition'),
                        'tenant_id' => $model->id,
                        'description' => $site_billing->fixed_addition_comment,
                        'use_type' => RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE,
                        'value' => $site_billing->fixed_addition_value,
                    ];
                    $rules = array_merge($rules, [$billing_rule]);
                    break;
                default:
                    break;
            }
        }
        if($rules != null) {
            $sql_date_format = Formatter::SQL_DATE_FORMAT;
            $from_date = TimeManipulator::getStartOfDay($from_date);
            $to_date = TimeManipulator::getEndOfDay($to_date);
            $data['shefel'] = [
                'reading_diff' => 0,
                'total_pay' => 0,
            ];
            $data['geva'] = [
                'reading_diff' => 0,
                'total_pay' => 0,
            ];
            $data['pisga'] = [
                'reading_diff' => 0,
                'total_pay' => 0,
            ];
            $data['fixed'] = [
                'reading_diff' => 0,
                'total_pay' => 0,
            ];
            $data['total_pay'] = 0;
            $data['total_consumption'] = 0;
            $data['max_consumption'] = 0;
            foreach($rules as $key => $rule) {
                switch($rule['use_type']) {
                    case RuleFixedLoad::USE_TYPE_KWH_TAOZ:
                    case RuleFixedLoad::USE_TYPE_KWH_FIXED:
                        /**
                         * Rates
                         */
                        $rates = Rate::find()
                                     ->andwhere([
                                                    'rate_type_id' => $rule['rate_type_id'],
                                                    'status' => Rate::STATUS_ACTIVE,
                                                ])->andWhere('start_date < :to_date AND end_date > :from_date', [
                                'from_date' => $from_date,
                                'to_date' => $to_date,
                            ])->orderBy(['start_date' => SORT_ASC])->all();
                        if($rates != null) {
                            $rate_type = $model->getRateType();
                            $data['rules'][$key]['model_tenant'] = $model;
                            $data['rules'][$key]['rule'] = ArrayHelper::merge([
                                                                                  'type' => self::RULE_FIXED_LOAD,
                                                                              ], $rule);
                            if($data['rules'][$key]['rule']['name'] == null) {
                                $data['rules'][$key]['rule']['name'] = Yii::t('common.view', 'Fixed addition');
                            }
                            $data['rules'][$key]['tenant_name'] = $model->name;
                            $data['rules'][$key]['rule_name'] = $data['rules'][$key]['rule']['name'];
                            $data['rules'][$key]['total_pay'] = 0;
                            $data['rules'][$key]['shefel'] = 0;
                            $data['rules'][$key]['geva'] = 0;
                            $data['rules'][$key]['pisga'] = 0;
                            $data['rules'][$key]['fixed'] = 0;
                            $data['rules'][$key]['total_consumption'] = 0;
                            $data['rules'][$key]['max_consumption'] = 0;
                            foreach($rates as $index => $rate) {
                                $rate_type = $rate->relationRateType->type;
                                $from_rate_date = Yii::$app->formatter->modifyTimestamp($rate->start_date, 'midnight');
                                $to_rate_date = Yii::$app->formatter->modifyTimestamp($rate->end_date, 'tomorrow') - 1;
                                $from_reading_date = ($from_rate_date > $from_date) ? $from_rate_date : $from_date;
                                $to_reading_date = ($to_rate_date > $to_date) ? $to_date : $to_rate_date;
                                $data['rules'][$key]['rates'][$index]['id'] = $rate->id;
                                $data['rules'][$key]['rates'][$index]['type'] = $rate->getAliasRateType();
                                $data['rules'][$key]['rates'][$index]['reading_from_date'] = $from_reading_date;
                                $data['rules'][$key]['rates'][$index]['reading_to_date'] = $to_reading_date;
                                switch($rule['use_type']) {
                                    case RuleFixedLoad::USE_TYPE_KWH_TAOZ:
                                        $data['rules'][$key]['rates'][$index]['shefel']['price'] = $rate->shefel;
                                        $data['rules'][$key]['rates'][$index]['geva']['price'] = $rate->geva;
                                        $data['rules'][$key]['rates'][$index]['pisga']['price'] = $rate->pisga;
                                        $data['rules'][$key]['rates'][$index]['shefel']['identifier'] =
                                            $rate->getIdentifier(Rate::CONSUMPTION_SHEFEL);
                                        $data['rules'][$key]['rates'][$index]['geva']['identifier'] =
                                            $rate->getIdentifier(Rate::CONSUMPTION_GEVA);
                                        $data['rules'][$key]['rates'][$index]['pisga']['identifier'] =
                                            $rate->getIdentifier(Rate::CONSUMPTION_PISGA);
                                        $data['rules'][$key]['rates'][$index]['shefel']['reading_diff'] =
                                            $rule['shefel'];
                                        $data['rules'][$key]['rates'][$index]['geva']['reading_diff'] = $rule['geva'];
                                        $data['rules'][$key]['rates'][$index]['pisga']['reading_diff'] = $rule['pisga'];
                                        $data['rules'][$key]['rates'][$index]['shefel']['total_pay'] =
                                            ($data['rules'][$key]['rates'][$index]['shefel']['reading_diff'] *
                                             $data['rules'][$key]['rates'][$index]['shefel']['price']) / 100;
                                        $data['rules'][$key]['rates'][$index]['geva']['total_pay'] =
                                            ($data['rules'][$key]['rates'][$index]['geva']['reading_diff'] *
                                             $data['rules'][$key]['rates'][$index]['geva']['price']) / 100;
                                        $data['rules'][$key]['rates'][$index]['pisga']['total_pay'] =
                                            ($data['rules'][$key]['rates'][$index]['pisga']['reading_diff'] *
                                             $data['rules'][$key]['rates'][$index]['pisga']['price']) / 100;
                                        $data['shefel']['total_pay'] += (($rule['shefel'] *
                                                                          $data['rules'][$key]['rates'][$index]['shefel']['price'] /
                                                                          100));
                                        $data['geva']['total_pay'] += (($rule['geva'] *
                                                                        $data['rules'][$key]['rates'][$index]['geva']['price'] /
                                                                        100));
                                        $data['pisga']['total_pay'] += (($rule['pisga'] *
                                                                         $data['rules'][$key]['rates'][$index]['pisga']['price'] /
                                                                         100));
                                        $data['total_pay'] += (($rule['shefel'] *
                                                                $data['rules'][$key]['rates'][$index]['shefel']['price'] /
                                                                100) + ($rule['geva'] *
                                                                        $data['rules'][$key]['rates'][$index]['geva']['price'] /
                                                                        100) + ($rule['pisga'] *
                                                                                $data['rules'][$key]['rates'][$index]['pisga']['price'] /
                                                                                100));
                                        $data['rules'][$key]['total_pay'] += (($data['rules'][$key]['rates'][$index]['shefel']['reading_diff'] *
                                                                               $data['rules'][$key]['rates'][$index]['shefel']['price']) /
                                                                              100 +
                                                                              ($data['rules'][$key]['rates'][$index]['geva']['reading_diff'] *
                                                                               $data['rules'][$key]['rates'][$index]['geva']['price']) /
                                                                              100 +
                                                                              ($data['rules'][$key]['rates'][$index]['pisga']['reading_diff'] *
                                                                               $data['rules'][$key]['rates'][$index]['pisga']['price']) /
                                                                              100);
                                        $data['rules'][$key]['total_consumption'] += (($rule['shefel']) +
                                                                                      ($rule['geva']) +
                                                                                      ($rule['pisga']));
                                        $data['rules'][$key]['shefel'] += ($rule['shefel']);
                                        $data['rules'][$key]['geva'] += ($rule['geva']);
                                        $data['rules'][$key]['pisga'] += ($rule['pisga']);
                                        break;
                                    case RuleFixedLoad::USE_TYPE_KWH_FIXED:
                                    default:
                                        $data['rules'][$key]['rates'][$index]['fixed']['price'] = $rate->rate;
                                        $data['rules'][$key]['rates'][$index]['fixed']['identifier'] =
                                            $rate->getIdentifier();
                                        $data['rules'][$key]['rates'][$index]['fixed']['reading_diff'] = $rule['value'];
                                        $data['rules'][$key]['rates'][$index]['fixed']['total_pay'] =
                                            ($data['rules'][$key]['rates'][$index]['fixed']['reading_diff'] *
                                             $data['rules'][$key]['rates'][$index]['fixed']['price']) / 100;
                                        $data['fixed']['total_pay'] += (($rule['value'] *
                                                                         $data['rules'][$key]['rates'][$index]['fixed']['price'] /
                                                                         100));
                                        $data['total_pay'] += (($rule['value'] *
                                                                $data['rules'][$key]['rates'][$index]['fixed']['price'] /
                                                                100));
                                        $data['rules'][$key]['total_pay'] += (($data['rules'][$key]['rates'][$index]['fixed']['reading_diff'] *
                                                                               $data['rules'][$key]['rates'][$index]['fixed']['price']) /
                                                                              100);
                                        $data['rules'][$key]['total_consumption'] += $rule['value'];
                                        $data['rules'][$key]['fixed'] += $rule['value'];
                                        break;
                                }
                                break;
                            }
                            switch($rule['use_type']) {
                                case RuleFixedLoad::USE_TYPE_KWH_TAOZ:
                                    $data['shefel']['reading_diff'] += $data['rules'][$key]['shefel'];
                                    $data['geva']['reading_diff'] += $data['rules'][$key]['geva'];
                                    $data['pisga']['reading_diff'] += $data['rules'][$key]['pisga'];
                                    $data['total_consumption'] += $data['rules'][$key]['total_consumption'];
                                    break;
                                case RuleFixedLoad::USE_TYPE_KWH_FIXED:
                                default:
                                    $data['fixed']['reading_diff'] += $data['rules'][$key]['fixed'];
                                    $data['total_consumption'] += $data['rules'][$key]['total_consumption'];
                                    break;
                            }
                        }
                        break;
                    case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
                    case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
                        $single = self::generateSingleChannelRules($model, $from_date, $to_date);
                        if(!empty($single['rules'])) {
                            $data['rules'][$key]['model_tenant'] = $model;
                            $data['rules'][$key]['rule'] = ArrayHelper::merge([
                                                                                  'type' => self::RULE_FIXED_LOAD,
                                                                              ], $rule);
                            if($data['rules'][$key]['rule']['name'] == null) {
                                $data['rules'][$key]['rule']['name'] = Yii::t('common.view', 'Fixed addition');
                            }
                            $data['rules'][$key]['tenant_name'] = $model->name;
                            $data['rules'][$key]['rule_name'] = $data['rules'][$key]['rule']['name'];
                            $data['rules'][$key]['total_pay'] = 0;
                            $data['rules'][$key]['shefel'] = 0;
                            $data['rules'][$key]['geva'] = 0;
                            $data['rules'][$key]['pisga'] = 0;
                            $data['rules'][$key]['total_consumption'] = 0;
                            $data['rules'][$key]['max_consumption'] = 0;
                            $data['rules'][$key]['price'] = 0;
                            $data['rules'][$key]['shefel'] += ($single['shefel']['reading_diff'] / 100) *
                                                              $rule['value'];
                            $data['rules'][$key]['geva'] += ($single['geva']['reading_diff'] / 100) * $rule['value'];
                            $data['rules'][$key]['pisga'] += ($single['pisga']['reading_diff'] / 100) * $rule['value'];
                            $data['rules'][$key]['total_consumption'] += ($single['total_consumption'] / 100) *
                                                                         $rule['value'];
                            $data['rules'][$key]['total_pay'] += ($single['total_pay'] / 100) * $rule['value'];
                            $data['shefel']['reading_diff'] += ($single['shefel']['reading_diff'] / 100) *
                                                               $rule['value'];
                            $data['geva']['reading_diff'] += ($single['geva']['reading_diff'] / 100) * $rule['value'];
                            $data['pisga']['reading_diff'] += ($single['pisga']['reading_diff'] / 100) * $rule['value'];
                            $data['total_pay'] += ($single['total_pay'] / 100) * $rule['value'];
                            $data['total_consumption'] += ($single['total_consumption'] / 100) * $rule['value'];
                        }
                        $group = self::generateGroupLoadRules($model, $from_date, $to_date);
                        if(!empty($group['rules'])) {
                            $data['rules'][$key]['shefel'] += ($group['shefel']['reading_diff'] / 100) * $rule['value'];
                            $data['rules'][$key]['geva'] += ($group['geva']['reading_diff'] / 100) * $rule['value'];
                            $data['rules'][$key]['pisga'] += ($group['pisga']['reading_diff'] / 100) * $rule['value'];
                            $data['rules'][$key]['total_consumption'] += ($group['total_consumption'] / 100) *
                                                                         $rule['value'];
                            $data['rules'][$key]['total_pay'] += ($group['total_pay'] / 100) * $rule['value'];
                            $data['shefel']['reading_diff'] += ($group['shefel']['reading_diff'] / 100) *
                                                               $rule['value'];
                            $data['geva']['reading_diff'] += ($group['geva']['reading_diff'] / 100) * $rule['value'];
                            $data['pisga']['reading_diff'] += ($group['pisga']['reading_diff'] / 100) * $rule['value'];
                            $data['total_pay'] += ($group['total_pay'] / 100) * $rule['value'];
                            $data['total_consumption'] += ($group['total_consumption'] / 100) * $rule['value'];
                        }
                        $data['rules'][$key]['price'] = ($data['rules'][$key]['total_consumption']) ?
                            ($data['rules'][$key]['total_pay'] / $data['rules'][$key]['total_consumption']) : 0;
                        break;
                    case RuleFixedLoad::USE_TYPE_MONEY:
                    default:
                        $data['rules'][$key]['model_tenant'] = $model;
                        $data['rules'][$key]['rule'] = ArrayHelper::merge([
                                                                              'type' => self::RULE_FIXED_LOAD,
                                                                          ], $rule);
                        if($data['rules'][$key]['rule']['name'] == null) {
                            $data['rules'][$key]['rule']['name'] = Yii::t('common.view', 'Fixed addition');
                        }
                        $data['rules'][$key]['tenant_name'] = $model->name;
                        $data['rules'][$key]['rule_name'] = $data['rules'][$key]['rule']['name'];
                        $data['rules'][$key]['total_pay'] = $rule['value'];
                        $data['rules'][$key]['shefel'] = 0;
                        $data['rules'][$key]['geva'] = 0;
                        $data['rules'][$key]['pisga'] = 0;
                        $data['rules'][$key]['total_consumption'] = 0;
                        $data['rules'][$key]['max_consumption'] = 0;
                        $data['total_pay'] += $rule['value'];
                        break;
                }
            }
        }
        return $data;
    }


    /**
     * Generate data based on group load rules
     *
     * @param object Tenant $model
     * @param integer|string|DateTime $from_date
     * @param integer|string|DateTime $to_date
     */
    public static function generateGroupLoadRules($model, $from_date, $to_date) {
        $data = [];
        $query = (new Query())
            ->select('t.*')
            ->from(RuleGroupLoad::tableName() . ' t')
            ->andWhere([
                           't.tenant_id' => $model->id,
                           't.status' => RuleGroupLoad::STATUS_ACTIVE,
                       ])
            ->orderBy(['t.name' => SORT_ASC]);
        $rules = Yii::$app->db->cache(function ($db) use ($query) {
            return $query->createCommand($db)->queryAll();
        }, static::CACHE_DURATION);
        if($rules != null) {
            $sql_date_format = Formatter::SQL_DATE_FORMAT;
            $from_date = TimeManipulator::getStartOfDay($from_date);
            $to_date = TimeManipulator::getEndOfDay($to_date);
            $max_consumption = [];
            $data['shefel'] = [
                'reading_diff' => 0,
                'total_pay' => 0,
            ];
            $data['geva'] = [
                'reading_diff' => 0,
                'total_pay' => 0,
            ];
            $data['pisga'] = [
                'reading_diff' => 0,
                'total_pay' => 0,
            ];
            $data['total_pay'] = 0;
            $data['total_consumption'] = 0;
            $data['max_consumption'] = 0;
            foreach($rules as $key => $rule) {
                $raw_data = [
                    'rates' => [],
                    'max_consumption' => 0,
                    'total_consumption' => 0,
                    'total_pay' => 0,
                    'shefel' => [
                        'reading_diff' => 0,
                        'total_pay' => 0,
                    ],
                    'geva' => [
                        'reading_diff' => 0,
                        'total_pay' => 0,
                    ],
                    'pisga' => [
                        'reading_diff' => 0,
                        'total_pay' => 0,
                    ],
                ];
                /**
                 * Usage percent
                 */
                $channel_percent = ($rule['percent'] != null) ? $rule['percent'] / 100 : 1;
                $negative = ($rule['total_bill_action'] == RuleGroupLoad::TOTAL_BILL_ACTION_MINUS);
                switch($rule['use_percent']) {
                    case RuleGroupLoad::USE_PERCENT_USAGE:
                        $current_consumption = [];
                        $total_consumption = [];
                        $tenants = Tenant::find()
                                         ->innerJoin(TenantGroupItem::tableName() . ' item',
                                                     'item.tenant_id = ' . Tenant::tableName() . '.id')
                                         ->andWhere([
                                                        'item.group_id' => $rule['usage_tenant_group_id'],
                                                        Tenant::tableName() . '.status' => Tenant::STATUS_ACTIVE,
                                                    ])->all();
                        if($tenants != null) {
                            foreach($tenants as $tenant) {
                                $single_channel_data = self::generateSingleChannelRules($tenant, $from_date, $to_date);
                                if(!empty($single_channel_data)) {
                                    if($tenant->id == $model->id) {
                                        $current_consumption = [
                                            'shefel' => ArrayHelper::getValue($single_channel_data,
                                                                              'shefel.reading_diff', 0),
                                            'geva' => ArrayHelper::getValue($single_channel_data, 'geva.reading_diff',
                                                                            0),
                                            'pisga' => ArrayHelper::getValue($single_channel_data, 'pisga.reading_diff',
                                                                             0),
                                        ];
                                    }
                                    $total_consumption = [
                                        'shefel' => ArrayHelper::getValue($total_consumption, 'shefel', 0) +
                                                    ArrayHelper::getValue($single_channel_data, 'shefel.reading_diff',
                                                                          0),
                                        'geva' => ArrayHelper::getValue($total_consumption, 'geva', 0) +
                                                  ArrayHelper::getValue($single_channel_data, 'geva.reading_diff', 0),
                                        'pisga' => ArrayHelper::getValue($total_consumption, 'pisga', 0) +
                                                   ArrayHelper::getValue($single_channel_data, 'pisga.reading_diff', 0),
                                    ];
                                }
                            }
                        }
                        $percent = [
                            'shefel' => (($total_consumption_shefel =
                                    ArrayHelper::getValue($total_consumption, 'shefel', 0)) ?
                                    (ArrayHelper::getValue($current_consumption, 'shefel', 0) /
                                     $total_consumption_shefel) : 1) * $channel_percent,
                            'geva' => (($total_consumption_geva =
                                    ArrayHelper::getValue($total_consumption, 'geva', 0)) ?
                                    (ArrayHelper::getValue($current_consumption, 'geva', 0) / $total_consumption_geva) :
                                    1) * $channel_percent,
                            'pisga' => (($total_consumption_pisga =
                                    ArrayHelper::getValue($total_consumption, 'pisga', 0)) ?
                                    (ArrayHelper::getValue($current_consumption, 'pisga', 0) /
                                     $total_consumption_pisga) : 1) * $channel_percent,
                        ];
                        break;
                    case RuleGroupLoad::USE_PERCENT_FOOTAGE:
                        $tenants = Tenant::find()
                                         ->innerJoin(TenantGroupItem::tableName() . ' item',
                                                     'item.tenant_id = ' . Tenant::tableName() . '.id')
                                         ->andWhere([
                                                        'item.group_id' => $rule['usage_tenant_group_id'],
                                                        Tenant::tableName() . '.status' => Tenant::STATUS_ACTIVE,
                                                    ])->all();
                        $query = (new Query())
                            ->select(['SUM(IFNULL(t.square_meters, 0))'])
                            ->from(Tenant::tableName() . ' t')
                            ->andWhere([
                                           'and',
                                           ['t.site_id' => $model->site_id],
                                           ['in', 't.id', ArrayHelper::map($tenants, 'id', 'id')],
                                           ['t.status' => Tenant::STATUS_ACTIVE],
                                       ]);
                        $footage_sum = Yii::$app->db->cache(function ($db) use ($query) {
                            return $query->createCommand($db)->queryScalar();
                        }, static::CACHE_DURATION);
                        $percent = [
                            'shefel' => (($footage_sum && $model->square_meters) ?
                                    $model->square_meters / $footage_sum : 1) * $channel_percent,
                            'geva' => (($footage_sum && $model->square_meters) ? $model->square_meters / $footage_sum :
                                    1) * $channel_percent,
                            'pisga' => (($footage_sum && $model->square_meters) ? $model->square_meters / $footage_sum :
                                    1) * $channel_percent,
                        ];
                        break;
                    case RuleGroupLoad::USE_PERCENT_FLAT:
                    default:
                        $percent = [
                            'shefel' => $channel_percent != null ? $channel_percent : 1,
                            'geva' => $channel_percent != null ? $channel_percent : 1,
                            'pisga' => $channel_percent != null ? $channel_percent : 1,
                        ];
                        break;
                }
                /**
                 * Channels
                 */
                $model_rate_type = $model->relationRateType;
                switch($rule['use_type']) {
                    case RuleGroupLoad::USE_TYPE_SINGLE_METER_LOAD:
                        $raw_data = self::calculateMeterRawData($model, $rule['channel_id'], $from_date, $to_date,
                                                                $model_rate_type->id, $percent, $negative);
                        break;
                    case RuleGroupLoad::USE_TYPE_SINGLE_TENANT_GROUP_LOAD:
                        $usage_tenants = Tenant::find()->joinWith([
                                                                      'relationTenantGroupItems',
                                                                      'relationTenantGroupItems.relationTenantGroup',
                                                                  ])
                                               ->andWhere([
                                                              'and',
                                                              [TenantGroup::tableName() .
                                                               '.id' => $rule['tenant_group_id']],
                                                              [TenantGroup::tableName() .
                                                               '.status' => TenantGroup::STATUS_ACTIVE],
                                                              [Tenant::tableName() .
                                                               '.status' => Tenant::STATUS_ACTIVE],
                                                          ])
                                               ->groupBy([Tenant::tableName() . '.id'])
                                               ->all();
                        foreach($usage_tenants as $usage_tenant) {
                            if(($usage_tenant_data =
                                    self::generateSingleChannelRules($usage_tenant, $from_date, $to_date, [],
                                                                     $model_rate_type->id, $percent)) != null
                            ) {
                                $raw_data['shefel']['reading_diff'] += ($negative) ?
                                    -$usage_tenant_data['shefel']['reading_diff'] :
                                    $usage_tenant_data['shefel']['reading_diff'];
                                $raw_data['shefel']['total_pay'] += ($negative) ?
                                    -$usage_tenant_data['shefel']['total_pay'] :
                                    $usage_tenant_data['shefel']['total_pay'];
                                $raw_data['geva']['reading_diff'] += ($negative) ?
                                    -$usage_tenant_data['geva']['reading_diff'] :
                                    $usage_tenant_data['geva']['reading_diff'];
                                $raw_data['geva']['total_pay'] += ($negative) ?
                                    -$usage_tenant_data['geva']['total_pay'] : $usage_tenant_data['geva']['total_pay'];
                                $raw_data['pisga']['reading_diff'] += ($negative) ?
                                    -$usage_tenant_data['pisga']['reading_diff'] :
                                    $usage_tenant_data['pisga']['reading_diff'];
                                $raw_data['pisga']['total_pay'] += ($negative) ?
                                    -$usage_tenant_data['pisga']['total_pay'] :
                                    $usage_tenant_data['pisga']['total_pay'];
                                $raw_data['total_pay'] += ($negative) ? -$usage_tenant_data['total_pay'] :
                                    $usage_tenant_data['total_pay'];
                                $raw_data['total_consumption'] += ($negative) ?
                                    -$usage_tenant_data['total_consumption'] : $usage_tenant_data['total_consumption'];
                                foreach($usage_tenant_data['rules'] as $usage_tenant_data_rule) {
                                    foreach($usage_tenant_data_rule['rates'] as $usage_tenant_data_rule_rate) {
                                        if(empty($raw_data['rates'][$usage_tenant_data_rule_rate['id']])) {
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['id'] =
                                                $usage_tenant_data_rule_rate['id'];
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['type'] =
                                                $usage_tenant_data_rule_rate['type'];
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['reading_from_date'] =
                                                $usage_tenant_data_rule_rate['reading_from_date'];
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['reading_to_date'] =
                                                $usage_tenant_data_rule_rate['reading_to_date'];
                                            if($model_rate_type->type == RateType::TYPE_TAOZ) {
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['shefel']['identifier'] =
                                                    $usage_tenant_data_rule_rate['shefel']['identifier'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['shefel']['reading_diff'] =
                                                    ($negative) ?
                                                        -$usage_tenant_data_rule_rate['shefel']['reading_diff'] :
                                                        $usage_tenant_data_rule_rate['shefel']['reading_diff'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['shefel']['price'] =
                                                    $usage_tenant_data_rule_rate['shefel']['price'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['shefel']['total_pay'] =
                                                    ($negative) ? -$usage_tenant_data_rule_rate['shefel']['total_pay'] :
                                                        $usage_tenant_data_rule_rate['shefel']['total_pay'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['geva']['identifier'] =
                                                    $usage_tenant_data_rule_rate['geva']['identifier'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['geva']['reading_diff'] =
                                                    ($negative) ?
                                                        -$usage_tenant_data_rule_rate['geva']['reading_diff'] :
                                                        $usage_tenant_data_rule_rate['geva']['reading_diff'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['geva']['price'] =
                                                    $usage_tenant_data_rule_rate['geva']['price'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['geva']['total_pay'] =
                                                    ($negative) ? -$usage_tenant_data_rule_rate['geva']['total_pay'] :
                                                        $usage_tenant_data_rule_rate['geva']['total_pay'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['pisga']['identifier'] =
                                                    $usage_tenant_data_rule_rate['pisga']['identifier'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['pisga']['reading_diff'] =
                                                    ($negative) ?
                                                        -$usage_tenant_data_rule_rate['pisga']['reading_diff'] :
                                                        $usage_tenant_data_rule_rate['pisga']['reading_diff'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['pisga']['price'] =
                                                    $usage_tenant_data_rule_rate['pisga']['price'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['pisga']['total_pay'] =
                                                    ($negative) ? -$usage_tenant_data_rule_rate['pisga']['total_pay'] :
                                                        $usage_tenant_data_rule_rate['pisga']['total_pay'];
                                            }
                                            else {
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['identifier'] =
                                                    $usage_tenant_data_rule_rate['identifier'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['shefel']['reading_diff'] =
                                                    ($negative) ?
                                                        -$usage_tenant_data_rule_rate['shefel']['reading_diff'] :
                                                        $usage_tenant_data_rule_rate['shefel']['reading_diff'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['shefel']['total_pay'] =
                                                    ($negative) ? -$usage_tenant_data_rule_rate['shefel']['total_pay'] :
                                                        $usage_tenant_data_rule_rate['shefel']['total_pay'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['geva']['reading_diff'] =
                                                    ($negative) ?
                                                        -$usage_tenant_data_rule_rate['geva']['reading_diff'] :
                                                        $usage_tenant_data_rule_rate['geva']['reading_diff'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['geva']['total_pay'] =
                                                    ($negative) ? -$usage_tenant_data_rule_rate['geva']['total_pay'] :
                                                        $usage_tenant_data_rule_rate['geva']['total_pay'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['pisga']['reading_diff'] =
                                                    ($negative) ?
                                                        -$usage_tenant_data_rule_rate['pisga']['reading_diff'] :
                                                        $usage_tenant_data_rule_rate['pisga']['reading_diff'];
                                                $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['pisga']['total_pay'] =
                                                    ($negative) ? -$usage_tenant_data_rule_rate['pisga']['total_pay'] :
                                                        $usage_tenant_data_rule_rate['pisga']['total_pay'];
                                            }
                                        }
                                        else {
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['reading_from_date'] =
                                                min([
                                                        $usage_tenant_data_rule_rate['reading_from_date'],
                                                        $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['reading_from_date'],
                                                    ]);
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['reading_to_date'] =
                                                max([
                                                        $usage_tenant_data_rule_rate['reading_to_date'],
                                                        $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['reading_to_date'],
                                                    ]);
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['shefel']['reading_diff'] += ($negative) ?
                                                -$usage_tenant_data_rule_rate['shefel']['reading_diff'] :
                                                $usage_tenant_data_rule_rate['shefel']['reading_diff'];
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['shefel']['total_pay'] += ($negative) ?
                                                -$usage_tenant_data_rule_rate['shefel']['total_pay'] :
                                                $usage_tenant_data_rule_rate['shefel']['total_pay'];
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['geva']['reading_diff'] += ($negative) ?
                                                -$usage_tenant_data_rule_rate['geva']['reading_diff'] :
                                                $usage_tenant_data_rule_rate['geva']['reading_diff'];
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['geva']['total_pay'] += ($negative) ?
                                                -$usage_tenant_data_rule_rate['geva']['total_pay'] :
                                                $usage_tenant_data_rule_rate['geva']['total_pay'];
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['pisga']['reading_diff'] += ($negative) ?
                                                -$usage_tenant_data_rule_rate['pisga']['reading_diff'] :
                                                $usage_tenant_data_rule_rate['pisga']['reading_diff'];
                                            $raw_data['rates'][$usage_tenant_data_rule_rate['id']]['pisga']['total_pay'] += ($negative) ?
                                                -$usage_tenant_data_rule_rate['pisga']['total_pay'] :
                                                $usage_tenant_data_rule_rate['pisga']['total_pay'];
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case RuleGroupLoad::USE_TYPE_SINGLE_METER_GROUP_LOAD:
                    default:
                        $query = (new Query())
                            ->select('t.channel_id')
                            ->from(MeterChannelGroupItem::tableName() . ' t')
                            ->innerJoin(MeterChannelGroup::tableName() . ' group', 'group.id = t.group_id')
                            ->innerJoin(MeterChannel::tableName() . ' channel', 'channel.id = t.channel_id')
                            ->innerJoin(Meter::tableName() . ' meter', 'meter.id = channel.meter_id')
                            ->andWhere([
                                           'group.id' => $rule['channel_group_id'],
                                           'group.status' => MeterChannelGroup::STATUS_ACTIVE,
                                       ])
                            ->orderBy([
                                          'meter.name' => SORT_ASC,
                                          'channel.channel' => SORT_ASC,
                                      ])
                            ->groupBy(['t.channel_id']);
                        $channels = Yii::$app->db->cache(function ($db) use ($query) {
                            return $query->createCommand($db)->queryColumn();
                        }, static::CACHE_DURATION);
                        $raw_data =
                            self::calculateMeterRawData($model, $channels, $from_date, $to_date, $model_rate_type->id,
                                                        $percent, $negative);
                        break;
                }
                if(!empty($raw_data)) {
                    $data['rules'][$key]['model_tenant'] = $model;
                    $data['rules'][$key]['rate_type'] = $model_rate_type->type;
                    $data['rules'][$key]['rule'] = ArrayHelper::merge([
                                                                          'type' => self::RULE_GROUP_LOAD,
                                                                      ], $rule);
                    if($data['rules'][$key]['rule']['name'] == null) {
                        $data['rules'][$key]['rule']['name'] = Yii::t('common.view', 'Group load');
                    }
                    $data['rules'][$key]['tenant_name'] = $model->name;
                    $data['rules'][$key]['rule_name'] = $data['rules'][$key]['rule']['name'];
                    $data['rules'][$key]['rates'] = $raw_data['rates'];
                    $data['rules'][$key]['percent'] = [
                        'shefel' => $percent['shefel'] * 100,
                        'geva' => $percent['geva'] * 100,
                        'pisga' => $percent['pisga'] * 100,
                    ];
                    $data['rules'][$key]['shefel'] = $raw_data['shefel']['reading_diff'];
                    $data['rules'][$key]['geva'] = $raw_data['geva']['reading_diff'];
                    $data['rules'][$key]['pisga'] = $raw_data['pisga']['reading_diff'];
                    $data['rules'][$key]['shefel_total_pay'] = $raw_data['shefel']['total_pay'];
                    $data['rules'][$key]['geva_total_pay'] = $raw_data['geva']['total_pay'];
                    $data['rules'][$key]['pisga_total_pay'] = $raw_data['pisga']['total_pay'];
                    $data['rules'][$key]['max_consumption'] = $raw_data['max_consumption'];
                    $data['rules'][$key]['total_consumption'] = $raw_data['total_consumption'];
                    $data['rules'][$key]['total_pay'] = $raw_data['total_pay'];
                    $data['shefel']['reading_diff'] += $raw_data['shefel']['reading_diff'];
                    $data['geva']['reading_diff'] += $raw_data['geva']['reading_diff'];
                    $data['pisga']['reading_diff'] += $raw_data['pisga']['reading_diff'];
                    $data['shefel']['total_pay'] += $raw_data['shefel']['total_pay'];
                    $data['geva']['total_pay'] += $raw_data['geva']['total_pay'];
                    $data['pisga']['total_pay'] += $raw_data['pisga']['total_pay'];
                    $data['total_pay'] += $raw_data['total_pay'];
                    $data['total_consumption'] += $raw_data['total_consumption'];
                    $max_consumption[] = $raw_data['max_consumption'];
                }
            }
            if($max_consumption != null) {
                $data['max_consumption'] = max($max_consumption);
            }
        }
        return $data;
    }


    public static function getListOrderBy() {
        return [
            self::ORDER_BY_METER => Yii::t('common.view', 'MeterID'),
            self::ORDER_BY_TENANT => Yii::t('common.view', 'Tenant name'),
        ];
    }


    public static function getListPowerFactors() {
        return [
            self::POWER_FACTOR_SHOW_DONT_ADD_FUNDS => Yii::t('common.view', "Show but don't add funds"),
            self::POWER_FACTOR_SHOW_ADD_FUNDS => Yii::t('common.view', "Show and add funds"),
            self::POWER_FACTOR_DONT_SHOW => Yii::t('common.view', "Don't show at all"),
        ];
    }


}
