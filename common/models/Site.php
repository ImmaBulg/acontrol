<?php

namespace common\models;

use common\components\calculators\data\MainMetersData;
use common\components\TimeRange;
use dezmont765\yii2bundle\behaviors\FileSaveBehavior;
use Yii;
use yii\db\Query;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\web\NotFoundHttpException;
use common\components\rbac\Role;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * Site is the class for the table "site".
 * @property $power_factor_visibility
 * @property $id
 * @property $user_id
 * @property $name
 * @property $electric_company_id
 * @property $to_issue
 * @property SiteBillingSetting $relationSiteBillingSetting
 * @property Tenant[] relationTenantsToIssued
 * @property User relationUser
 * @property bool $status [tinyint(1)]
 * @property int $created_at [int(11)]
 * @property int $modified_at [int(11)]
 * @property int $created_by [int(11)]
 * @property int $modified_by [int(11)]
 * @property float $manual_cop [float]
 * @property float $manual_cop_geva [float]
 * @property float $manual_cop_pisga [float]
 * @property float $manual_cop_shefel [float]
 * @property string $old_id [varchar(255)]
 * @property int $cronjob_latest_meter_date_check [int(11)]
 * @property int $cronjob_latest_issue_date_check [int(11)]
 * @property string $auto_issue_reports
 */
class Site extends ActiveRecord
{
    const TO_ISSUE_NO = 1;
    const TO_ISSUE_AUTOMATIC = 2;
    const TO_ISSUE_MANUAL = 3;

    const POWER_FACTOR_SHOW_DONT_ADD_FUNDS = 3;
    const POWER_FACTOR_SHOW_ADD_FUNDS = 2;
    const POWER_FACTOR_DONT_SHOW = 1;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;


    public static function tableName() {
        return 'site';
    }


    public function rules() {
        return [
            [['name', 'electric_company_id'], 'filter', 'filter' => 'strip_tags'],
            [['name', 'electric_company_id'], 'filter', 'filter' => 'trim'],
            [['user_id', 'name'], 'required'],
            ['user_id', 'integer'],
            [['name'], 'string', 'max' => 255],
            [['electric_company_id'], 'string'],
            ['auto_issue_reports', 'string'],
            ['to_issue', 'default', 'value' => self::TO_ISSUE_NO],
            ['to_issue', 'in', 'range' => array_keys(self::getListToIssues()), 'skipOnEmpty' => true],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
            ['power_factor_visibility', 'in', 'range' => array_keys(Site::getListPowerFactors())],
            [['manual_cop', 'manual_cop_geva', 'manual_cop_pisga', 'manual_cop_shefel'], 'number']
        ];
    }


    public function attributeLabels() {
        return [
            'user_id' => Yii::t('common.site', 'Client'),
            'name' => Yii::t('common.site', 'Name'),
            'electric_company_id' => Yii::t('common.site', 'Electric company ID'),
            'to_issue' => Yii::t('common.tenant', 'To issue'),
            'status' => Yii::t('common.site', 'Status'),
            'created_at' => Yii::t('common.site', 'Created at'),
            'modified_at' => Yii::t('common.site', 'Modified at'),
            'created_by' => Yii::t('common.site', 'Created by'),
            'modified_by' => Yii::t('common.site', 'Modified by'),
            'auto_issue_reports' => Yii::t('common.site', 'Auto issue reports'),
            'old_id' => Yii::t('common.site', 'Old ID'),
            'rate_type_id' => Yii::t('common.site', 'Rate type'),
            'billing_day' => Yii::t('common.site', 'Day of billing'),
            'include_multiplier' => Yii::t('common.site', 'Include multipliers'),
            'include_vat' => Yii::t('common.site', 'Include VAT'),
            'comments' => Yii::t('common.site', 'Comments'),
            'fixed_addition_type' => Yii::t('common.site', 'Fixed addition of'),
            'fixed_addition_load' => Yii::t('common.site', 'Load as'),
            'fixed_addition_value' => Yii::t('common.site', 'Value (money, kwh or percentage)'),
            'fixed_addition_comment' => Yii::t('common.site', 'Comment for fixed addition'),
            'cronjob_latest_issue_date_check' => Yii::t('common.site', 'Latest automatic report issuing date'),
            'site_name' => Yii::t('common.site', 'Site name'),
            'square_meters' => Yii::t('common.site', 'Square meters'),
            'user_name' => Yii::t('common.site', 'Client name'),
            'issue_tenants' => Yii::t('common.site', 'Number of tenants to be issued'),
            'issue_dates' => Yii::t('common.site', 'Date range to issue'),
            'last_issue_date' => Yii::t('common.site', 'Last issue date'),
            'fixed_payment' => Yii::t('common.site', 'Fixed payment'),
            'manual_cop' => Yii::t('common.site', 'Manual COP'),
            'manual_cop_shefel' => Yii::t('common.site', 'Manual COP Shefel'),
            'manual_cop_geva' => Yii::t('common.site', 'Manual COP Geva'),
            'manual_cop_pisga' => Yii::t('common.site', 'Manual COP Pisga'),
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
        ];
    }


    public function getRelationUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    public function getRelationSiteBillingSetting() {
        return $this->hasOne(SiteBillingSetting::className(), ['site_id' => 'id']);
    }


    public function getRelationSiteIpAddresses() {
        return $this->hasMany(SiteIpAddress::className(), ['site_id' => 'id']);
    }


    public function getRelationTasks() {
        return $this->hasMany(Task::className(), ['site_id' => 'id']);
    }


    public function getRelationTenants() {
        return $this->hasMany(Tenant::className(), ['site_id' => 'id']);
    }


    public function getRelationTenantGroups() {
        return $this->hasMany(TenantGroup::className(), ['site_id' => 'id']);
    }

    public function getRelationTenantsToIssued() {
        $query = $this->hasMany(Tenant::className(), ['site_id' => 'id'])
                      ->andWhere([
                                     Tenant::tableName() . '.status' => Tenant::STATUS_ACTIVE,
                                 ])
                      ->andWhere(['in', Tenant::tableName() . '.to_issue', [
                          Site::TO_ISSUE_MANUAL,
                          Site::TO_ISSUE_AUTOMATIC,
                      ]])
                      ->leftJoin(RuleSingleChannel::tableName(), [
                          'and',
                          RuleSingleChannel::tableName() . '.tenant_id =' . Tenant::tableName() . '.id',
                          [RuleSingleChannel::tableName() . '.status' => RuleSingleChannel::STATUS_ACTIVE],
                      ])
                      ->leftJoin(RuleGroupLoad::tableName(), [
                          'and',
                          RuleGroupLoad::tableName() . '.tenant_id =' . Tenant::tableName() . '.id',
                          [RuleGroupLoad::tableName() . '.status' => RuleGroupLoad::STATUS_ACTIVE],
                      ])
                      ->leftJoin(RuleFixedLoad::tableName(), [
                          'and',
                          RuleFixedLoad::tableName() . '.tenant_id =' . Tenant::tableName() . '.id',
                          [RuleFixedLoad::tableName() . '.status' => RuleFixedLoad::STATUS_ACTIVE],
                      ])
                      ->andHaving([
                                      'or',
                                      'COUNT(' . RuleSingleChannel::tableName() . '.id) > 0',
                                      'COUNT(' . RuleGroupLoad::tableName() . '.id) > 0',
                                      'COUNT(' . RuleFixedLoad::tableName() . '.id) > 0',
                                  ])
                      ->orderBy([Tenant::tableName() . '.name' => SORT_ASC])
                      ->groupBy([Tenant::tableName() . '.id']);
        return $query;
    }


    public function getRelationTenantsIssued() {
        return $this->hasMany(Tenant::className(), ['site_id' => 'id'])->andWhere([
                                                                                      Tenant::tableName() .
                                                                                      '.status' => Tenant::STATUS_ACTIVE,
                                                                                  ])
                    ->leftJoin(RuleSingleChannel::tableName(), [
                        'and',
                        RuleSingleChannel::tableName() . '.tenant_id =' . Tenant::tableName() . '.id',
                        [RuleSingleChannel::tableName() . '.status' => RuleSingleChannel::STATUS_ACTIVE],
                    ])
                    ->leftJoin(RuleGroupLoad::tableName(), [
                        'and',
                        RuleGroupLoad::tableName() . '.tenant_id =' . Tenant::tableName() . '.id',
                        [RuleGroupLoad::tableName() . '.status' => RuleGroupLoad::STATUS_ACTIVE],
                    ])
                    ->leftJoin(RuleFixedLoad::tableName(), [
                        'and',
                        RuleFixedLoad::tableName() . '.tenant_id =' . Tenant::tableName() . '.id',
                        [RuleFixedLoad::tableName() . '.status' => RuleFixedLoad::STATUS_ACTIVE],
                    ])
                    ->andHaving([
                                    'or',
                                    'COUNT(' . RuleSingleChannel::tableName() . '.id) > 0',
                                    'COUNT(' . RuleGroupLoad::tableName() . '.id) > 0',
                                    'COUNT(' . RuleFixedLoad::tableName() . '.id) > 0',
                                ])
                    ->orderBy([Tenant::tableName() . '.name' => SORT_ASC])
                    ->groupBy([Tenant::tableName() . '.id']);
    }


    public function getRelationMeterChannelGroups() {
        return $this->hasMany(MeterChannelGroup::className(), ['site_id' => 'id']);
    }


    public function getRelationSiteContacts() {
        return $this->hasMany(SiteContact::className(), ['site_id' => 'id']);
    }


    public function getRelationMeters() {
        return $this->hasMany(Meter::className(), ['site_id' => 'id']);
    }


    public function getRelationReports() {
        return $this->hasMany(Report::className(), ['site_id' => 'id']);
    }


    public static function getListToIssues() {
        return [
            self::TO_ISSUE_AUTOMATIC => Yii::t('common.site', 'Yes, automatic'),
            self::TO_ISSUE_MANUAL => Yii::t('common.site', 'Yes, manual'),
            self::TO_ISSUE_NO => Yii::t('common.site', 'Not to issue'),
        ];
    }


    public function getAliasToIssue() {
        $list = self::getListToIssues();
        return (isset($list[$this->to_issue])) ? $list[$this->to_issue] : null;
    }


    public static function getListStatuses() {
        return [
            self::STATUS_INACTIVE => Yii::t('common.site', 'Inactive'),
            self::STATUS_ACTIVE => Yii::t('common.site', 'Active'),
        ];
    }


    public function getAliasStatus() {
        $list = self::getListStatuses();
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }


    public function getSquareMeters() {
        $rows = (new Query())->from(Tenant::tableName() . ' t')
                             ->andWhere([
                                            't.site_id' => $this->id,
                                        ])->andWhere(['in', 't.status', [
                Tenant::STATUS_ACTIVE,
            ]])->sum('IFNULL(t.square_meters, 0)');
        return $rows ? $rows : 0;
    }


    public function getIncludeVat() {
        return ($this->relationSiteBillingSetting != null) ? $this->relationSiteBillingSetting->include_vat : 0;
    }


    public function getComment() {
        return ($this->relationSiteBillingSetting != null) ? $this->relationSiteBillingSetting->comment : null;
    }


    public static function getListUsers() {
        $rows = (new Query())->select('t.id, t.name')->from(User::tableName() . ' t')
                             ->innerJoin(Site::tableName() . ' site', 'site.user_id = t.id')
                             ->andWhere(['role' => Role::ROLE_CLIENT]);
        if(!Yii::$app->user->can('ReportController.actionCreate')) {
            if(Yii::$app->user->can('ReportController.actionCreateOwner')) {
                $users_model = Yii::$app->user->identity->relationUserOwners;
                $user_ids = ArrayHelper::getColumn($users_model, 'user_id');
                array_unshift($user_ids, Yii::$app->user->id);
                $rows->andWhere(['t.id' => $user_ids]);
            }
            else {
                if(Yii::$app->user->can('ReportController.actionCreateSiteOwner')) {
                    $sites = UserOwnerSite::find()->where(['user_owner_id' => Yii::$app->user->id])->all();
                    $site_ids = ArrayHelper::getColumn($sites, 'site_id');
                    $rows->andWhere(['t.id' => $site_ids]);
                }
                else {
                    if(Yii::$app->user->can('SiteController.actionReportsSiteOwner')) {
                        // TO DO /site/meter-channel-group-create?id=353 with role site
                        $id_site = Yii::$app->request->getQueryParam('id');
                        $model_site = Site::findOne($id_site);
                        $rows->andWhere(['t.id' => $model_site->user_id]);
                    }
                }
            }
        }
        $rows = $rows->groupBy(['t.id'])->all();
        return ArrayHelper::map($rows, 'id', 'name');
    }


    public static function getListTenants($site_id) {
        $rows = (new Query())->select('t.id, t.name')->from(Tenant::tableName() . ' t')->andWhere([
                                                                                                      't.site_id' => $site_id,
                                                                                                  ])->andWhere(['in',
                                                                                                                't.status',
                                                                                                                [
                                                                                                                    Tenant::STATUS_INACTIVE,
                                                                                                                    Tenant::STATUS_ACTIVE,
                                                                                                                ]])
                             ->orderBy(['t.name' => SORT_ASC])->all();
        return ArrayHelper::map($rows, 'id', 'name');
    }


    public static function getListContacts($site_id) {
        $rows = (new Query())->select('t.id, t.name')->from(SiteContact::tableName() . ' t')->andWhere([
                                                                                                           't.site_id' => $site_id,
                                                                                                       ])
                             ->andWhere(['in', 't.status', [
                                 SiteContact::STATUS_INACTIVE,
                                 SiteContact::STATUS_ACTIVE,
                             ]])->orderBy(['t.name' => SORT_ASC])->all();
        return ArrayHelper::map($rows, 'id', 'name');
    }


    public static function getListMeters($id = null) {
        $query = (new Query())->select('t.id, t.name, type.name as type_name, type.channels, type.phases')
                              ->from(Meter::tableName() . ' t')
                              ->innerJoin(MeterType::tableName() . ' type', 'type.id = t.type_id')
                              ->andWhere([
                                             't.status' => Meter::STATUS_ACTIVE,
                                         ]);
        if($id) {
            $query->andWhere('t.site_id IS NULL OR t.site_id = :id', ['id' => $id]);
        }
        else {
            $query->andWhere('t.site_id IS NULL');
        }
        $rows = $query->orderBy(['t.name' => SORT_ASC])->all();
        return ArrayHelper::map($rows, 'id', function ($row) {
            return $row['name'] . ' - (' . Yii::t('common.meter',
                                                  '{name} - total {n, plural, =0{are no channels} =1{# channel} other{# channels}}',
                                                  [
                                                      'name' => $row['type_name'],
                                                      'n' => $row['channels'] * $row['phases'],
                                                  ]) . ')';
        });
    }


    static function loadSite($id) {
        $model = Site::find()->andWhere([
                                            'id' => $id,
                                        ])->andWhere(['in', 'status', [
            Site::STATUS_INACTIVE,
            Site::STATUS_ACTIVE,
        ]])->one();
        if($model == null) {
            throw new NotFoundHttpException(Yii::t('yii', 'Not not found'));
        }
        return $model;
    }


    public function getAutoIssueReports() {
        if(($auto_issue_reports = $this->auto_issue_reports) != null) {
            return Json::decode($auto_issue_reports);
        }
    }


    public function setAutoIssueReports($value) {
        $this->auto_issue_reports = Json::encode($value);
    }


    public static function getListIpAddresses($site_id) {
        return (new Query())->select(['ip_address'])->from(SiteIpAddress::tableName())->where(['site_id' => $site_id])
                            ->indexBy('ip_address')->column();
    }


    public static function getListPowerFactors() {
        return [
            self::POWER_FACTOR_SHOW_DONT_ADD_FUNDS => Yii::t('common.view', "Show but don't add funds"),
            self::POWER_FACTOR_SHOW_ADD_FUNDS => Yii::t('common.view', "Show and add funds"),
            self::POWER_FACTOR_DONT_SHOW => Yii::t('common.view', "Don't show at all"),
        ];
    }


    public function getIrregularHoursFrom() {
        return $this->relationSiteBillingSetting->getIrregularHoursFrom();
    }


    public function getIrregularHoursTo() {
        return $this->relationSiteBillingSetting->getIrregularHoursTo();
    }


    public function getMainMeters($type) {
        $main_meters = Meter::find()->where(['site_id' => $this->id])
                            ->andWhere(['is_main' => (int)true])
                            ->andWhere(['type' => $type])
                            ->all();
        return $main_meters;
    }


    /**
     * @param $type
     * @return MainMetersData[]
     */
    public function getMainSubChannels($type) {
        $subchannels = (new Query())->select('meter.name as meter_name,meter_subchannel.channel')
                                    ->from('meter_subchannel')
                                    ->innerJoin('meter_channel', 'meter_subchannel.channel_id = meter_channel.id')
                                    ->innerJoin('meter', 'meter.id = meter_channel.meter_id')
                                    ->andWhere(['meter_channel.is_main' => (int)true])
                                    //->andWhere(['meter.is_main' => (int)true])
                                    ->andWhere(['meter.type' => $type])
                                    ->andWhere(['meter.site_id' => $this->id]);
        $subchannels = Yii::$app->db->cache(function () use ($subchannels) {
            return $subchannels->all();
        });
        $main_meter_data = [];
        foreach($subchannels as $subchannel) {
            $main_meter_data[] = new MainMetersData($subchannel['meter_name'], $subchannel['channel']);
        }
        return $main_meter_data;
    }


    public function getRuleSubChannels(RuleSingleChannel $rule) {
        $subchannels = (new Query())->select('meter.name as meter_name,meter_subchannel.channel')
            ->from('meter_subchannel')
            ->innerJoin('meter_channel', 'meter_subchannel.channel_id = meter_channel.id')
            ->innerJoin('meter', 'meter.id = meter_channel.meter_id')
            ->andWhere(['meter.type' => Meter::TYPE_AIR])
            ->andWhere(['meter.id' => $rule->relationMeterChannel->relationMeter->id])
            ->andWhere(['meter.site_id' => $this->id]);
        $subchannels = Yii::$app->db->cache(function () use ($subchannels) {
            return $subchannels->all();
        });
        $main_meter_data = [];
        foreach($subchannels as $subchannel) {
            $main_meter_data[] = new MainMetersData($subchannel['meter_name'], $subchannel['channel']);
        }
        return $main_meter_data;
    }
}
