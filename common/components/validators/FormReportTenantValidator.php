<?php
namespace common\components\validators;

use backend\models\forms\FormReport;
use Carbon\Carbon;
use common\components\i18n\Formatter;
use common\exceptions\FormReportValidationContinueException;
use common\exceptions\FormReportValidationInterruptException;
use common\helpers\TimeManipulator;
use common\models\AirMeterRawData;
use common\models\AirRates;
use common\models\helpers\reports\ReportGenerator;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelGroupItem;
use common\models\MeterSubchannel;
use common\models\Rate;
use common\models\RateType;
use common\models\Report;
use common\models\RuleGroupLoad;
use common\models\RuleSingleChannel;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\Tenant;
use common\models\TenantBillingSetting;
use Yii;
use yii\console\Request;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 12.07.2017
 * Time: 22:39
 */
class FormReportTenantValidator
{
    /**
     * @var Tenant
     */
    public $tenant = null;
    /**
     * @var FormReport
     */
    public $form_report = null;

    public $missing_data = [];
    public $push_alerts = [];


    public function __construct(Tenant $tenant, FormReport $form_report, Carbon $from_date,
                                Carbon $to_date) {
        $this->tenant = $tenant;
        $this->form_report = $form_report;
        $this->from_date = clone $from_date;
        $this->to_date = clone $to_date;
    }


    public function validate() {
        /**
         * Is Entrance/Exit dates are valid
         */
        if(!in_array($this->form_report->type, (array)$this->tenant->getIncludedReports())) {
            throw new FormReportValidationContinueException('Tenant is not included in reports!');
        }
        $to_issue = $this->tenant->to_issue;
        // If tenant is set to issue automatically - set $is_automatically_generated = true
        if($to_issue == Site::TO_ISSUE_AUTOMATIC && Yii::$app->request instanceof Request) {
            $this->form_report->is_automatically_generated = true;
        }
        else $this->form_report->is_automatically_generated = false;
        $this->checkReportRangeAgainstTenantEntranceAndExitDates();
        /**
         * Is tenant have valid rate
         */
        if(!$this->isAnyRatesAvailableWithinDateRange()) {
            $message = Yii::t('backend.report',
                              'There are no rates available of tenant {name} for the period you selected.', [
                                  'name' => $this->tenant->name,
                              ]);
            $this->form_report->addError('type', $message);
            throw new FormReportValidationInterruptException($message);
        }
        /**
         * Is tenant have readings for his rules
         */
        $channels = $this->getChannels();
        if($channels != null) {
            /**
             * Detect missing data
             */
            foreach($channels as $channel_id) {

                list($meter_id, $meter_name) = $this->getMeterInfo($channel_id);
                $subchannels = $this->getSubchannels($channel_id);
                foreach($subchannels as $subchannel) {

                    $this->checkReadings($meter_id, $meter_name, $channel_id, $subchannel);
                }
            }
        }
        else
        {
            $message = Yii::t('backend.report',
                'There are no rules available of tenant {name} for the period you selected.', [
                    'name' => $this->tenant->name,
                ]);
            $this->form_report->addError('type', $message);
            throw new FormReportValidationInterruptException($message);
        }

        if ($this->form_report->level == Report::LEVEL_SITE) {
            switch($this->form_report->report_calculation_type)
            {
                case Report::TENANT_BILL_REPORT_BY_MANUAL_COP:
                    $this->checkManualCop();
                    break;
                case Report::TENANT_BILL_REPORT_BY_MAIN_METERS:
                    $this->checkMainChannels();
                    break;
                /*case Report::TENANT_BILL_REPORT_BY_FIRST_RULE:
                    $this->checkElectricalMainChannels();
                    break;*/
            }
        }
        else {
            if ($this->form_report->type == Report::TYPE_TENANT_BILLS) {
                switch($this->form_report->report_calculation_type)
                {
                    case Report::TENANT_BILL_REPORT_BY_MANUAL_COP:
                        $this->checkManualCop();
                        break;
                    case Report::TENANT_BILL_REPORT_BY_MAIN_METERS:
                        $this->checkMainChannels();
                        break;
                    case Report::TENANT_BILL_REPORT_BY_FIRST_RULE:
                        $this->checkElectricalMainChannels();
                        break;
                }
            }
        }


        return ['missing_date' => $this->missing_data, 'push_alerts' => $this->push_alerts];
    }

    private function checkManualCop()
    {
        $rate_type_id = (new Query())
            ->select('t.rate_type_id')
            ->from(SiteBillingSetting::tableName() . ' t')
            ->where(['=', 't.site_id', $this->form_report->site_id])
            ->column();
        $rate_name = (new Query())
            ->select('t.name_en')
            ->from(RateType::tableName() . ' t')
            ->where(['=', 't.id', $rate_type_id])
            ->column();
        $site_cop = (new Query())
            ->select('t.manual_cop, t.manual_cop_pisga, t.manual_cop_geva, t.manual_cop_shefel')
            ->from(Site::tableName() . ' t')
            ->where(['=', 't.id', $this->form_report->site_id])
            ->one();

        switch ($rate_name) {
            case 'Home':
            case 'General':
                if ($site_cop['manual_cop'] === null) {
                    $message = Yii::t('backend.report',
                        'You are trying to issue report using Manual COP. Please make sure to enter the manual COP on Site.');
                    $this->form_report->addError('type', $message);
                    throw new FormReportValidationInterruptException($message);
                }
                break;
            default:
                if ($site_cop['manual_cop_pisga'] === null || $site_cop['manual_cop_geva'] === null || $site_cop['manual_cop_shefel'] === null) {
                    $message = Yii::t('backend.report',
                        'You are trying to issue report using Manual COP. Please make sure to enter the manual COP on Site.');
                    $this->form_report->addError('type', $message);
                    throw new FormReportValidationInterruptException($message);
                }
                break;
        }
    }

    private function checkElectricalMainChannels()
    {
        $channels = (new Query())
            ->select('t.channel_id')
            ->from(RuleSingleChannel::tableName() . ' t')
            ->where(['<=', 't.start_date', strtotime($this->from_date)])
            ->andWhere([
                't.tenant_id' => $this->tenant->id,
                't.status' => RuleSingleChannel::STATUS_ACTIVE,
            ])->column();
        $main_channels = [];
        foreach ($channels as $channel_id) {
            $channel = (new Query())
                ->select('t.meter_id')
                ->from(MeterChannel::tableName() . ' t')
                ->where(['=', 't.id', $channel_id])
                ->andWhere([
                    't.is_main' => (int)true,
                ])
                ->column();
            $meter = (new Query())
                ->select('t.id')
                ->from(Meter::tableName() . ' t')
                ->where(['=', 'type', 'electricity'])
                ->column();
            if ($channel)
                $main_channels[] = $channel;
        }
        if ($main_channels === []) {
            $message = Yii::t('backend.report',
                'You are trying to issue report with COP calculation of type No main air meter which requires at least one IsMain electrical channel.', [
                    'name' => $this->tenant->name,
                ]);
            $this->form_report->addError('type', $message);
            throw new FormReportValidationInterruptException($message);
        }
    }

    private function checkMainChannels()
    {
        $channels = (new Query())
            ->select('t.channel_id')
            ->from(RuleSingleChannel::tableName() . ' t')
            ->where(['<=', 't.start_date', strtotime($this->from_date)])
            ->andWhere([
                't.tenant_id' => $this->tenant->id,
                't.status' => RuleSingleChannel::STATUS_ACTIVE,
            ])->column();
        $main_channels = [];
        foreach ($channels as $channel_id) {
            $channel = (new Query())
                ->select('t.meter_id')
                ->from(MeterChannel::tableName() . ' t')
                ->where(['=', 't.id', $channel_id])
                ->andWhere([
                    't.is_main' => (int)true,
                ])
                ->column();
            if ($channel)
                $main_channels[] = $channel;
        }

        if ($main_channels === []) {
            $message = Yii::t('backend.report',
                'You are trying to issue report using COP calculation that requires at leat one Electric IsMain channel and at least one Air IsMain channel. Currently there are no Electric / Air channels marked IsMain to tenant {name}', [
                    'name' => $this->tenant->name,
                ]);
            $this->form_report->addError('type', $message);
            throw new FormReportValidationInterruptException($message);
        }

    }

    private function checkReadings($meter_id, $meter_name, $channel_id, $subchannel) {
        $readings_from = $this->getReadings($meter_name, $subchannel['channel'], $this->from_date);
        if($readings_from == null) {
            $this->reportEmptyReadings($meter_id, $meter_name, $channel_id, $subchannel['channel'], $this->from_date);
        }
        $readings_to = $this->getReadings($meter_name, $subchannel['channel'], $this->to_date);
        if($readings_to == null) {
            $this->reportEmptyReadings($meter_id, $meter_name, $channel_id, $subchannel['channel'], $this->to_date);
        }
    }


    private function getReadings($meter_name, $subchannel, Carbon $date) {
        $readings =
            array_filter(AirMeterRawData::getReadings($meter_name,
                                                      $subchannel,
                                                      $date),
                function ($value) {
                    return $value !== null;
                });
        return $readings;
    }


    private function reportEmptyReadings($meter_id, $meter_name, $channel_id, $subchannel, Carbon $date) {
        $this->missing_data[$subchannel['id']]['meter_id'] = $meter_name;
        $this->missing_data[$subchannel['id']]['channel_id'] = $subchannel['channel'];
        $this->missing_data[$subchannel['id']]['dates'][$date->format(Formatter::SITE_DATE_FORMAT)] =
            $date->format(Formatter::SITE_DATE_FORMAT);
        $this->missing_data[$subchannel['id']]['tenants'][] = $this->tenant->name;

        $this->push_alerts[$channel_id]['meter_id'] = $meter_id;
        $this->push_alerts[$channel_id]['channel_id'] = $channel_id;
        $this->push_alerts[$channel_id]['description'][$subchannel['channel'] . '-' .
                                                       $date->format(Formatter::SITE_DATE_FORMAT)] =
            Yii::t('backend.view',
                   'Missing channel {channel} ({meter}) data for date: {date}', [
                       'meter' => $meter_name,
                       'channel' => $subchannel['channel'],
                       'date' => $date->format(Formatter::SITE_DATE_FORMAT),
                   ]);
    }


    private function getMeterInfo($channel_id) {
        $meter = (new Query())
            ->select('t.id, t.name')
            ->from(Meter::tableName() . ' t')
            ->innerJoin(MeterChannel::tableName() . ' meter_channel',
                        'meter_channel.meter_id = t.id')
            ->andWhere(['meter_channel.id' => $channel_id])
            ->one();
        $meter_id = ArrayHelper::getValue($meter, 'id');
        $meter_name = ArrayHelper::getValue($meter, 'name');
        return [$meter_id, $meter_name];
    }


    private function getSubchannels($channel_id) {
        $subchannels = (new Query())
            ->select('t.id, t.channel')
            ->from(MeterSubchannel::tableName() . ' t')
            ->andWhere(['t.channel_id' => $channel_id])
            ->all();
        return $subchannels;
    }


    private function isAnyRatesAvailableWithinDateRange() {
        $model_rate_start = AirRates::find()->where([
                                                        'rate_type_id' => $this->tenant->getRateType(),
                                                        'status' => Rate::STATUS_ACTIVE,
                                                    ])->andWhere('start_date <= :start_date', [
            'start_date' => $this->to_date->format('Y-m-d'),
        ])->exists();
        $model_rate_end = AirRates::find()->where([
                                                      'rate_type_id' => $this->tenant->getRateType(),
                                                      'status' => Rate::STATUS_ACTIVE,
                                                  ])->andWhere('end_date >= :end_date', [
            'end_date' => $this->from_date->format('Y-m-d'),
        ])->exists();
        return $model_rate_start || $model_rate_end;
    }

    private function getChannels() {
        $single_rule_channels = (new Query())
            ->select('t.channel_id')
            ->from(RuleSingleChannel::tableName() . ' t')
            ->where(['<=', 't.start_date', strtotime($this->from_date)])
            ->andWhere([
                           't.tenant_id' => $this->tenant->id,
                           't.status' => RuleSingleChannel::STATUS_ACTIVE,
                       ])->column();
        $group_rule_main_channels = (new Query())
            ->select('t.channel_id')
            ->innerJoin(MeterChannel::tableName() . ' channel', 'channel.id = t.channel_id')
            ->from(RuleGroupLoad::tableName() . ' t')
            ->andWhere([
                           't.tenant_id' => $this->tenant->id,
                           't.status' => RuleGroupLoad::STATUS_ACTIVE,
                       ])->column();
        $group_rule_channels = (new Query())
            ->select('group_item.channel_id')
            ->from(RuleGroupLoad::tableName() . ' t')
            ->innerJoin(MeterChannelGroupItem::tableName() . ' group_item',
                        'group_item.group_id = t.channel_group_id')
            ->andWhere([
                           't.tenant_id' => $this->tenant->id,
                           't.status' => RuleGroupLoad::STATUS_ACTIVE,
                       ])->column();
        $channels = array_unique(ArrayHelper::merge($single_rule_channels, $group_rule_channels,
                                                    $group_rule_main_channels));
        return $channels;
    }


    private function checkReportRangeAgainstTenantEntranceAndExitDates() {
        $entrance_date = $this->tenant->entrance_date;
        $exit_date = $this->tenant->exit_date;
        if($entrance_date) {
            $entrance_date = TimeManipulator::getStartOfDay($entrance_date);
            if($entrance_date > $this->to_date) {
                throw new FormReportValidationContinueException('Tenant will enter later than last report day!');
            }
            else {
                if($entrance_date > $this->from_date) {
                    $this->from_date = $entrance_date;
                }
            }
        }
        if($exit_date) {
            $exit_date = TimeManipulator::getEndOfDay($exit_date);
            if($exit_date < $this->from_date) {
                throw new FormReportValidationContinueException('Tenant will leave us earlier than first report day!');
            }
            else {
                if($exit_date < $this->to_date) {
                    $this->to_date = $exit_date;
                }
            }
        }
    }
}