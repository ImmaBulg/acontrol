<?php

namespace backend\models\forms;

use api\models\AirMeterRawData;
use Carbon\Carbon;
use common\components\i18n\Formatter;
use common\components\rbac\Role;
use common\components\validators\FormReportTenantValidator;
use common\exceptions\FormReportDontSkipErrors;
use common\exceptions\FormReportValidationContinueException;
use common\exceptions\FormReportValidationInterruptException;
use common\helpers\Html;
use common\helpers\TimeManipulator;
use common\models\AirRates;
use common\models\ElectricityMeterRawData;
use common\models\events\logs\EventLogReport;
use common\models\helpers\reports\ReportGenerator;
use common\models\helpers\reports\ReportGeneratorKwh;
use common\models\helpers\reports\ReportGeneratorNis;
use common\models\helpers\reports\ReportGeneratorNisKwh;
use common\models\helpers\reports\ReportGeneratorNisKwhPerSite;
use common\models\helpers\reports\ReportGeneratorTenantBills;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelGroupItem;
use common\models\MeterSubchannel;
use common\models\Rate;
use common\models\Report;
use common\models\RuleGroupLoad;
use common\models\RuleSingleChannel;
use common\models\Site;
use common\models\Task;
use common\models\Tenant;
use common\models\TenantReport;
use common\models\User;
use common\widgets\Alert;
use Exception;
use Yii;
use yii\base\Model;
use yii\console\Request;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;

/**
 * FormReport is the class for report create/edit.
 */
class FormReport extends Model
{
    protected $_report_errors = [];
    protected $_data = [];

    public $site_owner_id;
    public $site_id;
    public $tenants_id = [];
    public $from_date;
    public $to_date;
    public $level;
    public $type;
    public $is_public;
    public $parent_id;

    public $format_pdf = true;
    public $format_excel = false;
    public $format_dat = false;
    public $order_by;
    public $days_with_no_data;

    public $electric_company_shefel;
    public $electric_company_geva;
    public $electric_company_pisga;
    public $electric_company_rate_low;
    public $electric_company_price;

    public $column_fixed_payment = true;
    public $column_total_pay_single_channel_rules = true;
    public $column_total_pay_group_load_rules = true;
    public $column_total_pay_fixed_load_rules = true;

    public $group_use_percent;
    public $power_factor = true;
    public $skip_errors = false;
    public $is_vat_included = false;
    public $is_automatically_generated = false;
    public $is_import_export_separatly = false;

    public $report_calculation_type = null;

    public function rules() {
        return [
            [['from_date', 'to_date'], 'filter', 'filter' => 'trim'],
            [['site_owner_id', 'site_id', 'from_date', 'to_date', 'level', 'type'], 'required'],
            [['site_id', 'parent_id'], 'integer'],
            ['from_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
            ['to_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
            ['to_date', '\common\components\validators\DateTimeCompareValidator', 'compareAttribute' => 'from_date',
                'format' => Formatter::PHP_DATE_FORMAT, 'operator' => '>='],
            ['level', 'in', 'range' => array_keys(Report::getListLevels()), 'skipOnEmpty' => false],
            ['type', 'in', 'range' => array_keys(Report::getListTypes()), 'skipOnEmpty' => false],
            [['is_public'], 'default', 'value' => Report::NO],
            [['is_public'], 'boolean'],
            ['site_owner_id', '\common\components\validators\ModelExistsValidator',
                'modelClass' => '\common\models\User', 'modelAttribute' => 'id', 'filter' => function ($model) {
                return $model->innerJoin(Site::tableName() . ' site', 'site.user_id = ' . User::tableName() . '.id')
                    ->andWhere([
                        'site.id' => $this->site_id,
                        User::tableName() . '.role' => Role::ROLE_CLIENT,
                    ]);
            }],
            ['site_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Site',
                'modelAttribute' => 'id', 'filter' => function ($model) {
                return $model->andWhere(['status' => Site::STATUS_ACTIVE]);
            }],
            [['days_with_no_data'], 'integer', 'min' => 1],
            ['order_by', 'in', 'range' => array_keys(ReportGenerator::getListOrderBy()), 'skipOnEmpty' => true],
            [['electric_company_shefel', 'electric_company_geva', 'electric_company_pisga', 'electric_company_rate_low',
                'electric_company_price'], 'number', 'min' => 0],
            [['column_fixed_payment', 'column_total_pay_single_channel_rules', 'column_total_pay_group_load_rules',
                'column_total_pay_fixed_load_rules'], 'boolean'],
            [['format_pdf', 'format_excel', 'format_dat', 'group_use_percent', 'skip_errors',
                'is_automatically_generated', 'is_vat_included', 'is_import_export_separatly'], 'boolean'],
            ['tenants_id', 'each', 'rule' => ['integer']],
            ['type', 'validateType'],
            ['power_factor', 'in', 'range' => array_keys(Site::getListPowerFactors()),
                'skipOnEmpty' => true],
            ['report_calculation_type', 'safe']
        ];
    }


    public function validateType($attribute, $params) {
        switch($this->$attribute) {
            case Report::TYPE_TENANT_BILLS:
                if(!$this->format_pdf && !$this->format_excel && !$this->format_dat) {
                    return $this->addError('format_pdf',
                        Yii::t('backend.report', 'Please set at least one export format.'));
                }
                break;
            default:
                if(!$this->format_pdf && !$this->format_excel) {
                    return $this->addError('format_pdf',
                        Yii::t('backend.report', 'Please set at least one export format.'));
                }
                $this->format_dat = false;
                break;
        }
    }


    public function attributeLabels() {
        return [
            'from_date' => Yii::t('backend.report', 'From date'),
            'to_date' => Yii::t('backend.report', 'To date'),
            'type' => Yii::t('backend.report', 'Type'),
            'site_owner_id' => Yii::t('backend.report', 'Client'),
            'site_id' => Yii::t('backend.report', 'Site'),
            'tenants_id' => Yii::t('backend.report', 'Tenants'),
            'format_pdf' => Yii::t('backend.report', 'PDF Export'),
            'format_excel' => Yii::t('backend.report', 'Excel Export'),
            'format_dat' => Yii::t('backend.report', 'DAT Export '),
            'order_by' => Yii::t('backend.report', 'Reports order of sorting'),
            'is_public' => Yii::t('backend.report', 'Published to client'),
            'days_with_no_data' => Yii::t('backend.report', 'Number of days with no data'),
            'electric_company_shefel' => Yii::t('backend.report', 'Electric company shefel'),
            'electric_company_geva' => Yii::t('backend.report', 'Electric company geva'),
            'electric_company_pisga' => Yii::t('backend.report', 'Electric company pisga'),
            'electric_company_rate_low' => Yii::t('backend.report', 'Electric company low rate'),
            'electric_company_price' => Yii::t('backend.report', 'Electric company NIS'),
            'column_fixed_payment' => Yii::t('backend.report', 'Fixed payment'),
            'column_total_pay_single_channel_rules' => Yii::t('backend.report', 'Total to pay based on Single rules'),
            'column_total_pay_group_load_rules' => Yii::t('backend.report', 'Total to pay based on Group load rules'),
            'column_total_pay_fixed_load_rules' => Yii::t('backend.report', 'Total to pay based on Fixed load rules'),
            'group_use_percent' => Yii::t('backend.report', 'Group usage percentage'),
            'power_factor' => Yii::t('backend.report', 'Power factor'),
            'is_vat_included' => Yii::t('backend.report', 'Include VAT'),
            'is_import_export_separatly' => Yii::t('backend.report', 'Import seperatly, Export separatly'),
            'report_calculation_type' => Yii::t('backend.report', 'Report calculation type')
        ];
    }


    public function validate($attributeNames = null, $clearErrors = true) {
        if(!parent::validate()) return false;
        /**
         * Increase memory and set safe off time limit
         */
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $site = Site::findOne($this->site_id);
        $tenants = [];
        /**
         * Add predefined parameters
         */
        switch($this->type) {
            case Report::TYPE_TENANT_BILLS:
            case Report::TYPE_NIS_KWH:
            case Report::TYPE_NIS:
            case Report::TYPE_KWH:
                if($this->tenants_id == null) {
                    $this->tenants_id = ArrayHelper::map($site->relationTenantsToIssued, 'id', 'id');
                }
                break;
        }
		
        /**
         * Do validate
         */
        switch($this->type) {
            case Report::TYPE_TENANT_BILLS:
            case Report::TYPE_NIS:
            case Report::TYPE_NIS_KWH:
            case Report::TYPE_KWH:
                $push_alerts = [];
                $missing_data = [];
                /** @var Tenant[] $tenants */
                $tenants = ArrayHelper::index(Tenant::find()->andWhere(['in', 'id', $this->tenants_id])
                    ->orderBy(['name' => SORT_ASC])->all(), 'id');
                $this->from_date = TimeManipulator::getStartOfDay($this->from_date);
                $this->to_date = TimeManipulator::getEndOfDay($this->to_date);
                foreach($tenants as $tenant) {
                    $tenant_validator = new FormReportTenantValidator($tenant, $this, $this->from_date, $this->to_date);
                    try {
                        $validate_result = $tenant_validator->validate();
                        $tenant_push_alerts = $validate_result['push_alerts'];
                        $tenant_missing_data = $validate_result['missing_date'];
                        $missing_data = ArrayHelper::merge($missing_data, $tenant_missing_data);
                        $push_alerts = ArrayHelper::merge($push_alerts, $tenant_push_alerts);
                    }
                    catch(FormReportValidationContinueException $e) {
                        unset($tenants[$tenant->id]);
                        unset($this->tenants_id[$tenant->id]);
                        continue;
                    }
                    catch(FormReportValidationInterruptException $e) {
                        return false;
                    }
                }

				//print_r($this->tenants_id);
                /**
                 * Is tenant have readings for his rules
                 */
                /*if($missing_data != null) {
                    $this->processMissingData($missing_data);
                    $this->sendPushAlerts($push_alerts);
                    return false;
                }*/
                /**
                 * Is tenants list are not empty
                 */
				
                if($this->tenants_id == null) {
                    $this->addError('type', Yii::t('backend.report',
                        'There are no tenants with active rules for the site you selected.'));
                    return false;
                }
                break;
            default:
                break;
        }
        $errors = [];
        $currentLanguage = Yii::$app->language;
        $reportLanguage = Report::getReportLanguage();
        Yii::$app->language = $reportLanguage;
//        $power_factor_visibility = $this->initPowerFactorVisibility();
//        $parameters = [
//            'power_factor_visibility' => $power_factor_visibility,
//        ];
        switch($this->type) {
            case Report::TYPE_TENANT_BILLS:
                if (!array_key_exists($this->report_calculation_type,Report::getTenantBillReportTypes())) {
                    throw new BadRequestHttpException(Yii::t('backend.report', 'Report type not found'));
                }
                if (($this->report_calculation_type == Report::TENANT_BILL_REPORT_BY_MANUAL_COP)
                    && (empty($site->manual_cop)
                    || empty($site->manual_cop_geva)
                    || empty($site->manual_cop_pisga)
                    || empty($site->manual_cop_shefel))) {
                    $this->addError('type', Yii::t('backend.report', 'No manual COP found in site settings.'));
                    return false;
                }
                /** @var ReportGenerator $reportClass */
                $report_generator = new ReportGeneratorTenantBills($this->from_date, $this->to_date, $site, $tenants, $this->report_calculation_type);
                $this->_data = $report_generator->calculate();
                break;
            case Report::TYPE_NIS:
                $report_generator = new ReportGeneratorNis(
                    $this->from_date,
                    $this->to_date,
                    $site,
                    $tenants,
                    [
                        'is_vat_included' => $this->is_vat_included,
                        'column_fixed_payment' => $this->column_fixed_payment,
                        'column_total_pay_single_channel_rules' => $this->column_total_pay_single_channel_rules,
                        'column_total_pay_group_load_rules' => $this->column_total_pay_group_load_rules,
                        'column_total_pay_fixed_load_rules' => $this->column_total_pay_fixed_load_rules,
                        'electric_company_price' => $this->electric_company_price,
                        'report_calculation_type' => $this->report_calculation_type,
                    ]
                );
                $this->_data = $report_generator->calculate();
                break;
            case Report::TYPE_KWH:
                $report_generator = new ReportGeneratorKwh(
                    $this->from_date,
                    $this->to_date,
                    $site,
                    $tenants,
                    [
                        'electric_company_shefel' => $this->electric_company_shefel,
                        'electric_company_geva' => $this->electric_company_geva,
                        'electric_company_pisga' => $this->electric_company_pisga,
                        'report_calculation_type' => $this->report_calculation_type,
                        'electric_company_price' => $this->electric_company_price
                    ]
                );
                $this->_data = $report_generator->calculate();
                break;
            case Report::TYPE_NIS_KWH:
                $report_generator = new ReportGeneratorNisKwh($this->from_date, $this->to_date, $site, $tenants, [
                    'report_calculation_type' => $this->report_calculation_type,
                    'electric_company_price' => $this->electric_company_price,
                    'electric_company_shefel' => $this->electric_company_shefel,
                    'electric_company_geva' => $this->electric_company_geva,
                    'electric_company_pisga' => $this->electric_company_pisga
                ]);
                $this->_data = $report_generator->calculate();
                break;
            default:
                break;
        }
        Yii::$app->language = $currentLanguage;
        if($errors != null) {
            try {
                $this->processErrors($errors);
                return true;
            }
            catch(FormReportDontSkipErrors $e) {
                return false;
            }
        }
        //VarDumper::dump($this->_data, 100, true);
        return true;
    }


    private function processErrors($errors) {
        foreach($errors as $type => $message) {
            switch($type) {
                case Alert::ALERT_DANGER:
                    $this->addError('type', $message);
                    Task::addAlert(
                        $this->site_id,
                        implode("\r\n", $message),
                        time(),
                        Task::URGENCY_NORMAL,
                        Task::COLOR_RED
                    );
                    break;
                case Alert::ALERT_WARNING:
                default:
                    Task::addAlert(
                        $this->site_id,
                        implode("\r\n", $message),
                        time(),
                        Task::URGENCY_NORMAL,
                        Task::COLOR_ORANGE
                    );
                    if(!(Yii::$app->request instanceof Request)) {
                        if(!$this->skip_errors) {
                            Yii::$app->session->setFlash($type, implode("<br>", [
                                implode("<br>", $message),
                                Html::a(Yii::t('backend.report', 'Skip error'),
                                    Url::current(['skip_errors' => true]),
                                    ['class' => 'btn btn-default btn-sm']),
                            ]));
                            throw new FormReportDontSkipErrors();
                        }
                    }
                    else {
                        $this->addError('type', implode("\r\n", $message));
                    }
                    break;
            }
        }
    }


    private function initPowerFactorVisibility() {
        if($this->is_automatically_generated == true) {
            $power_factor_visibility = 1;
        }
        else {
            $power_factor_visibility = $this->site->power_factor_visibility;
        }
        return $power_factor_visibility;
    }


    private function sendPushAlerts($push_alerts) {
        foreach($push_alerts as $push_alert) {
            Task::addAlert(
                $this->site_id,
                implode("\r\n", $push_alert['description']),
                time(),
                Task::URGENCY_NORMAL,
                Task::COLOR_RED,
                $push_alert['meter_id'],
                $push_alert['channel_id']
            );
        }
    }


    private function processMissingData($missing_data) {
        foreach($missing_data as $missing_entry) {
            $first_date = ArrayHelper::getValue(array_keys($missing_entry['dates']), 0);
            $last_date = ArrayHelper::getValue(array_keys($missing_entry['dates']), 1, $first_date);
            $last_date = Yii::$app->formatter->asDate(Yii::$app->formatter->asTimestamp($first_date) + 30*86400);
            if(!(Yii::$app->request instanceof Request)) {
                $link =
                    $this->getGoToUrl($missing_entry['meter_id'], $missing_entry['channel_id'], $first_date,
                        $last_date);
                $errors[] = Yii::t('backend.report',
                        'Missing channel {channel} ({meter}) data for dates: {dates}. Tenants: {tenants}',
                        [
                            'meter' => $missing_entry['meter_id'],
                            'channel' => $missing_entry['channel_id'],
                            'dates' => implode(', ', $missing_entry['dates']),
                            'tenants' => implode(', ', array_unique($missing_entry['tenants'])),
                        ]) . "<br>" . $link . "<br>";
            }
            else {
                $errors[] = Yii::t('backend.report',
                    'Missing channel {channel} ({meter}) data for dates: {dates}. Tenants: {tenants}',
                    [
                        'meter' => $missing_entry['meter_id'],
                        'channel' => $missing_entry['channel_id'],
                        'dates' => implode(', ', $missing_entry['dates']),
                        'tenants' => implode(', ', array_unique($missing_entry['tenants'])),
                    ]);
            }
        }
        if(!(Yii::$app->request instanceof Request)) {
            $this->addError('type', implode("<br>", $errors));
        }
        else {
            $this->addError('type', implode("\n", $errors));
        }
    }


    public function save() {
        //
        if(!$this->validate()) return false;

//        VarDumper::dump($this->_data, 100, true);
        $transaction = Yii::$app->db->beginTransaction();
        try {
//            foreach($this->_data as $type => $data) {
            $model = new Report();
            $model->site_owner_id = $this->site_owner_id;
            $model->site_id = $this->site_id;
            $model->from_date = $this->from_date;
            $model->to_date = $this->to_date;
            $model->level = $this->level;
            $model->type = $this->type;
            $model->is_public = $this->is_public;
            $model->parent_id = $this->parent_id;
            $model->is_automatically_generated = $this->is_automatically_generated;
            $model->data_usage_method = Meter::DATA_USAGE_METHOD_IMPORT;
            $event = new EventLogReport();
            $event->model = $model;
            $model->on(EventLogReport::EVENT_AFTER_INSERT, [$event, EventLogReport::METHOD_CREATE]);
            if(!$model->save()) {
                throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
            }
            foreach($this->tenants_id as $tenant_id) {
                $model_tenant_report = new TenantReport();
                $model_tenant_report->tenant_id = $tenant_id;
                $model_tenant_report->report_id = $model->id;
                if(!$model_tenant_report->save()) {
                    throw new BadRequestHttpException(implode(' ', $model_tenant_report->getFirstErrors()));
                }
            }
            if($this->format_pdf) {
                $model->generatePdf($this->_data, []);
            }
            if($this->format_excel) {
                $params = [];
                switch($this->type) {
                    case Report::TYPE_TENANT_BILLS:
                        //VarDumper::dump($this->_data, 100, true);
                        break;
                    case Report::TYPE_NIS:
                        $params = $this->_data['params'];
                        unset($this->_data['[params']);
                        break;
                    case Report::TYPE_KWH:
                        $params = $this->_data['electrical_company'];
                        unset($this->_data['electrical_company']);
                        break;
                    case Report::TYPE_NIS_KWH:
                        $params = $this->_data['site_total'];
                        $params['electric_company_price'] =  $this->_data['electric_company_price'];
                        $params['electric_company_shefel'] =  $this->_data['electric_company_shefel'];
                        $params['electric_company_geva'] =  $this->_data['electric_company_geva'];
                        $params['electric_company_pisga'] =  $this->_data['electric_company_pisga'];
                        $params['diff'] = $this->_data['diff'];
                        unset($this->_data['electric_company_price']);
                        unset($this->_data['electric_company_geva']);
                        unset($this->_data['electric_company_pisga']);
                        unset($this->_data['electric_company_shefel']);
                        unset($this->_data['site_total']);
                        unset($this->_data['diff']);

                        //VarDumper::dump($this->_data, 100, true);
                        break;
                }
                $model->generateExcel($this->_data, $params);

            }

            $transaction->commit();
            if(!(Yii::$app->request instanceof \yii\console\Request)) {
                $session = Yii::$app->session;
                $session->set('issue_from_date', $this->from_date);
                $session->set('issue_to_date', $this->to_date);
            }
            return true;
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new BadRequestHttpException($e->getMessage());
        }

    }


    public function getGoToUrl($meter_id, $channel_id, $from_date, $to_date) {
        switch($this->level) {
            case Report::LEVEL_SITE:
                return FormMeterRawDataFilter::getGoToUrl($meter_id, $channel_id, [
                    'from_date' => Yii::$app->formatter->asDate($from_date),
                    'to_date' => Yii::$app->formatter->asDate($to_date),
                    'go_back_source' => FormMeterRawDataFilter::GO_BACK_SOURCE_SITE_REPORT,
                    'go_back_url' => Url::current(),
                ], ['class' => 'btn btn-sm btn-default']);
                break;
            case Report::LEVEL_TENANT:
            default:
                return FormMeterRawDataFilter::getGoToUrl($meter_id, $channel_id, [
                    'from_date' => Yii::$app->formatter->asDate($from_date),
                    'to_date' => Yii::$app->formatter->asDate($to_date),
                    'go_back_source' => FormMeterRawDataFilter::GO_BACK_SOURCE_TENANT_REPORT,
                    'go_back_url' => Url::current(),
                ], ['class' => 'btn btn-sm btn-default']);
                break;
        }
    }
}
