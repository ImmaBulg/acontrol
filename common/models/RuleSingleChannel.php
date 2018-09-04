<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;
use common\components\behaviors\ToTimestampBehavior;
use common\components\i18n\Formatter;

/**
 * RuleSingleChannel is the class for the table "rule_single_channel".
 * @property $total_bill_action
 * @property $use_type
 * @property $channel_id
 * @property $percent
 * @property $usage_tenant_di
 * @property $from_hours
 * @property $to_hours
 * @property $use_percent
 * @property $status
 * @property $name
 */
class RuleSingleChannel extends ActiveRecord
{
    const USE_TYPE_SINGLE_METER_LOAD = 1;
    const USE_TYPE_SINGLE_TENANT_LOAD = 2;

    const USE_PERCENT_FULL = 1;
    const USE_PERCENT_PARTIAL = 2;
    const USE_PERCENT_RELATIVE_TO_SQUARE_FOOTAGE = 3;
    const USE_PERCENT_HOUR = 4;

    const TOTAL_BILL_ACTION_PLUS = 1;
    const TOTAL_BILL_ACTION_MINUS = 2;

    const MAX_PERCENT = 100;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;


    public static function tableName() {
        return 'rule_single_channel';
    }


    public function rules() {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['tenant_id', 'channel_id', 'usage_tenant_id'], 'integer'],
            ['replaced', 'boolean'],
            [['percent'], 'number', 'min' => 0, 'max' => self::MAX_PERCENT],
            ['from_hours', 'date', 'format' => Formatter::PHP_TIME_FORMAT],
            ['to_hours', 'date', 'format' => Formatter::PHP_TIME_FORMAT],
            ['use_type', 'default', 'value' => self::USE_TYPE_SINGLE_METER_LOAD],
            ['use_type', 'in', 'range' => array_keys(self::getListUseTypes()), 'skipOnEmpty' => true],
            ['use_percent', 'default', 'value' => self::USE_PERCENT_FULL],
            ['use_percent', 'in', 'range' => array_keys(self::getListUsePercents()), 'skipOnEmpty' => true],
            ['total_bill_action', 'in', 'range' => array_keys(self::getListTotalBillActions()), 'skipOnEmpty' => true],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
        ];
    }


    public function attributeLabels() {
        return [
            'name' => Yii::t('common.rule', 'Name'),
            'tenant_id' => Yii::t('common.rule', 'Tenant'),
            'channel_id' => Yii::t('common.rule', 'Channel'),
            'use_type' => Yii::t('common.rule', 'Usage type'),
            'use_percent' => Yii::t('common.rule', 'Usage percentage'),
            'replaced' => Yii::t('common.rule', 'Meter has been replaced'),
            'percent' => Yii::t('common.rule', 'Percent'),
            'from_hours' => Yii::t('common.rule', 'From hour'),
            'to_hours' => Yii::t('common.rule', 'To hour'),
            'start_date' => Yii::t('common.rule', 'Start date'),
            'total_bill_action' => Yii::t('common.rule', 'Action'),
            'usage_tenant_id' => Yii::t('common.rule', 'Usage tenant ID'),
            'status' => Yii::t('common.rule', 'Status'),
            'created_at' => Yii::t('common.rule', 'Created at'),
            'modified_at' => Yii::t('common.rule', 'Modified at'),
            'created_by' => Yii::t('common.rule', 'Created by'),
            'modified_by' => Yii::t('common.rule', 'Modified by'),
            'usage_tenant_name' => Yii::t('common.rule', 'Tenant name'),
            'meter_name' => Yii::t('common.rule', 'Meter ID'),
            'channel_name' => Yii::t('common.rule', 'Channel'),
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
                    'start_date',
                ],
            ],
        ];
    }


    public function getRelationTenant() {
        return $this->hasOne(Tenant::className(), ['id' => 'tenant_id']);
    }


    public function getRelationUsageTenant() {
        return $this->hasOne(Tenant::className(), ['id' => 'usage_tenant_id']);
    }


    public function getRelationMeterChannel() {
        return $this->hasOne(MeterChannel::className(), ['id' => 'channel_id']);
    }


    public static function getListTotalBillActions() {
        return [
            self::TOTAL_BILL_ACTION_PLUS => '+',
            self::TOTAL_BILL_ACTION_MINUS => '-',
        ];
    }


    public function getAliasTotalBillAction() {
        $list = self::getListTotalBillActions();
        return (isset($list[$this->total_bill_action])) ? $list[$this->total_bill_action] : $this->total_bill_action;
    }


    public static function getListStatuses() {
        return [
            self::STATUS_INACTIVE => Yii::t('common.rule', 'Inactive'),
            self::STATUS_ACTIVE => Yii::t('common.rule', 'Active'),
        ];
    }


    public function getAliasStatus() {
        $list = self::getListStatuses();
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }


    public static function getListUseTypes() {
        return [
            self::USE_TYPE_SINGLE_METER_LOAD => Yii::t('common.rule', 'Single meter load'),
            self::USE_TYPE_SINGLE_TENANT_LOAD => Yii::t('common.rule', 'Single tenant load'),
        ];
    }


    public function getAliasUseType() {
        $list = self::getListUseTypes();
        return (isset($list[$this->use_type])) ? $list[$this->use_type] : $this->use_type;
    }


    public static function getListUsePercents() {
        return [
            self::USE_PERCENT_FULL => Yii::t('common.rule', 'Full'),
            self::USE_PERCENT_PARTIAL => Yii::t('common.rule', 'Partial'),
            self::USE_PERCENT_RELATIVE_TO_SQUARE_FOOTAGE => Yii::t('common.rule',
                                                                   "Relative to tenant's square footage in relation to the site"),
            self::USE_PERCENT_HOUR => Yii::t('common.rule', 'By hour'),
        ];
    }


    public function getAliasUsePercent() {
        $list = self::getListUsePercents();
        return (isset($list[$this->use_percent])) ? $list[$this->use_percent] : $this->use_percent;
    }


    public static function getListMeterChannels($action, $meter_id, $tenant_id = null) {
        $list = [];
        $model_channels = MeterChannel::find()->where([
                                                          'meter_id' => $meter_id,
                                                          'status' => MeterChannel::STATUS_ACTIVE,
                                                      ])->all();
        if($model_channels != null) {
            foreach($model_channels as $model_channel) {
                $name = $model_channel->channel . ' - '
                        . Yii::t('common.rule', '(M={m})', [
                        'm' => $model_channel->meter_multiplier,
                    ]);
                $rules_query = $model_channel->getRelationRuleSingleChannels()
                                             ->joinWith(['relationTenant'])
                                             ->andWhere([
                                                            'and',
                                                            [self::tableName() . '.total_bill_action' => $action],
                                                            [self::tableName() . '.status' => self::STATUS_ACTIVE],
                                                            [Tenant::tableName() . '.status' => self::STATUS_ACTIVE],
                                                            ['in', Tenant::tableName() . '.to_issue', [
                                                                Site::TO_ISSUE_AUTOMATIC,
                                                                Site::TO_ISSUE_MANUAL,
                                                            ]],
                                                            [
                                                                'or',
                                                                Tenant::tableName() . '.exit_date IS NULL',
                                                                ['>', Tenant::tableName() . '.exit_date',
                                                                 strtotime('midnight')],
                                                            ],
                                                        ]);
                if(!is_null($tenant_id)) {
                    $rules_query->andWhere(Tenant::tableName() . '.id != :tenant_id', ['tenant_id' => $tenant_id]);
                }
                $rules = $rules_query->groupBy([self::tableName() . '.id'])->all();
                if($rules != null) {
                    $tenants = [];
                    foreach($rules as $rule) {
                        $tenants[] =
                            $rule->relationTenant->name . ' ' . Yii::$app->formatter->asPercentage($rule->percent);
                    }
                    $name .= " - " .
                             Yii::t('common.rule', 'Taken by {tenants}', ['tenants' => implode(", ", $tenants)]);
                }
                $list[$model_channel->id] = $name;
            }
        }
        return $list;
    }


    public static function getListTenants($site_id, $tenant_id) {
        $list = [];
        $models = Tenant::find()->where([
                                            'and',
                                            ['site_id' => $site_id],
                                            ['status' => Tenant::STATUS_ACTIVE],
                                            ['!=', 'id', $tenant_id],
                                        ])->all();
        if($models != null) {
            foreach($models as $model) {
                $list[$model->id] = $model->name;
            }
        }
        return $list;
    }


    /**
     * @param Tenant $tenant
     * @param MeterChannel $channel
     * @return Query
     */
    public static function getActiveRulesByTenantAndChannel(Tenant $tenant, MeterChannel $channel) {
        $query = (new Query())
            ->select('t.*')
            ->from(RuleSingleChannel::tableName() . ' t')
            ->andWhere([
                           'and',
                           ['t.tenant_id' => $tenant->id],
                           ['t.channel_id' => $channel->id],
                           ['t.status' => RuleSingleChannel::STATUS_ACTIVE],
                       ]);
        return $query;
    }


    /**
     * @param Tenant $tenant
     * @param array $channels
     * @return Query
     */
    public static function getActiveTenantRulesFilteredByChannels(Tenant $tenant, $channels = []) {
        $query = (new Query())
            ->select('t.*')
            ->from(RuleSingleChannel::tableName() . ' t')
            ->leftJoin(MeterChannel::tableName() . ' channel', 'channel.id = t.channel_id')
            ->leftJoin(Meter::tableName() . ' meter', 'meter.id = channel.meter_id')
            ->andWhere([
                           't.tenant_id' => $tenant->id,
                           't.status' => RuleSingleChannel::STATUS_ACTIVE,
                       ])
            ->andFilterWhere(['in', 't.channel_id', $channels])
            ->orderBy([
                          'meter.name' => SORT_ASC,
                          'channel.channel' => SORT_ASC,
                      ]);
        return $query;
    }


    /**
     * @param $percent
     * @param $tenant_id
     * @return Query
     */
    public static function getActiveTenantChannelsByTenantId($percent, $tenant_id) {
        $query = (new Query())->select(['t.channel_id as id', '(t.percent * :percent / 100) as percent'])
                              ->from(RuleSingleChannel::tableName() . ' t')
                              ->innerJoin(Tenant::tableName() . ' tenant', 'tenant.id = t.tenant_id')
                              ->innerJoin(MeterChannel::tableName() . ' channel',
                                          'channel.id = t.channel_id')
                              ->innerJoin(Meter::tableName() . ' meter', 'meter.id = channel.meter_id')
                              ->addParams(['percent' => $percent])
                              ->andWhere([
                                             't.tenant_id' => $tenant_id,
                                             't.status' => RuleSingleChannel::STATUS_ACTIVE,
                                             'tenant.status' => Tenant::STATUS_ACTIVE,
                                         ]);
        return $query;
    }


    public function isNegative() {
        return $this->total_bill_action === self::TOTAL_BILL_ACTION_MINUS;
    }


    public function getChannelName() {
        /**
         * @var MeterChannel $channel ;
         */
        $channel = $this->getRelationMeterChannel()->one();
        return $channel->channel;
    }


    public function getMeterName() {
        /**
         * @var MeterChannel $channel ;
         */
        $channel = $this->getRelationMeterChannel()->one();
        return $channel->relationMeter->name;
    }

}
