<?php

namespace common\helpers;

use common\components\i18n\Formatter;
use common\models\ElectricityMeterRawData;
use common\models\helpers\reports\ReportGenerator;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelGroup;
use common\models\MeterChannelGroupItem;
use common\models\MeterChannelMultiplier;
use common\models\MeterSubchannel;
use common\models\RuleFixedLoad;
use common\models\RuleGroupLoad;
use common\models\RuleSingleChannel;
use common\models\SiteBillingSetting;
use common\models\Tenant;
use common\models\TenantGroup;
use common\models\TenantGroupItem;
use DateTime;
use Exception;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class KwhCalculator
{
    public static $data_usage_method;


    /**
     * Get from date
     *
     * @param integer|string|DateTime $value
     */
    public static function getFromDate($value) {
        return Yii::$app->formatter->modifyTimestamp($value, 'midnight');
    }


    /**
     * Get to date
     *
     * @param integer|string|DateTime $value
     */
    public static function getToDate($value) {
        return Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1;
    }


    /**
     * Get reading from date
     *
     * @param integer|string|DateTime $value
     */
    public static function getReadingFromDate($value) {
        return Yii::$app->formatter->modifyTimestamp($value, 'midnight') - 86400;
    }


    /**
     * Get reading to date
     *
     * @param integer|string|DateTime $value
     */
    public static function getReadingToDate($value) {
        return Yii::$app->formatter->modifyTimestamp($value, 'tomorrow') - 1;
    }


    /**
     * Generate report
     *
     * @param \common\models\Tenant $tenant
     * @param null $channel_id
     * @param string|integer $from_date
     * @param string|integer $to_date
     * @param string $drilldown
     * @return array
     * @internal param array $channel_ids
     */
    public static function generate(Tenant $tenant, $channel_id = null, $from_date, $to_date,
                                    $drilldown = 'd') {
        $data = [];
        $index = 0;
        $from_date = TimeManipulator::getStartOfDay($from_date);
        $to_date = TimeManipulator::getEndOfDay($to_date);
        $channel_ids = [];
        if($channel_id !== null) {
            $channel_ids = [$channel_id];
        }
        switch($drilldown) {
            case 'm':
                while($from_date <= $to_date) {
                    $start_date =
                        (new DateTime())->setTimestamp($from_date)->modify('first day of this month')->getTimestamp();
                    $end_date =
                        (new DateTime())->setTimestamp($from_date)->modify('last day of this month')->getTimestamp();
                    if($start_date < $from_date) {
                        $start_date = $from_date;
                    }
                    if($end_date > $to_date) {
                        $end_date = $to_date;
                    }
                    self::gatherData($data, $index, $tenant, $channel_ids, $start_date, $end_date, 'MMM');
                    $from_date =
                        (new DateTime())->setTimestamp($from_date)->modify('first day of next month')->getTimestamp();
                    $index++;
                }
                break;
            case 'd':
            default:
                while($from_date <= $to_date) {
                    self::gatherData($data, $index, $tenant, $channel_ids, $from_date, $from_date, 'dd-MM');
                    $from_date += ReportGenerator::_24_HOURS;
                    $index++;
                }
                break;
        }
        return $data;
    }


    public static function gatherData(&$data, $index, $tenant, $channel_ids = [], $from_date, $to_date, $date_format) {
        $data[$index] = [
            'id' => $index,
            'date' => Yii::$app->formatter->asDate($from_date, $date_format),
            'timestamp' => $from_date,
            'pisga' => 0,
            'geva' => 0,
            'shefel' => 0,
            'max_demand' => 0,
            'kvar' => 0,
        ];
        $max_demand = [];
        $rule_from_date = Yii::$app->formatter->asDate($from_date);
        $rule_to_date = Yii::$app->formatter->asDate($to_date);
        $single_rules = static::generateSingleChannelRules($rule_from_date, $rule_to_date, $tenant, $channel_ids);
        if($single_rules != null) {
            $data[$index]['pisga'] += ArrayHelper::getValue($single_rules, 'pisga', 0);
            $data[$index]['geva'] += ArrayHelper::getValue($single_rules, 'geva', 0);
            $data[$index]['shefel'] += ArrayHelper::getValue($single_rules, 'shefel', 0);
            $data[$index]['kvar'] += ArrayHelper::getValue($single_rules, 'kvar', 0);
            $max_demand[] = ArrayHelper::getValue($single_rules, 'max_demand', 0);
        }
        $data[$index]['pisga'] = max($data[$index]['pisga'], 0);
        $data[$index]['geva'] = max($data[$index]['geva'], 0);
        $data[$index]['shefel'] = max($data[$index]['shefel'], 0);
        $data[$index]['kvar'] = max($data[$index]['kvar'], 0);
        if($max_demand != null) {
            $data[$index]['max_demand'] = max($max_demand);
        }
    }


    /**
     * Generate data based on single channel rule
     *
     * @param integer|string|DateTime $from_date
     * @param integer|string|DateTime $to_date
     * @param \common\models\Tenant $tenant
     * @param array $channel_ids
     * @return array
     * @internal param MeterChannel $channel
     */
    public static function generateSingleChannelRules($from_date, $to_date, Tenant $tenant, $channel_ids = []) {
        $data = [
            'pisga' => 0,
            'geva' => 0,
            'shefel' => 0,
            'max_demand' => 0,
            'kvar' => 0,
        ];
        $max_consumption = [];
        $from_date = TimeManipulator::getStartOfDay($from_date);
        $to_date = TimeManipulator::getEndOfDay($to_date);
        $rules_query = RuleSingleChannel::getActiveTenantRulesFilteredByChannels($tenant, $channel_ids);
        $rules = Yii::$app->db->cache(function () use ($rules_query) {
            return $rules_query->all();
        }, ReportGenerator::CACHE_DURATION);
        if(!empty($rules)) {
            foreach($rules as $rule) {
                $channels = [];
                $negative = ($rule['total_bill_action'] == RuleSingleChannel::TOTAL_BILL_ACTION_MINUS);
                switch($rule['use_type']) {
                    case RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD:
                        $channels_query = RuleSingleChannel::getActiveTenantChannelsByTenantId($rule['percent'],
                                                                                               $rule['usage_tenant_id']);
                        $channels = Yii::$app->db->cache(function () use ($channels_query) {
                            return $channels_query->all();
                        }, ReportGenerator::CACHE_DURATION);
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
                        $percent = [
                            'shefel' => $channel['percent'] / 100,
                            'geva' => $channel['percent'] / 100,
                            'pisga' => $channel['percent'] / 100,
                        ];
//                        if($calculate_percent != null) {
//                            $percent = [
//                                'shefel' => $percent['shefel'] * ArrayHelper::getValue($calculate_percent, 'shefel', 1),
//                                'geva' => $percent['geva'] * ArrayHelper::getValue($calculate_percent, 'geva', 1),
//                                'pisga' => $percent['pisga'] * ArrayHelper::getValue($calculate_percent, 'pisga', 1),
//                            ];
//                        }
                        $raw_data = static::calculateMeterRawData($tenant, $channel['channel_id'], $from_date, $to_date,
                                                                  $percent, $negative);
//                        $multipliers =
//                            MeterChannelMultiplier::getMultipliers($channel['channel_id'], $from_date, $to_date);
//                        $max_consumption[] = max($rows_max_consumption) * $multipliers['current_multiplier'] * $multipliers['voltage_multiplier'];
                        if(!empty($raw_data)) {
                            $data['shefel'] += $raw_data['shefel'];
                            $data['geva'] += $raw_data['geva'];
                            $data['pisga'] += $raw_data['pisga'];
                            $data['kvar'] += $raw_data['kvar'];
                            $max_consumption[] = $raw_data['max_demand'];
                        }
                    }
                }
            }
        }
        if($max_consumption != null) {
            $data['max_demand'] = max($max_consumption);
        }
        return $data;
    }


    /**
     * Calculate meter channel raw data
     *
     * @param \common\models\Tenant $model
     * @param integer|array $channels_ids
     * @param integer|string|DateTime $from_date
     * @param integer|string|DateTime $to_date
     * @param array $percent
     * @param boolean $negative
     * @return array|bool
     */
    public static function calculateMeterRawData(Tenant $model, $channels_ids, $from_date, $to_date, $percent = [],
                                                 $negative = false) {
        $data = [
            'pisga' => 0,
            'geva' => 0,
            'shefel' => 0,
            'max_demand' => 0,
            'kvar' => 0,
        ];
        try {
            ReportGenerator::shiftReportRangeByTenantEntranceAndExitDates($from_date, $to_date, $model);
            $from_date = TimeManipulator::getStartOfDay($from_date);
            $to_date = TimeManipulator::getEndOfDay($to_date);
            $percent_shefel = ArrayHelper::getValue($percent, 'shefel', 1);
            $percent_geva = ArrayHelper::getValue($percent, 'geva', 1);
            $percent_pisga = ArrayHelper::getValue($percent, 'pisga', 1);
            $is_divided_by_1000 = MeterChannel::isDividedBy1000($channels_ids);
            $channels = MeterChannel::getActiveByIds($channels_ids);
            $max_consumptions = [0];
            if($channels != null) {
                foreach($channels as $channel) {
                    $subchannels_query = (new Query())
                        ->select('t.channel')
                        ->from(MeterSubchannel::tableName() . ' t')
                        ->andWhere(['t.channel_id' => $channel['id']]);
                    $subchannels = Yii::$app->db->cache(function ($db) use ($subchannels_query) {
                        return $subchannels_query->createCommand($db)->queryColumn();
                    }, ReportGenerator::CACHE_DURATION);
                    $max_consumption = 0;
                    $multipliers = MeterChannelMultiplier::getMultipliers($channel['id'], $from_date,
                                                                          $to_date);
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
                    foreach($multipliers as $multiplier) {
                        if($multiplier->getStartDate() !== null &&
                           TimeManipulator::getDayBefore($multiplier->getStartDate()) > $from_date
                        ) {
                            $multiplier_range_from_date = TimeManipulator::getDayBefore($multiplier->getStartDate());
                        }
                        else {
                            $multiplier_range_from_date = $from_date;
                        }
                        if($multiplier->getEndDate() !== null &&
                           TimeManipulator::getDayBefore($multiplier->getEndDate()) < $to_date
                        ) {
                            $multiplier_range_to_date = TimeManipulator::getDayBefore($multiplier->getEndDate());
                        }
                        else {
                            $multiplier_range_to_date = $to_date;
                        }
                        $multiplier_max_consumption =
                            ElectricityMeterRawData::getMaxConsumptionWithinDateRange($multiplier_range_from_date -
                                                                                      ReportGenerator::_24_HOURS,
                                                                                      $multiplier_range_to_date,
                                                                                      $channel['meter_id'],
                                                                                      $subchannels);
                        $max_consumption += $multiplier_max_consumption * $multiplier->getCurrentMultiplier() *
                                            $multiplier->getVoltageMultiplier();
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
                                array_filter(ElectricityMeterRawData::getReadings($channel['meter_id'], $subchannel,
                                                                                  $multiplier_range_from_date -
                                                                                  ReportGenerator::_24_HOURS,
                                                                                  static::$data_usage_method),
                                    function ($value) {
                                        return $value !== null;
                                    });
                            if($reading_subchannel_from == null) {
                                continue;
                            }
                            $multiplier_reading_from['shefel'] += ArrayHelper::getValue($reading_subchannel_from,
                                                                                        'shefel',
                                                                                        0);
                            $multiplier_reading_from['geva'] += ArrayHelper::getValue($reading_subchannel_from, 'geva',
                                                                                      0);
                            $multiplier_reading_from['pisga'] += ArrayHelper::getValue($reading_subchannel_from,
                                                                                       'pisga',
                                                                                       0);
                            $multiplier_reading_from['kvar_shefel'] += ArrayHelper::getValue($reading_subchannel_from,
                                                                                             'kvar_shefel', 0);
                            $multiplier_reading_from['kvar_geva'] += ArrayHelper::getValue($reading_subchannel_from,
                                                                                           'kvar_geva', 0);
                            $multiplier_reading_from['kvar_pisga'] += ArrayHelper::getValue($reading_subchannel_from,
                                                                                            'kvar_pisga', 0);
                            $reading_subchannel_to =
                                array_filter(ElectricityMeterRawData::getReadings($channel['meter_id'], $subchannel,
                                                                                  $multiplier_range_to_date,
                                                                                  static::$data_usage_method),
                                    function ($value) {
                                        return $value !== null;
                                    });
                            if($reading_subchannel_to == null) {
                                continue;
                            }
                            $multiplier_reading_to['shefel'] += ArrayHelper::getValue($reading_subchannel_to, 'shefel',
                                                                                      0);
                            $multiplier_reading_to['geva'] += ArrayHelper::getValue($reading_subchannel_to, 'geva', 0);
                            $multiplier_reading_to['pisga'] += ArrayHelper::getValue($reading_subchannel_to, 'pisga',
                                                                                     0);
                            $multiplier_reading_to['kvar_shefel'] += ArrayHelper::getValue($reading_subchannel_to,
                                                                                           'kvar_shefel',
                                                                                           0);
                            $multiplier_reading_to['kvar_geva'] += ArrayHelper::getValue($reading_subchannel_to,
                                                                                         'kvar_geva', 0);
                            $multiplier_reading_to['kvar_pisga'] += ArrayHelper::getValue($reading_subchannel_to,
                                                                                          'kvar_pisga', 0);
                        }
                        $reading_from['shefel'] +=
                            $multiplier_reading_from['shefel'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_shefel;
                        $reading_from['geva'] +=
                            $multiplier_reading_from['geva'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_geva;
                        $reading_from['pisga'] +=
                            $multiplier_reading_from['pisga'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_pisga;
                        $reading_from['kvar_shefel'] +=
                            $multiplier_reading_from['kvar_shefel'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_shefel;
                        $reading_from['kvar_geva'] +=
                            $multiplier_reading_from['kvar_geva'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_geva;
                        $reading_from['kvar_pisga'] +=
                            $multiplier_reading_from['kvar_pisga'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_pisga;
                        $reading_to['shefel'] +=
                            $multiplier_reading_to['shefel'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_shefel;
                        $reading_to['geva'] +=
                            $multiplier_reading_to['geva'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_geva;
                        $reading_to['pisga'] +=
                            $multiplier_reading_to['pisga'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_pisga;
                        $reading_to['kvar_shefel'] +=
                            $multiplier_reading_to['kvar_shefel'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_shefel;
                        $reading_to['kvar_geva'] +=
                            $multiplier_reading_to['kvar_geva'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_geva;
                        $reading_to['kvar_pisga'] +=
                            $multiplier_reading_to['kvar_pisga'] *
                            $multiplier->getCurrentMultiplier() *
                            $multiplier->getVoltageMultiplier() *
                            $percent_pisga;
                    }
                    $kvar = ($reading_to['kvar_shefel'] + $reading_to['kvar_geva'] + $reading_to['kvar_pisga']) -
                            ($reading_from['kvar_shefel'] + $reading_from['kvar_geva'] + $reading_from['kvar_pisga']);
                    if($negative) {
                        $data['shefel'] -= ($reading_to['shefel'] - $reading_from['shefel']);
                        $data['geva'] -= ($reading_to['geva'] - $reading_from['geva']);
                        $data['pisga'] -= ($reading_to['pisga'] - $reading_from['pisga']);
                        $data['kvar'] -= ($kvar);
                    }
                    else {
                        $data['shefel'] += ($reading_to['shefel'] - $reading_from['shefel']);
                        $data['geva'] += ($reading_to['geva'] - $reading_from['geva']);
                        $data['pisga'] += ($reading_to['pisga'] - $reading_from['pisga']);
                        $data['kvar'] += ($kvar);
                    }
                    if($is_divided_by_1000) {
                        $max_consumption /= 1000;
                    }
                    $max_consumptions[] = $max_consumption;
                }
            }
            if(!empty($max_consumptions)) {
                $data['max_demand'] = max($max_consumptions);
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
     * @param \common\models\Tenant $model
     * @param integer|string|DateTime $from_date
     * @param integer|string|DateTime $to_date
     * @param array $calculate_percent
     * @return array
     */
    public static function buildSingleChannelRules(Tenant $model, $from_date, $to_date, $calculate_percent = []) {
        $data = [
            'pisga' => 0,
            'geva' => 0,
            'shefel' => 0,
            'max_demand' => 0,
            'kvar' => 0,
        ];
        $max_demand = [];
        $from_date = TimeManipulator::getStartOfDay($from_date);
        $to_date = TimeManipulator::getEndOfDay($to_date);
        $rules = (new Query())
            ->select('t.*')
            ->from(RuleSingleChannel::tableName() . ' t')
            ->andWhere([
                           't.tenant_id' => $model->id,
                           't.status' => RuleSingleChannel::STATUS_ACTIVE,
                       ])->all();
        if($rules != null) {
            foreach($rules as $rule) {
                $negative = ($rule['total_bill_action'] == RuleSingleChannel::TOTAL_BILL_ACTION_MINUS);
                switch($rule['use_type']) {
                    case RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD:
                        $channels = (new Query())->select(['t.channel_id', '(t.percent * :percent / 100) as percent'])
                                                 ->from(RuleSingleChannel::tableName() . ' t')
                                                 ->addParams(['percent' => $rule['percent']])
                                                 ->andWhere([
                                                                't.tenant_id' => $rule['usage_tenant_id'],
                                                                't.status' => RuleSingleChannel::STATUS_ACTIVE,
                                                            ])->all();
                        break;
                    case RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD:
                    default:
                        $channels[] = [
                            'channel_id' => $rule['channel_id'],
                            'percent' => $rule['percent'],
                        ];
                        break;
                }
                if($channels != null) {
                    foreach($channels as $channel) {
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
                        $raw_data = static::calculateMeterRawData($model, $channel['channel_id'], $from_date, $to_date,
                                                                  $percent, $negative);
                        if(!empty($raw_data)) {
                            $data['shefel'] += $raw_data['shefel'];
                            $data['geva'] += $raw_data['geva'];
                            $data['pisga'] += $raw_data['pisga'];
                            $data['kvar'] += $raw_data['kvar'];
                            $max_demand[] = $raw_data['max_demand'];
                        }
                    }
                }
            }
        }
        if($max_demand != null) {
            $data['max_demand'] = max($max_demand);
        }
        return $data;
    }


    /**
     * Generate data based on group load rules
     *
     * @param \common\models\Tenant $model
     * @param integer|string|DateTime $from_date
     * @param integer|string|DateTime $to_date
     */
    public static function buildGroupLoadRules(Tenant $model, $from_date, $to_date) {
        $data = [
            'pisga' => 0,
            'geva' => 0,
            'shefel' => 0,
            'max_demand' => 0,
            'kvar' => 0,
        ];
        $max_demand = [];
        $rules = (new Query())
            ->select('t.*')
            ->from(RuleGroupLoad::tableName() . ' t')
            ->andWhere([
                           't.tenant_id' => $model->id,
                           't.status' => RuleGroupLoad::STATUS_ACTIVE,
                       ])
            ->all();
        if($rules != null) {
            $sql_date_format = Formatter::SQL_DATE_FORMAT;
            $from_date = static::getFromDate($from_date);
            $to_date = static::getToDate($to_date);
            foreach($rules as $key => $rule) {
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
                                $single_channel_data = static::buildSingleChannelRules($tenant, $from_date, $to_date);
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
                        $footage_sum = (new Query())
                            ->select(['SUM(IFNULL(t.square_meters, 0))'])
                            ->from(Tenant::tableName() . ' t')
                            ->andWhere([
                                           'and',
                                           ['t.site_id' => $model->site_id],
                                           ['in', 't.id', ArrayHelper::map($tenants, 'id', 'id')],
                                           ['t.status' => Tenant::STATUS_ACTIVE],
                                       ])->scalar();
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
                switch($rule['use_type']) {
                    case RuleGroupLoad::USE_TYPE_SINGLE_METER_LOAD:
                        $raw_data =
                            static::calculateMeterRawData($model, $rule['channel_id'], $from_date, $to_date, $percent,
                                                          $negative);
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
                                    static::buildSingleChannelRules($usage_tenant, $from_date, $to_date, $percent)) !=
                               null
                            ) {
                                $raw_data['shefel'] += ($negative) ? -$usage_tenant_data['shefel'] :
                                    $usage_tenant_data['shefel'];
                                $raw_data['geva'] += ($negative) ? -$usage_tenant_data['geva'] :
                                    $usage_tenant_data['geva'];
                                $raw_data['pisga'] += ($negative) ? -$usage_tenant_data['pisga'] :
                                    $usage_tenant_data['pisga'];
                            }
                        }
                        break;
                    case RuleGroupLoad::USE_TYPE_SINGLE_METER_GROUP_LOAD:
                    default:
                        $channels = (new Query())
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
                            ->groupBy(['t.channel_id'])
                            ->column();
                        $raw_data =
                            static::calculateMeterRawData($model, $channels, $from_date, $to_date, $percent, $negative);
                        break;
                }
                if(!empty($raw_data)) {
                    $data['shefel'] += $raw_data['shefel'];
                    $data['geva'] += $raw_data['geva'];
                    $data['pisga'] += $raw_data['pisga'];
                    $data['kvar'] += $raw_data['kvar'];
                    $max_demand[] = $raw_data['max_demand'];
                }
            }
        }
        if($max_demand != null) {
            $data['max_demand'] = max($max_demand);
        }
        return $data;
    }


    /**
     * Generate data based on fixed load rules
     *
     * @param \common\models\Tenant $model
     * @param integer|string|DateTime $from_date
     * @param integer|string|DateTime $to_date
     */
    public static function buildFixedLoadRules(Tenant $model, $from_date, $to_date) {
        $data = [
            'pisga' => 0,
            'geva' => 0,
            'shefel' => 0,
            'max_demand' => 0,
            'kvar' => 0,
        ];
        $max_demand = [];
        $site_billing = $model->relationSite->relationSiteBillingSetting;
        $rules = (new Query())
            ->select('t.*')
            ->from(RuleFixedLoad::tableName() . ' t')
            ->andWhere([
                           't.tenant_id' => $model->id,
                           't.status' => RuleFixedLoad::STATUS_ACTIVE,
                       ])->all();
        if($site_billing->fixed_addition_type != null) {
            switch($site_billing->fixed_addition_load) {
                case SiteBillingSetting::FIXED_ADDITION_LOAD_FLAT:
                    switch($site_billing->fixed_addition_type) {
                        case SiteBillingSetting::FIXED_ADDITION_TYPE_KWH:
                            $billing_rule = [
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
            $from_date = static::getFromDate($from_date);
            $to_date = static::getToDate($to_date);
            foreach($rules as $key => $rule) {
                switch($rule['use_type']) {
                    case RuleFixedLoad::USE_TYPE_KWH_TAOZ:
                        $data['shefel'] += $rule['shefel'];
                        $data['geva'] += $rule['geva'];
                        $data['pisga'] += $rule['pisga'];
                        break;
                    case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
                    case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
                        $single = static::buildSingleChannelRules($model, $from_date, $to_date);
                        if(!empty($single)) {
                            $data['shefel'] += ($single['shefel'] / 100) * $rule['value'];
                            $data['geva'] += ($single['geva'] / 100) * $rule['value'];
                            $data['pisga'] += ($single['pisga'] / 100) * $rule['value'];
                        }
                        $group = static::buildGroupLoadRules($model, $from_date, $to_date);
                        if(!empty($group)) {
                            $data['shefel'] += ($group['shefel'] / 100) * $rule['value'];
                            $data['geva'] += ($group['geva'] / 100) * $rule['value'];
                            $data['pisga'] += ($group['pisga'] / 100) * $rule['value'];
                        }
                        break;
                    case RuleFixedLoad::USE_TYPE_KWH_FIXED:
                    case RuleFixedLoad::USE_TYPE_MONEY:
                    default:
                        break;
                }
            }
        }
        if($max_demand != null) {
            $data['max_demand'] = max($max_demand);
        }
        return $data;
    }
}
