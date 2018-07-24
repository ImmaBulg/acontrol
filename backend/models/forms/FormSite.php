<?php

namespace backend\models\forms;

use Exception;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use common\models\User;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\Tenant;
use common\models\TenantBillingSetting;
use common\models\Rate;
use common\models\Report;
use common\models\RateType;
use common\components\rbac\Role;
use common\models\events\logs\EventLogSite;

/**
 * FormSite is the class for site create/edit.
 */
class FormSite extends Model
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_EDIT = 'edit';

    private $_id;

    public $user_id;
    public $name;
    public $electric_company_id;
    public $to_issue;

    public $rate_type_id;
    public $fixed_payment;
    public $billing_day;
    public $include_vat;
    public $comment;
    public $fixed_addition_type;
    public $fixed_addition_load;
    public $fixed_addition_value;
    public $fixed_addition_comment;
    public $auto_issue_reports;
    public $power_factor_visibility = Site::POWER_FACTOR_DONT_SHOW;
    public $irregular_hours_from = null;
    public $irregular_hours_to = null;
    public $irregular_additional_percent = null;

    public $manual_cop;
    public $manual_cop_pisga;
    public $manual_cop_geva;
    public $manual_cop_shefel;
    public $irregular_hours_data = [];

    public function rules() {
        return [
            [['name', 'electric_company_id'], 'filter', 'filter' => 'strip_tags'],
            [['name', 'electric_company_id'], 'filter', 'filter' => 'trim'],
            [['user_id', 'name', 'to_issue'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['electric_company_id'], 'string'],
            ['user_id', 'in', 'range' => array_keys(User::getListClients()), 'skipOnEmpty' => false],
            [['rate_type_id', 'billing_day'], 'required'],
            [['fixed_payment'], 'number', 'min' => 0],
            [['fixed_payment'], 'compare', 'compareValue' => 0, 'operator' => '>='],
            [['fixed_addition_value'], 'number'],
            [['include_vat'], 'default', 'value' => SiteBillingSetting::NO],
            [['include_vat'], 'boolean'],
            [['comment', 'fixed_addition_comment'], 'string'],
            ['to_issue', 'in', 'range' => array_keys(Site::getListToIssues()), 'skipOnEmpty' => false],
            ['billing_day', 'in', 'range' => array_keys(SiteBillingSetting::getListBillingDays()),
                'skipOnEmpty' => false],
            ['rate_type_id', '\common\components\validators\ModelExistsValidator',
                'modelClass' => '\common\models\RateType', 'modelAttribute' => 'id', 'filter' => function ($model) {
                return $model->andWhere(['in', 'status', [
                    RateType::STATUS_INACTIVE,
                    RateType::STATUS_ACTIVE,
                ]]);
            }],
            ['fixed_addition_type', 'in', 'range' => array_keys(SiteBillingSetting::getListFixedAdditionTypes()),
                'skipOnEmpty' => true],
            ['fixed_addition_load', 'in', 'range' => array_keys(SiteBillingSetting::getListFixedAdditionLoads()),
                'skipOnEmpty' => true],
            [['fixed_addition_load', 'fixed_addition_value'], 'required', 'when' => function ($model) {
                return $model->fixed_addition_type != null;
            }, 'enableClientValidation' => false],
            ['auto_issue_reports', 'each',
                'rule' => ['in', 'range' => array_keys(Report::getListTypes()), 'skipOnEmpty' => true]],
            ['power_factor_visibility', 'in', 'range' => array_keys(Site::getListPowerFactors())],
            [['irregular_hours_from', 'irregular_hours_to'], 'string'],
            ['irregular_additional_percent', 'number'],
            [['manual_cop', 'manual_cop_geva', 'manual_cop_pisga', 'manual_cop_shefel'], 'number'],
            ['irregular_hours_data', 'safe']
        ];
    }


    public function attributeLabels() {
        return [
            'name' => Yii::t('backend.site', 'Name'),
            'electric_company_id' => Yii::t('backend.site', 'Electric company ID'),
            'user_id' => Yii::t('backend.site', 'Client'),
            'to_issue' => Yii::t('backend.site', 'To issue'),
            'auto_issue_reports' => Yii::t('backend.site', 'Auto issue reports'),
            'rate_type_id' => Yii::t('backend.site', 'Rate type'),
            'fixed_payment' => Yii::t('backend.site', 'Fixed payment'),
            'billing_day' => Yii::t('backend.site', 'Day of billing'),
            'include_vat' => Yii::t('backend.site', 'Include VAT'),
            'comment' => Yii::t('backend.site', 'Comment'),
            'fixed_addition_type' => Yii::t('backend.site', 'Fixed addition of'),
            'fixed_addition_load' => Yii::t('backend.site', 'Load as'),
            'fixed_addition_value' => Yii::t('backend.site', 'Value (money, kwh or percentage)'),
            'fixed_addition_comment' => Yii::t('backend.site', 'Comment for fixed addition'),
        ];
    }


    public function loadAttributes($scenario, Site $model) {
        switch($scenario) {
            case self::SCENARIO_EDIT:
                $this->_id = $model->id;
                $this->user_id = $model->user_id;
                $this->name = $model->name;
                $this->electric_company_id = $model->electric_company_id;
                $this->to_issue = $model->to_issue;
                $this->auto_issue_reports = $model->getAutoIssueReports();
                $this->power_factor_visibility = $model->power_factor_visibility;
                $this->manual_cop = $model->manual_cop;
                $this->manual_cop_shefel = $model->manual_cop_shefel;
                $this->manual_cop_pisga = $model->manual_cop_pisga;
                $this->manual_cop_geva = $model->manual_cop_geva;

                $model_billing = $model->relationSiteBillingSetting;
                $this->rate_type_id = $model_billing->rate_type_id;
                $this->fixed_payment = $model_billing->fixed_payment;
                $this->billing_day = $model_billing->billing_day;
                $this->include_vat = $model_billing->include_vat;
                $this->comment = $model_billing->comment;
                $this->irregular_hours_from = $model_billing->irregular_hours_from;
                $this->irregular_hours_to = $model_billing->irregular_hours_to;
                $this->irregular_additional_percent = $model_billing->irregular_additional_percent;
                $this->fixed_addition_type = $model_billing->fixed_addition_type;
                $this->fixed_addition_load = $model_billing->fixed_addition_load;
                $this->fixed_addition_value = $model_billing->fixed_addition_value;
                $this->fixed_addition_comment = $model_billing->fixed_addition_comment;
                break;
            default:
                break;
        }
    }


    public function save() {
        if(!$this->validate()) return false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new Site();
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
            $model = Site::findOne($this->_id);
            $this->onSave($model);
            $transaction->commit();
            return $model;
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    public function onSave(Site $model) {
        $updated_attributes = [];
        $is_create = $model->isNewRecord;
        $model->user_id = $this->user_id;
        $model->name = $this->name;
        $model->electric_company_id = $this->electric_company_id;
        $model->to_issue = $this->to_issue;
        $model->power_factor_visibility = $this->power_factor_visibility;
        $model->manual_cop = $this->manual_cop;
        $model->manual_cop_geva = $this->manual_cop_geva;
        $model->manual_cop_pisga = $this->manual_cop_pisga;
        $model->manual_cop_shefel = $this->manual_cop_shefel;

        $model->setAutoIssueReports($this->auto_issue_reports);
        if($is_create) {
            $event = new EventLogSite();
            $event->model = $model;
            $model->on(EventLogSite::EVENT_AFTER_INSERT, [$event, EventLogSite::METHOD_CREATE]);
        }
        else {
            $updated_attributes = ArrayHelper::merge($model->getUpdatedAttributes(), $updated_attributes);
            if(ArrayHelper::getValue($model->getOldAttributes(), 'user_id') != $model->user_id) {
                Tenant::updateAll([
                    'user_id' => $model->user_id,
                ], [
                    'site_id' => $model->id,
                ]);
            }
        }
        if(!$model->save()) {
            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
        }
        if($is_create) {
            $model_billing = new SiteBillingSetting();
            $model_billing->site_id = $model->id;
        }
        else {
            $model_billing = $model->relationSiteBillingSetting;
        }
        $model_billing->rate_type_id = $this->rate_type_id;
        $model_billing->fixed_payment = $this->fixed_payment;
        $model_billing->billing_day = $this->billing_day;
        $model_billing->include_vat = $this->include_vat;
        $model_billing->comment = $this->comment;
        $model_billing->irregular_hours_from = $this->irregular_hours_from;
        $model_billing->irregular_hours_to = $this->irregular_hours_to;
        $model_billing->irregular_additional_percent = $this->irregular_additional_percent;
        switch($this->fixed_addition_type) {
            case SiteBillingSetting::FIXED_ADDITION_TYPE_MONEY:
            case SiteBillingSetting::FIXED_ADDITION_TYPE_KWH:
                $model_billing->fixed_addition_type = $this->fixed_addition_type;
                $model_billing->fixed_addition_load = $this->fixed_addition_load;
                $model_billing->fixed_addition_value = $this->fixed_addition_value;
                $model_billing->fixed_addition_comment = $this->fixed_addition_comment;
                break;
            default:
                $model_billing->fixed_addition_type = $this->fixed_addition_type;
                $model_billing->fixed_addition_load = null;
                $model_billing->fixed_addition_value = null;
                $model_billing->fixed_addition_comment = null;
                break;
        }
        if(!$is_create) {
            $updated_attributes = ArrayHelper::merge($model_billing->getUpdatedAttributes(), $updated_attributes);
        }
        if(!$model_billing->save()) {
            throw new BadRequestHttpException(implode(' ', $model_billing->getFirstErrors()));
        }
        if(!$is_create) {
            if($updated_attributes != null) {
                $event = new EventLogSite();
                $event->model = $model;
                $model->on(EventLogSite::EVENT_INIT, [$event, EventLogSite::METHOD_UPDATE]);
                $model->init();
            }
        }
    }
}
