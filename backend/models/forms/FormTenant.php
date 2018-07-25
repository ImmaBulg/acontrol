<?php

namespace backend\models\forms;

use common\components\i18n\Formatter;
use common\models\events\logs\EventLogTenant;
use common\models\RateType;
use common\models\Report;
use common\models\RuleSingleChannel;
use common\models\Site;
use common\models\Tenant;
use common\models\TenantBillingSetting;
use Exception;
use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * FormTenant is the class for site tenant create/edit.
 */
class FormTenant extends Model
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_EDIT = 'edit';

    const DEFAULT_ENTRANCE_DATE = '01-01-2010';

    private $_id;

    public $site_id;
    public $name;
    public $type;
    public $to_issue;
    public $square_meters = 0;
    public $entrance_date = self::DEFAULT_ENTRANCE_DATE;
    public $exit_date;
    public $hide_drilldown;

    public $rate_type_id;
    public $comment;
    public $fixed_payment;
    public $irregular_hours_from;
    public $site_irregular_hours_from;
    public $irregular_hours_to;
    public $site_irregular_hours_to;
    public $irregular_additional_percent;
    public $site_irregular_additional_percent;
    public $id_with_client;
    public $accounting_number;
    public $billing_content;
    public $included_in_cop;
    public $included_reports;
    public $is_visible_on_dat_file = true;

    public $site_rate;
    public $site_fixed_payment;

    // Barcode settings
    public $prefix; // number
    public $ending; // number
    public $client_code; // number
    public $contract_id; // number
    public $property_id; // number
    public $formatting; // format: 1@property@contract@code@date@total@0
    public $option_visible_barcode = false; // checkbox - display of barcode on the tenant bill


    public function rules() {
        return [
            [['name'], 'filter', 'filter' => 'strip_tags'],
            [['name', 'entrance_date', 'exit_date'], 'filter', 'filter' => 'trim'],
            [['site_id', 'name', 'type', 'to_issue', 'square_meters', 'entrance_date'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['square_meters'], 'number', 'min' => 0],
            ['entrance_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
            ['exit_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
            ['exit_date', '\common\components\validators\DateTimeCompareValidator',
                'compareAttribute' => 'entrance_date', 'format' => Formatter::PHP_DATE_FORMAT, 'operator' => '>='],
            ['to_issue', 'in', 'range' => array_keys(Site::getListToIssues()), 'skipOnEmpty' => false],
            ['type', 'in', 'range' => array_keys(Tenant::getListTypes()), 'skipOnEmpty' => false],
            ['site_id', 'in', 'range' => array_keys(Tenant::getListSites()), 'skipOnEmpty' => false],
            [['comment', 'billing_content', 'id_with_client', 'accounting_number'], 'filter', 'filter' => 'strip_tags'],
            [['comment', 'billing_content', 'id_with_client', 'accounting_number'], 'filter', 'filter' => 'trim'],
            [['fixed_payment'], 'number', 'min' => 0],
            //            [['fixed_payment'], 'compare', 'compareValue' => 0, 'operator' => '>='],
            [['comment', 'billing_content'], 'string'],
            [['id_with_client', 'accounting_number'], 'string', 'max' => 255],
            ['rate_type_id', '\common\components\validators\ModelExistsValidator',
                'modelClass' => '\common\models\RateType', 'modelAttribute' => 'id', 'filter' => function ($model) {
                return $model->andWhere(['in', 'status', [
                    RateType::STATUS_INACTIVE,
                    RateType::STATUS_ACTIVE,
                ]]);
            }],
            ['included_reports', 'each',
                'rule' => ['in', 'range' => array_keys(Report::getListTypes()), 'skipOnEmpty' => true]],
            ['to_issue', 'validateToIssue', 'when' => function ($model) {
                return $model->to_issue != Site::TO_ISSUE_NO;
            }],
            [['hide_drilldown'], 'default', 'value' => Tenant::NO],
            [['is_visible_on_dat_file'], 'default', 'value' => Tenant::YES],
            [['hide_drilldown', 'is_visible_on_dat_file'], 'boolean'],
            // Barcode settings
            [['prefix', 'ending', 'contract_id', 'property_id'], 'number', 'min' => 0],
            [['client_code'], 'string', 'min' => 0],
            //[['formatting'], 'match', 'pattern' => '/^(@(property|contract|code|date|total){1})$/', 'message' => 'Error pattern!'],
            // todo: change pattern - exclude repetitions
            [['formatting'], 'match', 'pattern' => '/^(@(prefix|property|contract|code|date|total|ending){1})+$/',
                'message' => 'Error pattern!'],
            [['option_visible_barcode'], 'default', 'value' => Tenant::YES],
            [['option_visible_barcode'], 'boolean'],
            ['included_in_cop', 'integer'],
            ['irregular_additional_percent', 'number'],
            [['irregular_hours_from', 'irregular_hours_to'], 'string'],
        ];
    }


    public function validateToIssue($attribute, $params) {
        $tenant = Tenant::findOne([
            'id' => $this->_id,
            'status' => Tenant::STATUS_ACTIVE,
        ]);
        if($tenant == null) return false;
        $rule_single_channels = (new Query())
            ->select(['t.channel_id'])
            ->from(RuleSingleChannel::tableName() . ' t')
            ->leftJoin(Tenant::tableName() . ' tenant', ' tenant.id = t.tenant_id')
            ->andWhere([
                'and',
                ['t.tenant_id' => $tenant->id],
                ['t.use_type' => RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD],
                ['t.total_bill_action' => RuleSingleChannel::TOTAL_BILL_ACTION_PLUS],
                ['t.status' => RuleSingleChannel::STATUS_ACTIVE],
                ['tenant.status' => RuleSingleChannel::STATUS_ACTIVE],
            ])
            ->column();
        if($rule_single_channels == null) return false;
        foreach($rule_single_channels as $rule_single_channel) {
            $result = (new Query())
                ->select(['SUM(t.use_percent) as sum', 'GROUP_CONCAT(tenant.name SEPARATOR ", ") as tenants'])
                ->from(RuleSingleChannel::tableName() . ' t')
                ->leftJoin(Tenant::tableName() . ' tenant', ' tenant.id = t.tenant_id')
                ->andWhere([
                    'and',
                    ['t.channel_id' => $rule_single_channel],
                    ['t.use_type' => RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD],
                    ['t.total_bill_action' => RuleSingleChannel::TOTAL_BILL_ACTION_PLUS],
                    ['tenant.status' => Tenant::STATUS_ACTIVE],
                    [
                        'or',
                        ['in', 'tenant.to_issue', [
                            Site::TO_ISSUE_AUTOMATIC,
                            Site::TO_ISSUE_MANUAL,
                        ]],
                        ['tenant.id' => $tenant->id],
                    ],
                    [
                        'or',
                        'tenant.exit_date IS NULL',
                        ['>', 'tenant.exit_date', strtotime('midnight')],
                    ],
                ])
                ->one();
            if($result != null) {
                $sum = ArrayHelper::getValue($result, 'sum', 0);
                $tenants = explode(', ', ArrayHelper::getValue($result, 'tenants'));
                unset($tenants[array_search($tenant->name, $tenants)]);
                if($sum > RuleSingleChannel::MAX_PERCENT) {
                    return $this->addError($attribute, Yii::t('backend.rule',
                        'This tenant has conflict rules with next tenants: {tenants}.',
                        [
                            'attribute' => $this->getAttributeLabel($attribute),
                            'tenants' => implode(', ', $tenants),
                        ]));
                }
            }
        }
    }


    public function attributeLabels() {
        return [
            'name' => Yii::t('backend.tenant', 'Name'),
            'type' => Yii::t('backend.tenant', 'Type'),
            'to_issue' => Yii::t('backend.tenant', 'To issue'),
            'square_meters' => Yii::t('backend.tenant', 'Square meters'),
            'entrance_date' => Yii::t('backend.tenant', 'Entrance date'),
            'exit_date' => Yii::t('backend.tenant', 'Exit date'),
            'site_id' => Yii::t('backend.tenant', 'Site'),
            'hide_drilldown' => Yii::t('backend.tenant', 'Hide drilldown on tenant bill'),
            'is_visible_on_dat_file' => Yii::t('backend.tenant', 'Show in DAT file'),
            'rate_type_id' => Yii::t('backend.tenant', 'Rate type'),
            'comment' => Yii::t('backend.tenant', 'Comment'),
            'fixed_payment' => Yii::t('backend.tenant', 'Fixed payment'),
            'id_with_client' => Yii::t('backend.tenant', 'ID with client'),
            'accounting_number' => Yii::t('backend.tenant', 'Accounting number'),
            'billing_content' => Yii::t('backend.tenant', 'Billing content'),
            'included_reports' => Yii::t('backend.tenant', 'Included reports'),
            'site_rate' => Yii::t('backend.tenant', 'Site rate type'),
            'site_fixed_payment' => Yii::t('backend.tenant', 'Site fixed payment'),
            // Barcode settings
            'prefix' => Yii::t('backend.tenant', 'Prefix'),
            'ending' => Yii::t('backend.tenant', 'Ending'),
            'client_code' => Yii::t('backend.tenant', 'Client code'),
            'contract_id' => Yii::t('backend.tenant', 'Contract id'),
            'property_id' => Yii::t('backend.tenant', 'Property id'),
            'formatting' => Yii::t('backend.tenant', 'Formatting'),
            'option_visible_barcode' => Yii::t('backend.tenant', 'Display of barcode on the tenant bill'),
            'included_in_cop' => Yii::t('backend.tenant', 'Do not include this tenant in COP calculation for No main meters method'),
        ];
    }


    public function loadAttributes($scenario, Tenant $model) {
        switch($scenario) {
            case self::SCENARIO_EDIT:
                $this->_id = $model->id;
                $this->site_id = $model->site_id;
                $this->name = $model->name;
                $this->type = $model->type;
                $this->to_issue = $model->to_issue;
                $this->square_meters = $model->square_meters;
                $this->entrance_date = $model->entrance_date;
                $this->exit_date = $model->exit_date;
                $this->hide_drilldown = $model->hide_drilldown;
                $this->is_visible_on_dat_file = $model->is_visible_on_dat_file;
                $this->included_reports = $model->getIncludedReports();
                $model_billing = $model->relationTenantBillingSetting;
                // Barcode settings
                $this->prefix = $model->prefix;
                $this->ending = $model->ending;
                $this->client_code = $model->client_code;
                $this->contract_id = $model->contract_id;
                $this->property_id = $model->property_id;
                $this->formatting = $model->formatting;
                $this->option_visible_barcode = $model->option_visible_barcode;
                $this->included_in_cop = $model->included_in_cop;
                if($model_billing instanceof TenantBillingSetting) {
                    $this->rate_type_id = $model_billing->rate_type_id;
                    if ($this->rate_type_id == null)
                        $this->rate_type_id = $model->relationSite->relationSiteBillingSetting->rate_type_id;
                    $this->comment = $model_billing->comment;
                    $this->fixed_payment = $model_billing->fixed_payment;
                    $this->irregular_hours_from = $model_billing->irregular_hours_from;
                    $this->irregular_hours_to = $model_billing->irregular_hours_to;
                    $this->irregular_additional_percent = $model_billing->irregular_additional_percent;
                    $this->id_with_client = $model_billing->id_with_client;
                    $this->accounting_number = $model_billing->accounting_number;
                    $this->billing_content = $model_billing->billing_content;
                }
                break;
            default:
                break;
        }
    }


    public function save() {
        if(!$this->validate()) return false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new Tenant();
            $this->onSave($model);
            $transaction->commit();
            return $model;
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new BadRequestHttpException($e->getMessage());
        }
    }


    public function edit() {
        if(!$this->validate()) return false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = Tenant::findOne($this->_id);
            $this->onSave($model);
            $transaction->commit();
            return $model;
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new BadRequestHttpException($e->getMessage());
        }
    }


    public function onSave($model) {
        $is_create = $model->isNewRecord;
        $updated_attributes = [];
        $model_site = Site::findOne($this->site_id);
        if($is_create) {
            $model = new Tenant();
        }
        else {
            $model = Tenant::findOne($this->_id);
        }
        $model->user_id = $model_site->user_id;
        $model->site_id = $model_site->id;
        $model->name = $this->name;
        $model->type = $this->type;
        $model->to_issue = $this->to_issue;
        $model->square_meters = $this->square_meters;
        $model->entrance_date = $this->entrance_date;
        $model->exit_date = $this->exit_date;
        $model->hide_drilldown = $this->hide_drilldown;
        $model->is_visible_on_dat_file = $this->is_visible_on_dat_file;
        $model->setIncludedReports($this->included_reports);
        // Barcode settings
        $model->prefix = $this->prefix;
        $model->ending = $this->ending;
        $model->client_code = $this->client_code;
        $model->contract_id = $this->contract_id;
        $model->property_id = $this->property_id;
        $model->formatting = $this->formatting;
        $model->option_visible_barcode = $this->option_visible_barcode;
        $model->included_in_cop = $this->included_in_cop;
        if($is_create) {
            $event = new EventLogTenant();
            $event->model = $model;
            $model->on(EventLogTenant::EVENT_AFTER_INSERT, [$event, EventLogTenant::METHOD_CREATE]);
        }
        else {
            $updated_attributes = ArrayHelper::merge($model->getUpdatedAttributes(), $updated_attributes);
        }
        if(!$model->save()) {
            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
        }
        $model_billing = $model->relationTenantBillingSetting;
        if($model_billing == null) {
            $model_billing = new TenantBillingSetting();
            $model_billing->tenant_id = $model->id;
        }
        $model_billing->site_id = $model_site->id;
        if ($this->rate_type_id == null)
            $model_billing->rate_type_id = $model->relationSite->relationSiteBillingSetting->rate_type_id;
        else
            $model_billing->rate_type_id = $this->rate_type_id;
        $model_billing->comment = $this->comment;
        $model_billing->fixed_payment = $this->fixed_payment;
        $model_billing->irregular_hours_from = $this->irregular_hours_from;
        $model_billing->irregular_hours_to = $this->irregular_hours_to;
        $model_billing->irregular_additional_percent = $this->irregular_additional_percent;
        $model_billing->id_with_client = $this->id_with_client;
        $model_billing->accounting_number = $this->accounting_number;
        $model_billing->billing_content = $this->billing_content;
        if(!$is_create) {
            $updated_attributes = ArrayHelper::merge($model_billing->getUpdatedAttributes(), $updated_attributes);
        }
        if(!$model_billing->save()) {
            throw new BadRequestHttpException(implode(' ', $model_billing->getFirstErrors()));
        }
        if($is_create && !empty($updated_attributes)) {
            $event = new EventLogTenant();
            $event->model = $model;
            $model->on(EventLogTenant::EVENT_INIT, [$event, EventLogTenant::METHOD_UPDATE]);
            $model->init();
        }
    }


    public static function getListToIssueIncludedReports() {
        return [
            Site::TO_ISSUE_NO => [
                Report::TYPE_NIS => false,
                Report::TYPE_KWH => false,
                Report::TYPE_SUMMARY => true,
                Report::TYPE_METERS => false,
                Report::TYPE_NIS_KWH => false,
                Report::TYPE_RATES_COMPRASION => false,
                Report::TYPE_TENANT_BILLS => false,
                Report::TYPE_YEARLY => false,
                Report::TYPE_ENERGY => false,
            ],
            Site::TO_ISSUE_AUTOMATIC => [
                Report::TYPE_NIS => true,
                Report::TYPE_KWH => true,
                Report::TYPE_SUMMARY => true,
                Report::TYPE_METERS => true,
                Report::TYPE_NIS_KWH => true,
                Report::TYPE_RATES_COMPRASION => true,
                Report::TYPE_TENANT_BILLS => true,
                Report::TYPE_YEARLY => true,
                Report::TYPE_ENERGY => true,
            ],
            Site::TO_ISSUE_MANUAL => [
                Report::TYPE_NIS => true,
                Report::TYPE_KWH => true,
                Report::TYPE_SUMMARY => true,
                Report::TYPE_METERS => true,
                Report::TYPE_NIS_KWH => true,
                Report::TYPE_RATES_COMPRASION => true,
                Report::TYPE_TENANT_BILLS => true,
                Report::TYPE_YEARLY => true,
                Report::TYPE_ENERGY => true,
            ],
        ];
    }
}

