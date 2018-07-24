<?php

namespace common\models\helpers\reports;

use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelMultiplier;
use common\models\RuleSingleChannel;
use common\models\Site;
use common\models\Tenant;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ReportGeneratorMeters extends ReportGenerator implements IReportGenerator
{
    /**
     * Generate report
     *
     * @param string|integer $report_from_date
     * @param string|integer $report_to_date
     * @param $site
     * @param array $tenants
     * @param array $params
     * @return array
     */
    public static function generate($report_from_date, $report_to_date, $site, $tenants = [], array $params = []) {
        $data = [];
        $rows = [];
        $site_owner = $site->relationUser;
        $tenants = ArrayHelper::map($site->getRelationTenantsIssued()->andWhere([
                                                                                    'or',
                                                                                    Tenant::tableName() .
                                                                                    '.exit_date IS NULL',
                                                                                    ['>=',
                                                                                     Tenant::tableName() . '.exit_date',
                                                                                     strtotime($report_to_date)],
                                                                                ])->all(), 'id', 'id');
        $from_date = TimeManipulator::getStartOfDay($report_from_date);
        $to_date = TimeManipulator::getEndOfDay($report_to_date);
        $data['site'] = $site;
        $data['total_single_meters'] = 0;
        $data['total_multiphase_meters'] = 0;
        $rules = RuleSingleChannel::find()
                                  ->joinWith(['relationTenant'])
                                  ->andWhere(['in', RuleSingleChannel::tableName() . '.tenant_id', $tenants])
                                  ->andWhere([
                                                 RuleSingleChannel::tableName() .
                                                 '.status' => RuleSingleChannel::STATUS_ACTIVE,
                                             ])
                                  ->groupBy([RuleSingleChannel::tableName() . '.id'])
                                  ->orderBy([Tenant::tableName() . '.to_issue' => SORT_ASC])
                                  ->all();
        if($rules != null) {
            foreach($rules as $rule) {
                $channels = [];
                switch($rule->use_type) {
                    case RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD:
                        $channels = (new Query())->select(['t.channel_id'])
                                                 ->from(RuleSingleChannel::tableName() . ' t')
                                                 ->innerJoin(Tenant::tableName() . ' tenant', 'tenant.id = t.tenant_id')
                                                 ->innerJoin(MeterChannel::tableName() . ' channel',
                                                             'channel.id = t.channel_id')
                                                 ->innerJoin(Meter::tableName() . ' meter',
                                                             'meter.id = channel.meter_id')
                                                 ->andWhere([
                                                                't.tenant_id' => $rule->usage_tenant_id,
                                                                't.status' => RuleSingleChannel::STATUS_ACTIVE,
                                                                'tenant.status' => Tenant::STATUS_ACTIVE,
                                                            ])->column();
                        break;
                    case RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD:
                    default:
                        $channels[] = $rule->channel_id;
                        break;
                }
                if($channels != null) {
                    foreach($channels as $channel_id) {
                        $meter_channel = MeterChannel::findOne($channel_id);
                        $meter = $meter_channel->relationMeter;
                        $phases = $meter->relationMeterType->phases;
                        $tenant = $rule->relationTenant;
                        if($phases > 1) {
                            $data['total_multiphase_meters']++;
                        }
                        else {
                            $data['total_single_meters']++;
                        }
                        switch($rule->relationTenant->to_issue) {
                            case Site::TO_ISSUE_AUTOMATIC:
                            case Site::TO_ISSUE_MANUAL:
                                $to_issue = true;
                                break;
                            default:
                                $to_issue = false;
                                break;
                        }
                        $multipliers = MeterChannelMultiplier::getMultipliers($channel_id, $from_date, $to_date, true);
                        $rows["{$meter->name}{$meter_channel->channel}-$to_issue"] = [
                            'meter' => $meter,
                            'meter_channel' => $meter_channel,
                            'tenant' => $tenant,
                            'current_multiplier' => $multipliers['current_multiplier'],
                            'voltage_multiplier' => $multipliers['voltage_multiplier'],
                            'to_issue' => $to_issue,
                            'tenant_name' => $tenant->name,
                            'rule_name' => "{$meter->name} - {$meter_channel->channel}",
                            'meter_name' => "{$meter->name}",
                            'meter_channel_name' => "{$meter_channel->channel}",
                        ];
                    }
                }
            }
            $order_by = ArrayHelper::getValue($params, 'order_by', static::ORDER_BY_METER);
            switch($order_by) {
                case static::ORDER_BY_TENANT:
                    usort($rows, function ($a, $b) {
                        return strcmp($a['tenant_name'], $b['tenant_name']) ?: $a['rule_name'] - $b['rule_name'];
                    });
                    break;
                case static::ORDER_BY_METER:
                default:
                    usort($rows, function ($a, $b) {
                        return strcmp($a['meter_name'], $b['meter_name']) ?:
                            $a['meter_channel_name'] - $b['meter_channel_name'];
                    });
                    break;
            }
        }
        $data['data'] = $rows;
        return $data;
    }
}