<?php

namespace common\models;

use Carbon\Carbon;
use common\components\behaviors\ToTimestampBehavior;
use common\components\behaviors\UserIdBehavior;
use common\components\calculators\data\SiteMainMetersData;
use common\components\calculators\data\WeightedChannel;
use common\components\db\ActiveRecord;
use common\components\TimeRange;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\rbac\Rule;

/**
 * Tenant is the class for the table "tenant".
 * @property  $id
 * @property  $name
 * @property  $type
 * @property  $user_id
 * @property  $site_id
 * @property  $entrance_date
 * @property  $exit_date
 * @property  $rate_type_id
 * @property  $is_visible_on_dat_file
 * @property  $prefix
 * @property  $ending
 * @property  $client_code
 * @property  $contract_id
 * @property  $property_id
 * @property  $formatting
 * @property  $option_visible_barcode
 * @property  $hide_drilldown
 * @property  $to_issue
 * @property  $square_meters
 * @property RateType relationRateType
 * @property TenantBillingSetting relationTenantBillingSetting
 * @property TenantIrregularHours $relationIrregularHours
 * @property Site relationSite
 * @property User relationUser
 */
class Tenant extends ActiveRecord
{
    const TYPE_TENANT = 1;
    const TYPE_TRANSPONDER = 2;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;


    public static function tableName()
    {
        return 'tenant';
    }


    public function rules()
    {
        return [
            [['name'], 'filter', 'filter' => 'strip_tags'],
            [['name'], 'filter', 'filter' => 'trim'],
            [['user_id', 'site_id', 'name'], 'required'],
            [['user_id', 'site_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['square_meters'], 'number', 'min' => 0],
            ['to_issue', 'default', 'value' => Site::TO_ISSUE_NO],
            ['to_issue', 'in', 'range' => array_keys(Site::getListToIssues()), 'skipOnEmpty' => true],
            ['type', 'default', 'value' => self::TYPE_TENANT],
            ['type', 'in', 'range' => array_keys(self::getListTypes()), 'skipOnEmpty' => true],
            ['included_reports', 'string'],
            [['hide_drilldown'], 'default', 'value' => self::NO],
            [['is_visible_on_dat_file'], 'default', 'value' => self::YES],
            [['hide_drilldown', 'is_visible_on_dat_file'], 'boolean'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
            ['included_in_cop', 'boolean'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('common.tenant', 'Client'),
            'site_id' => Yii::t('common.tenant', 'Site'),
            'name' => Yii::t('common.tenant', 'Name'),
            'type' => Yii::t('common.tenant', 'Type'),
            'to_issue' => Yii::t('common.tenant', 'To issue'),
            'square_meters' => Yii::t('common.tenant', 'Square meters'),
            'entrance_date' => Yii::t('common.tenant', 'Entrance date'),
            'exit_date' => Yii::t('common.tenant', 'Exit date'),
            'status' => Yii::t('common.tenant', 'Status'),
            'created_at' => Yii::t('common.tenant', 'Created at'),
            'modified_at' => Yii::t('common.tenant', 'Modified at'),
            'created_by' => Yii::t('common.tenant', 'Created by'),
            'modified_by' => Yii::t('common.tenant', 'Modified by'),
            'old_id' => Yii::t('common.tenant', 'Old ID'),
            'old_channel_id' => Yii::t('common.tenant', 'Old channel ID'),
            'included_reports' => Yii::t('common.tenant', 'Included reports'),
            'hide_drilldown' => Yii::t('common.tenant', 'Hide drilldown on tenant bill'),
            'is_visible_on_dat_file' => Yii::t('common.tenant', 'Show in DAT file'),
            'tenant_name' => Yii::t('common.tenant', 'Tenant name'),
            'rate_type_id' => Yii::t('common.tenant', 'Rate type'),
            'fixed_payment' => Yii::t('common.tenant', 'Fixed payment'),
            'site_name' => Yii::t('common.tenant', 'Site name'),
            'user_name' => Yii::t('common.tenant', 'Client name'),
            'site_footage' => Yii::t('common.tenant', 'Site footage'),
            'included_in_cop' => Yii::t('common.tenant', 'Do not include this tenant in COP calculation for No main meters method'),
        ];
    }


    public function behaviors()
    {
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
                    'entrance_date',
                    'exit_date',
                ],
            ],
        ];
    }


    public function getRelationUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    public function getRelationSite()
    {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }


    public function getRelationTenantGroupItems()
    {
        return $this->hasMany(TenantGroupItem::className(), ['tenant_id' => 'id']);
    }


    public function getRelationTenantBillingSetting()
    {
        return $this->hasOne(TenantBillingSetting::className(), ['tenant_id' => 'id']);
    }


    public function getRelationTenantContacts()
    {
        return $this->hasMany(TenantContact::className(), ['tenant_id' => 'id']);
    }


    public function getRelationRuleSingleChannels()
    {
        return $this->hasMany(RuleSingleChannel::className(), ['tenant_id' => 'id']);
    }


    public function getRelationRuleGroupLoads()
    {
        return $this->hasMany(RuleGroupLoad::className(), ['tenant_id' => 'id']);
    }


    public function getRelationRuleFixedLoads()
    {
        return $this->hasMany(RuleFixedLoad::className(), ['tenant_id' => 'id']);
    }

    public function getRelationIrregularHours()
    {
        return $this->hasMany(TenantIrregularHours::className(), ['tenant_id' => 'id']);
    }

    public function getIrregularHoursTimeRanges()
    {
        $ranges = [];

        foreach ($this->relationIrregularHours as $irregular_hour) {
            /**
             * @var TenantIrregularHours $irregular_hour
             */
            $ranges[] = new TimeRange($irregular_hour->hours_from, $irregular_hour->hours_to, $irregular_hour->day_number);
        }

        return $ranges;
    }

    public static function getListTypes()
    {
        return [
            self::TYPE_TENANT => Yii::t('common.tenant', 'Tenant'),
            self::TYPE_TRANSPONDER => Yii::t('common.tenant', 'Transponder'),
        ];
    }


    public function getAliasType()
    {
        $list = self::getListTypes();
        return (isset($list[$this->type])) ? $list[$this->type] : $this->type;
    }


    public function getAliasToIssue()
    {
        $list = Site::getListToIssues();
        return (isset($list[$this->to_issue])) ? $list[$this->to_issue] : null;
    }


    public static function getListStatuses()
    {
        return [
            self::STATUS_INACTIVE => Yii::t('common.tenant', 'Inactive'),
            self::STATUS_ACTIVE => Yii::t('common.tenant', 'Active'),
        ];
    }


    public function getAliasStatus()
    {
        $list = self::getListStatuses();
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }


    public function getAliasSiteFootage()
    {
        $sum = $this->relationSite->getSquareMeters();
        $footage = (!is_null($this->square_meters)) ? $this->square_meters : 0;
        return ($sum) ? round(($footage * 100) / $sum, 2) : 0;
    }


    public function getFixedPayment()
    {
        if (($tenant_settings = $this->relationTenantBillingSetting) != null &&
            !is_null($tenant_settings->fixed_payment)
        ) {
            return $tenant_settings->fixed_payment;
        } else {
            if (($site_settings = $this->relationSite->relationSiteBillingSetting) != null) {
                return $site_settings->fixed_payment;
            }
        }
    }


    public function getRelationRateType()
    {
        if (($tenant_settings = $this->relationTenantBillingSetting) != null && $tenant_settings->rate_type_id != null) {
            return $tenant_settings->relationRateType;
        } else {
            if (($site_settings = $this->relationSite->relationSiteBillingSetting) != null) {
                return $site_settings->relationRateType;
            } else return null;
        }
    }


    public function getRateType()
    {
        if (($rate_type = $this->relationRateType) != null) {
            return $rate_type->id;
        } else return null;
    }


    public function getAliasRateType()
    {
        if (($tenant_settings = $this->relationTenantBillingSetting) != null && $tenant_settings->rate_type_id != null) {
            return $tenant_settings->getAliasRateType();
        } else {
            if (($site_settings = $this->relationSite->relationSiteBillingSetting) != null &&
                $site_settings->rate_type_id != null
            ) {
                return $site_settings->getAliasRateType() . " " .
                    Html::tag('span', Yii::t('common.tenant', '(from site)'), ['class' => 'text-muted']);
            }
        }
    }


    public function getAliasFixedPayment()
    {
        if (($tenant_settings = $this->relationTenantBillingSetting) != null &&
            !is_null($tenant_settings->fixed_payment)
        ) {
            return $tenant_settings->fixed_payment;
        } else {
            if (($site_settings = $this->relationSite->relationSiteBillingSetting) != null &&
                !is_null($site_settings->fixed_payment)
            ) {
                return $site_settings->fixed_payment . " " .
                    Html::tag('span', Yii::t('common.tenant', '(from site)'), ['class' => 'text-muted']);
            }
        }
    }


    public function getBillingContent()
    {
        return ($this->relationTenantBillingSetting != null) ? $this->relationTenantBillingSetting->billing_content :
            null;
    }


    public function getCountRules()
    {
        $count = 0;
        $count += $this->getRelationRuleSingleChannels()->andWhere(['status' => RuleSingleChannel::STATUS_ACTIVE])
            ->count();
        $count += $this->getRelationRuleGroupLoads()->andWhere(['status' => RuleGroupLoad::STATUS_ACTIVE])->count();
        $count += $this->getRelationRuleFixedLoads()->andWhere(['status' => RuleFixedLoad::STATUS_ACTIVE])->count();
        return $count;
    }


    public static function getListSites($user_id = null)
    {
        $query = (new Query())->from(Site::tableName() . ' t')
            ->andWhere(['in', 't.status', [
                Site::STATUS_ACTIVE,
            ]]);
        if (!is_null($user_id)) {
            $id_tenant = Yii::$app->request->getQueryParam('id');
            $model_tenant = self::findOne($id_tenant);
            if (!Yii::$app->user->can('TenantController.actionCreate')) {
                if (Yii::$app->user->can('TenantController.actionEditSiteOwner', ['model' => $model_tenant])) {
                    $sites = UserOwnerSite::find()->where(['user_owner_id' => Yii::$app->user->id])->all();
                    $site_ids = ArrayHelper::getColumn($sites, 'site_id');
                    $query->andWhere(['t.id' => $site_ids]);
                } else {
                    if (Yii::$app->user->can('TenantController.actionEditOwner', ['model' => $model_tenant])) {
                        $users_model = Yii::$app->user->identity->relationUserOwners;
                        $user_ids = ArrayHelper::getColumn($users_model, 'user_id');
                        array_unshift($user_ids, Yii::$app->user->id);
                        $query->andWhere(['t.user_id' => $user_ids]);
                    }
                }
            }
            $query->andWhere(['t.user_id' => $user_id]);
        } else {
            if (!Yii::$app->user->can('TenantController.actionCreate')) {
                if (Yii::$app->user->can('TenantController.actionCreateSiteOwner')) {
                    $sites = UserOwnerSite::find()->where(['user_owner_id' => Yii::$app->user->id])->all();
                    $site_ids = ArrayHelper::getColumn($sites, 'site_id');
                    $query->andWhere(['t.id' => $site_ids]);
                } else {
                    if (Yii::$app->user->can('TenantController.actionCreateOwner')) {
                        $users_model = Yii::$app->user->identity->relationUserOwners;
                        $user_ids = ArrayHelper::getColumn($users_model, 'user_id');
                        array_unshift($user_ids, Yii::$app->user->id);
                        $query->andWhere(['t.user_id' => $user_ids]);
                    }
                }
            }
        }
        $rows = $query->all();
        return ArrayHelper::map($rows, 'id', 'name');
    }


    public function getIncludedReports()
    {
        if (($included_reports = $this->included_reports) != null) {
            return Json::decode($this->included_reports);
        }
    }


    public function setIncludedReports($value)
    {
        $this->included_reports = Json::encode($value);
    }


    public function getEntranceDateReport(Carbon $from_date): Carbon
    {
        if ($this->entrance_date != null && $this->entrance_date > $from_date) {
            return Carbon::createFromFormat('Y-m-d', $this->entrance_date);
        } else return $from_date;
    }


    public function getExitDateReport(Carbon $to_date): Carbon
    {
        if ($this->exit_date != null && $this->exit_date < $to_date) {
            return Carbon::createFromFormat('Y-m-d', $this->exit_date);
        } else return $to_date;
    }


    /**
     * @param Carbon $range_start_date
     * @return Query
     */
    public function getSingleRules(Carbon $range_start_date)
    {
        $query = RuleSingleChannel::find()
            ->joinWith(['relationMeterChannel' => function (ActiveQuery $query) {
                $query->joinWith('relationMeter');
            }])
            ->andWhere(['<=', RuleSingleChannel::tableName() . '.start_date',
                $range_start_date->getTimestamp()])
            ->andWhere([
                RuleSingleChannel::tableName() . '.tenant_id' => $this->id,
                RuleSingleChannel::tableName() .
                '.status' => RuleSingleChannel::STATUS_ACTIVE,
            ])
            ->orderBy([
                Meter::tableName() . '.name' => SORT_ASC,
                MeterChannel::tableName() . '.channel' => SORT_ASC,
            ]);
        return $query;
    }


    /**
     * @param RuleSingleChannel $rule
     * @return array | WeightedChannel[]
     */
    public function getWeightedChannels(RuleSingleChannel $rule)
    {
        $weighted_channels = [];
        switch ($rule->use_type) {
            case RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD :
                $weighted_channels =
                    [new WeightedChannel($rule->channel_id, $rule->isNegative() ? -$rule->percent : $rule->percent)];
                break;
            case RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD:
                $channels_query = RuleSingleChannel::getActiveTenantChannelsByTenantId($rule->percent,
                    $rule->usage_tenant_di);
                $channels = Yii::$app->db->cache(function () use ($channels_query) {
                    return $channels_query->all();
                }, static::CACHE_DURATION);
                $weighted_channels = [];
                foreach ($channels as $channel) {
                    $weighted_channels[] = new WeightedChannel($channel['id'],
                        $rule->isNegative() ? -$channel['percent'] :
                            $channel['percent']);
                }
                break;
        }
        return $weighted_channels;
    }


    /**
     * @return TimeRange[]
     */
    public function getIrregularTimeRanges()
    {
        $time_ranges = [];
        if (!empty($this->getIrregularHoursFrom()) && !empty($this->getIrregularHoursTo()) &&
            $this->getIrregularHoursFrom() !== $this->getIrregularHoursTo()
        ) {
            $time_ranges[] = new TimeRange($this->getIrregularHoursFrom(), $this->getIrregularHoursTo());
        }
        return $time_ranges;
    }


    /**
     * @return TimeRange[]
     */
    public function getRegularTimeRanges()
    {
        $regular_time_ranges = [];
        $irregular_time_ranges = $this->getIrregularTimeRanges();
        $irregular_time_range = $irregular_time_ranges[0] ?? null;
        if ($irregular_time_range instanceof TimeRange) {
            if ($irregular_time_range->isOverlappingMidnight()) {
                $regular_time_ranges[] = $irregular_time_range->getInverted();
            } else {
                if ($irregular_time_range->isStartingFromMidnight()) {
                    $regular_time_ranges[] = new TimeRange($irregular_time_range->getEndTime(), TimeRange::endOfDay());
                } else {
                    if ($irregular_time_range->isEndingOnMidnight()) {
                        $regular_time_ranges[] =
                            new TimeRange(TimeRange::midnight(), $irregular_time_range->getStartTime());
                    } else {
                        $regular_time_ranges[] =
                            new TimeRange(TimeRange::midnight(), $irregular_time_range->getStartTime());
                        $regular_time_ranges[] =
                            new TimeRange($irregular_time_range->getEndTime(), TimeRange::endOfDay());
                    }
                }
            }
        } else {
            $regular_time_ranges[] = new TimeRange();
        }
        return $regular_time_ranges;
    }


    public function getRegularTimeString()
    {
        $time_ranges = $this->getRegularTimeRanges();
        if (empty($time_ranges)) {
            return '';
        }
        $start_time = reset($time_ranges)->getStartTime();
        $end_time = end($time_ranges)->getEndTime();
        return $start_time->format('H:i') . ' - ' . $end_time->format('H:i');
    }


    public function getIrregularTimeString()
    {
        $time_ranges = $this->getIrregularTimeRanges();
        if (empty($time_ranges)) {
            return '';
        }
        $start_time = reset($time_ranges)->getStartTime();
        $end_time = end($time_ranges)->getEndTime();
        return $start_time->format('H:i') . ' - ' . $end_time->format('H:i');
    }


    public function getIrregularHoursFrom()
    {
        $irregular_hours_from = $this->relationTenantBillingSetting->getIrregularHoursFrom();
        if (!empty($irregular_hours_from)) {
            return $irregular_hours_from;
        } else {
            return $this->relationSite->getIrregularHoursFrom();
        }
    }


    public function getIrregularHoursTo()
    {
        $irregular_hours_to = $this->relationTenantBillingSetting->getIrregularHoursTo();
        if (!empty($irregular_hours_to)) {
            return $irregular_hours_to;
        } else {
            return $this->relationSite->getIrregularHoursTo();
        }
    }

    public function getGroupRules()
    {
        $query = RuleGroupLoad::find()
            ->joinWith(['relationMeterChannel' => function(ActiveQuery $query) {
                $query->joinWith('relationMeter');
            }])
            ->andWhere([
                RuleGroupLoad::tableName() . '.tenant_id' => $this->id,
                RuleGroupLoad::tableName() . '.status' => RuleGroupLoad::STATUS_ACTIVE,
            ])
            ->orderBy([
                Meter::tableName() . '.name' => SORT_ASC,
                MeterChannel::tableName() . '.channel' => SORT_ASC,
            ]);

        return $query;
    }

    public function getFixedRules()
    {
        $query = RuleFixedLoad::find()
            ->where([
                RuleFixedLoad::tableName() . '.tenant_id' => $this->id,
                RuleFixedLoad::tableName() . '.status' => RuleFixedLoad::STATUS_ACTIVE,
            ])
            ->orderBy([
                'created_at' => SORT_ASC,
                'name' => SORT_ASC,
            ]);

        return $query;
    }
}
