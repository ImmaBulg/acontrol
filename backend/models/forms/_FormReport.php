<?php
 
namespace backend\models\forms;

use common\models\helpers\reports\ReportGeneratorEnergy;
use common\models\helpers\reports\ReportGeneratorKwhPerSite;
use common\models\helpers\reports\ReportGeneratorMeters;
use common\models\helpers\reports\ReportGeneratorNisKwhPerSite;
use common\models\helpers\reports\ReportGeneratorNisPerSite;
use common\models\helpers\reports\ReportGeneratorRatesComprasion;
use common\models\helpers\reports\ReportGeneratorSummaryPerSite;
use common\models\helpers\reports\ReportGeneratorTenantBills;
use common\models\helpers\reports\ReportGeneratorYearly;
use \DateTime;
use Exception;
use Yii;
use yii\console\Request;
use yii\db\Query;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use common\helpers\Html;
use common\models\Task;
use common\models\Tenant;
use common\models\TenantReport;
use common\models\Site;
use common\models\User;
use common\models\Report;
use common\models\ReportFile;
use common\models\Rate;
use common\models\RuleSingleChannel;
use common\models\RuleGroupLoad;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterSubchannel;
use common\models\MeterChannelGroupItem;
use common\models\MeterRawData;
use common\widgets\Alert;
use common\components\i18n\Formatter;
use common\components\rbac\Role;
use common\models\events\logs\EventLogReport;
use common\models\helpers\reports\ReportGenerator;

/**
 * FormReport is the class for report create/edit.
 */
class FormReport extends \yii\base\Model
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
            case Report::TYPE_NIS:
            case Report::TYPE_KWH:
            case Report::TYPE_NIS_KWH:
            case Report::TYPE_RATES_COMPRASION:
            case Report::TYPE_TENANT_BILLS:
                if($this->tenants_id == null) {
                    $this->tenants_id = ArrayHelper::map($site->relationTenantsToIssued, 'id', 'id');
                }
                break;
            case Report::TYPE_SUMMARY:
            case Report::TYPE_YEARLY:
                if($this->tenants_id == null) {
                    $this->tenants_id = ArrayHelper::map($site->relationTenantsIssued, 'id', 'id');
                }
                break;
            default:
                break;
        }
        /**
         * Do validate
         */
        switch($this->type) {
            case Report::TYPE_NIS:
            case Report::TYPE_KWH:
            case Report::TYPE_NIS_KWH:
            case Report::TYPE_RATES_COMPRASION:
            case Report::TYPE_TENANT_BILLS:
            case Report::TYPE_YEARLY:
            case Report::TYPE_SUMMARY:
                $push_alerts = [];
                $missing_days = 0;
                $missing_datas = [];
                $tenants = ArrayHelper::index(Tenant::find()->andWhere(['in', 'id', $this->tenants_id])
                                                    ->orderBy(['name' => SORT_ASC])->all(), 'id');
                $from_date = ReportGenerator::getDayBeginning($this->from_date);
                $to_date = ReportGenerator::getDayEnding($this->to_date);
                foreach($tenants as $tenant) {
                    /**
                     * Is selected report type exists in tenant
                     */
                    if(!in_array($this->type, (array)$tenant->getIncludedReports())) {
                        unset($tenants[$tenant->id]);
                        unset($this->tenants_id[$tenant->id]);
                        continue;
                    }
                    /**
                     * Is Entrance/Exit dates are valid
                     */
                    $tenant_from_date = $from_date;
                    $tenant_to_date = $to_date;
                    $entrance_date = $tenant->entrance_date;
                    $exit_date = $tenant->exit_date;
                    // If tenant is set to issue automatically - set $is_automatically_generated = true
                    $to_issue = $tenant->to_issue;
                    if($to_issue == Site::TO_ISSUE_AUTOMATIC && Yii::$app->request instanceof Request) {
                        $this->is_automatically_generated = true;
                    }
                    else $this->is_automatically_generated = false;
                    if($entrance_date) {
                        $entrance_date = ReportGenerator::getDayBeginning($entrance_date);
                        if($entrance_date > $tenant_to_date) {
                            unset($tenants[$tenant->id]);
                            unset($this->tenants_id[$tenant->id]);
                            continue;
                        }
                        else if($entrance_date > $tenant_from_date) {
                            $tenant_from_date = $entrance_date;
                        }
                    }
                    if($exit_date) {
                        $exit_date = ReportGenerator::getDayEnding($exit_date);
                        if($exit_date < $tenant_from_date) {
                            unset($tenants[$tenant->id]);
                            unset($this->tenants_id[$tenant->id]);
                            continue;
                        }
                        else if($exit_date < $tenant_to_date) {
                            $tenant_to_date = $exit_date;
                        }
                    }
                    $from_reading_date = ReportGenerator::getDayBefore($tenant_from_date);
                    $to_reading_date = ReportGenerator::getDayEnding($tenant_to_date);
                    /**
                     * Is tenant have valid rate
                     */
                    $model_rate_start = Rate::find()->where([
                                                                'rate_type_id' => $tenant->getRateType(),
                                                                'status' => Rate::STATUS_ACTIVE,
                                                            ])->andWhere('start_date <= :start_date', [
                        'start_date' => ReportGenerator::getDayEnding($tenant_from_date)
                    ])->exists();
                    $model_rate_end = Rate::find()->where([
                                                              'rate_type_id' => $tenant->getRateType(),
                                                              'status' => Rate::STATUS_ACTIVE,
                                                          ])->andWhere('end_date >= :end_date', [
                        'end_date' => ReportGenerator::getDayBeginning($tenant_to_date)
                    ])->exists();
                    if(!$model_rate_start || !$model_rate_end) {
                        $this->addError('type', Yii::t('backend.report',
                                                       'There are no rates available of tenant {name} for the period you selected.',
                                                       [
                                                           'name' => $tenant->name,
                                                       ]));
                        return false;
                    }
                    /**
                     * Is tenant have readings for his rules
                     */
                    $sql_date_format = Formatter::SQL_DATE_FORMAT;
                    $single_rule_channels = (new Query())
                        ->select('t.channel_id')
                        ->from(RuleSingleChannel::tableName() . ' t')
                        ->andWhere([
                                       't.tenant_id' => $tenant->id,
                                       't.status' => RuleSingleChannel::STATUS_ACTIVE,
                                   ])->column();
                    $group_rule_main_channels = (new Query())
                        ->select('t.channel_id')
                        ->innerJoin(MeterChannel::tableName() . ' channel', 'channel.id = t.channel_id')
                        ->from(RuleGroupLoad::tableName() . ' t')
                        ->andWhere([
                                       't.tenant_id' => $tenant->id,
                                       't.status' => RuleGroupLoad::STATUS_ACTIVE,
                                   ])->column();
                    $group_rule_channels = (new Query())
                        ->select('group_item.channel_id')
                        ->from(RuleGroupLoad::tableName() . ' t')
                        ->innerJoin(MeterChannelGroupItem::tableName() . ' group_item',
                                    'group_item.group_id = t.channel_group_id')
                        ->andWhere([
                                       't.tenant_id' => $tenant->id,
                                       't.status' => RuleGroupLoad::STATUS_ACTIVE,
                                   ])->column();
                    $channels = array_unique(ArrayHelper::merge($single_rule_channels, $group_rule_channels,
                                                                $group_rule_main_channels));
                    if($channels != null) {
                        /**
                         * Detect missing data
                         */
                        foreach($channels as $channel_id) {
                            $meter = (new Query())
                                ->select('t.id, t.name')
                                ->from(Meter::tableName() . ' t')
                                ->innerJoin(MeterChannel::tableName() . ' meter_channel',
                                            'meter_channel.meter_id = t.id')
                                ->andWhere(['meter_channel.id' => $channel_id])
                                ->one();
                            $meter_id = ArrayHelper::getValue($meter, 'id');
                            $meter_name = ArrayHelper::getValue($meter, 'name');
                            $subchannels = (new Query())
                                ->select('t.id, t.channel')
                                ->from(MeterSubchannel::tableName() . ' t')
                                ->andWhere(['t.channel_id' => $channel_id])
                                ->all();
                            foreach($subchannels as $subchannel) {
                                $readings_from =
                                    array_filter(MeterRawData::getReadings($meter_name, $subchannel['channel'],
                                                                           $from_reading_date), function ($value) {
                                        return $value !== null;
                                    });
                                if($readings_from == null) {
                                    $missing_datas[$subchannel['id']]['meter_id'] = $meter_name;
                                    $missing_datas[$subchannel['id']]['channel_id'] = $subchannel['channel'];
                                    $missing_datas[$subchannel['id']]['dates'][$from_reading_date] =
                                        Yii::$app->formatter->asDate($from_reading_date, Formatter::PHP_DATE_FORMAT);
                                    $missing_datas[$subchannel['id']]['tenants'][] = $tenant->name;
                                    $push_alerts[$channel_id]['meter_id'] = $meter_id;
                                    $push_alerts[$channel_id]['channel_id'] = $channel_id;
                                    $push_alerts[$channel_id]['description'][$subchannel['channel'] . '-' .
                                                                             $from_reading_date] =
                                        Yii::t('backend.view',
                                               'Missing channel {channel} ({meter}) data for date: {date}', [
                                                   'meter' => $meter_name,
                                                   'channel' => $subchannel['channel'],
                                                   'date' => Yii::$app->formatter->asDate($from_reading_date,
                                                                                          Formatter::PHP_DATE_FORMAT),
                                               ]);
                                }
                                $readings_to =
                                    array_filter(MeterRawData::getReadings($meter_name, $subchannel['channel'],
                                                                           $to_reading_date), function ($value) {
                                        return $value !== null;
                                    });
                                if($readings_to == null) {
                                    $missing_datas[$subchannel['id']]['meter_id'] = $meter_name;
                                    $missing_datas[$subchannel['id']]['channel_id'] = $subchannel['channel'];
                                    $missing_datas[$subchannel['id']]['dates'][$to_reading_date] =
                                        Yii::$app->formatter->asDate($to_reading_date, Formatter::PHP_DATE_FORMAT);
                                    $missing_datas[$subchannel['id']]['tenants'][] = $tenant->name;
                                    $push_alerts[$channel_id]['meter_id'] = $meter_id;
                                    $push_alerts[$channel_id]['channel_id'] = $channel_id;
                                    $push_alerts[$channel_id]['description'][$subchannel['channel'] . '-' .
                                                                             $to_reading_date] = Yii::t('backend.view',
                                                                                                        'Missing channel {channel} ({meter}) data for date: {date}',
                                                                                                        [
                                                                                                            'meter' => $meter_name,
                                                                                                            'channel' => $subchannel['channel'],
                                                                                                            'date' => Yii::$app->formatter->asDate($from_reading_date,
                                                                                                                                                   Formatter::PHP_DATE_FORMAT),
                                                                                                        ]);
                                }
                                if($this->days_with_no_data) {
                                    $from_reading_date_min = $from_reading_date;
                                    while($from_reading_date_min <= $to_reading_date) {
                                        if($missing_days >= $this->days_with_no_data) {
                                            break;
                                        }
                                        $reading_min =
                                            array_filter(MeterRawData::getReadings($meter_name, $subchannel['channel'],
                                                                                   $from_reading_date_min),
                                                function ($value) {
                                                    return $value !== null;
                                                });
                                        if($reading_min == null) {
                                            $missing_days++;
                                        }
                                        $from_reading_date_min += 86400;
                                    }
                                }
                            }
                        }
                    }
                }
                /**
                 * Is tenant have readings for his rules
                 */
                if($missing_datas != null) {
                    foreach($missing_datas as $missing_data) {
                        $first_date = ArrayHelper::getValue(array_keys($missing_data['dates']), 0);
                        $last_date = ArrayHelper::getValue(array_keys($missing_data['dates']), 1, $first_date);
                        if(!(Yii::$app->request instanceof \yii\console\Request)) {
                            $link =
                                $this->getGoToUrl($missing_data['meter_id'], $missing_data['channel_id'], $first_date,
                                                  $last_date);
                            $errors[] = Yii::t('backend.report',
                                               'Missing channel {channel} ({meter}) data for dates: {dates}. Tenants: {tenants}',
                                               [
                                                   'meter' => $missing_data['meter_id'],
                                                   'channel' => $missing_data['channel_id'],
                                                   'dates' => implode(', ', $missing_data['dates']),
                                                   'tenants' => implode(', ', array_unique($missing_data['tenants'])),
                                               ]) . "<br>" . $link . "<br>";
                        }
                        else {
                            $errors[] = Yii::t('backend.report',
                                               'Missing channel {channel} ({meter}) data for dates: {dates}. Tenants: {tenants}',
                                               [
                                                   'meter' => $missing_data['meter_id'],
                                                   'channel' => $missing_data['channel_id'],
                                                   'dates' => implode(', ', $missing_data['dates']),
                                                   'tenants' => implode(', ', array_unique($missing_data['tenants'])),
                                               ]);
                        }
                    }
                    if(!(Yii::$app->request instanceof \yii\console\Request)) {
                        $this->addError('type', implode("<br>", $errors));
                    }
                    else {
                        $this->addError('type', implode("\n", $errors));
                    }
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
                    return false;
                }
                /**
                 * If tenant have rules with missing days
                 */
                if($this->days_with_no_data && $missing_days >= $this->days_with_no_data) {
                    $this->addError('type', Yii::t('backend.report',
                                                   'There is a {n} or more missing days during the report period ({dates}) for the site {site}.',
                                                   [
                                                       'n' => $missing_days,
                                                       'site' => $site->name,
                                                       'dates' => implode(' - ', [$this->from_date, $this->to_date]),
                                                   ]));
                    Task::addAlert(
                        $this->site_id,
                        Yii::t('backend.report',
                               'There is a {n} or more missing days during the report period ({dates}) for the site {site}.',
                               [
                                   'n' => $missing_days,
                                   'site' => $site->name,
                                   'dates' => implode(' - ', [$this->from_date, $this->to_date]),
                               ]),
                        time()
                    );
                    return false;
                }
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
        if($this->is_automatically_generated == true) {
            $power_factor_visibility = 1;
        }
        else {
            $power_factor_visibility = $site->power_factor_visibility;
        }
        $parameters = [
            'power_factor_visibility' => $power_factor_visibility
        ];
        Yii::$app->language = $reportLanguage;
        switch($this->type) {
            case Report::TYPE_NIS:
                /** @var ReportGenerator $reportClass */
                $reportClass = ReportGeneratorNisPerSite::class;
                $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT;
                $reportClass::$data_usage_method = $data_usage_method;
                $parameters = array_merge($parameters,array_filter([
                                               'electric_company_price' => $this->electric_company_price,
                                               'column_fixed_payment' => $this->column_fixed_payment,
                                               'column_total_pay_single_channel_rules' => $this->column_total_pay_single_channel_rules,
                                               'column_total_pay_group_load_rules' => $this->column_total_pay_group_load_rules,
                                               'column_total_pay_fixed_load_rules' => $this->column_total_pay_fixed_load_rules,
                                               'group_use_percent' => $this->group_use_percent,
                                               'is_vat_included' => $this->is_vat_included,
                                               'is_import_export_separatly' => $this->is_import_export_separatly,
                                           ]));
                $data = $reportClass::generate($this->from_date, $this->to_date, $site, $tenants, $parameters);
                $this->_data[$data_usage_method]['values'] = $data;
                $this->_data[$data_usage_method]['parameters'] = $parameters;
                if(($reportErrors = $reportClass::getErrors()) != null) {
                    foreach($reportErrors as $reportError) {
                        $errors[$reportError['type']][] = $reportError['message'];
                    }
                }
                break;
            case Report::TYPE_KWH:
                /** @var ReportGenerator $reportClass */
                $reportClass = ReportGeneratorKwhPerSite::class;
                $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT;
                $reportClass::$data_usage_method = $data_usage_method;
                $parameters = array_merge($parameters,array_filter([
                                               'electric_company_shefel' => $this->electric_company_shefel,
                                               'electric_company_geva' => $this->electric_company_geva,
                                               'electric_company_pisga' => $this->electric_company_pisga,
                                               'group_use_percent' => $this->group_use_percent,
                                               'is_import_export_separatly' => $this->is_import_export_separatly,
                                           ]));
                $data = $reportClass::generate($this->from_date, $this->to_date, $site, $tenants, $parameters);
                $this->_data[$data_usage_method]['values'] = $data;
                $this->_data[$data_usage_method]['parameters'] = $parameters;
                if(($reportErrors = $reportClass::getErrors()) != null) {
                    foreach($reportErrors as $reportError) {
                        $errors[$reportError['type']][] = $reportError['message'];
                    }
                }
                break;
            case Report::TYPE_NIS_KWH:
                /** @var ReportGenerator $reportClass */
                $reportClass = ReportGeneratorNisKwhPerSite::class;
                $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT;
                $reportClass::$data_usage_method = $data_usage_method;
                $parameters = array_merge($parameters,array_filter([
                                               'column_fixed_payment' => $this->column_fixed_payment,
                                               'electric_company_shefel' => $this->electric_company_shefel,
                                               'electric_company_geva' => $this->electric_company_geva,
                                               'electric_company_pisga' => $this->electric_company_pisga,
                                               'electric_company_price' => $this->electric_company_price,
                                               'group_use_percent' => $this->group_use_percent,
                                               'is_vat_included' => $this->is_vat_included,
                                               'is_import_export_separatly' => $this->is_import_export_separatly,
                                           ]));
                $data = $reportClass::generate($this->from_date, $this->to_date, $site, $tenants, $parameters);
                $this->_data[$data_usage_method]['values'] = $data;
                $this->_data[$data_usage_method]['parameters'] = $parameters;
                if(($reportErrors = $reportClass::getErrors()) != null) {
                    foreach($reportErrors as $reportError) {
                        $errors[$reportError['type']][] = $reportError['message'];
                    }
                }
                break;
            case Report::TYPE_METERS:
                /** @var ReportGenerator $reportClass */
                $reportClass = ReportGeneratorMeters::class;
                $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT;
                $reportClass::$data_usage_method = $data_usage_method;
                $parameters = array_merge($parameters,array_filter([
                                               'order_by' => $this->order_by,
                                           ]));
                $data = $reportClass::generate($this->from_date, $this->to_date, $site, [], $parameters);
                $this->_data[$data_usage_method]['values'] = $data;
                $this->_data[$data_usage_method]['parameters'] = $parameters;
                if(($reportErrors = $reportClass::getErrors()) != null) {
                    foreach($reportErrors as $reportError) {
                        $errors[$reportError['type']][] = $reportError['message'];
                    }
                }
                break;
            case Report::TYPE_SUMMARY:
                /** @var ReportGenerator $reportClass */
                $reportClass = ReportGeneratorSummaryPerSite::class;
                $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT;
                $reportClass::$data_usage_method = $data_usage_method;
                $data = $reportClass::generate($this->from_date, $this->to_date, $site, $tenants);
                $this->_data[$data_usage_method]['values'] = $data;
                $this->_data[$data_usage_method]['parameters'] = $parameters;
                if(($reportErrors = $reportClass::getErrors()) != null) {
                    foreach($reportErrors as $reportError) {
                        $errors[$reportError['type']][] = $reportError['message'];
                    }
                }
                break;
            case Report::TYPE_RATES_COMPRASION:
                /** @var ReportGenerator $reportClass */
                $reportClass = ReportGeneratorRatesComprasion::class;
                $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT;
                $reportClass::$data_usage_method = $data_usage_method;
                $parameters = array_merge($parameters,array_filter([
                                               'order_by' => $this->order_by,
                                               'electric_company_rate_low' => $this->electric_company_rate_low,
                                               'group_use_percent' => $this->group_use_percent,
                                           ]));
                $data = $reportClass::generate($this->from_date, $this->to_date, $site, $tenants, $parameters);
                $this->_data[$data_usage_method]['values'] = $data;
                $this->_data[$data_usage_method]['parameters'] = $parameters;
                if(($reportErrors = $reportClass::getErrors()) != null) {
                    foreach($reportErrors as $reportError) {
                        $errors[$reportError['type']][] = $reportError['message'];
                    }
                }
                break;
            case Report::TYPE_ENERGY:
                /** @var ReportGenerator $reportClass */
                $reportClass = ReportGeneratorEnergy::class;
                $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT;
                $reportClass::$data_usage_method = $data_usage_method;
                $data = $reportClass::generate($this->from_date, $this->to_date, $site);
                $this->_data[$data_usage_method]['values'] = $data;
                $this->_data[$data_usage_method]['parameters'] = $parameters;
                if(($reportErrors = $reportClass::getErrors()) != null) {
                    foreach($reportErrors as $reportError) {
                        $errors[$reportError['type']][] = $reportError['message'];
                    }
                }
                break;
            case Report::TYPE_YEARLY:
                /** @var ReportGenerator $reportClass */
                $reportClass = ReportGeneratorYearly::class;
                $data_usage_method = Meter::DATA_USAGE_METHOD_DEFAULT;
                $reportClass::$data_usage_method = $data_usage_method;
                $data = $reportClass::generate($this->from_date, $this->to_date, $site, $tenants);
                $this->_data[$data_usage_method]['values'] = $data;
                $this->_data[$data_usage_method]['parameters'] = $parameters;
                if(($reportErrors = $reportClass::getErrors()) != null) {
                    foreach($reportErrors as $reportError) {
                        $errors[$reportError['type']][] = $reportError['message'];
                    }
                }
                break;
            case Report::TYPE_TENANT_BILLS:
                /** @var ReportGenerator $reportClass */
                $data_usage_methods = ($this->is_import_export_separatly) ? [
                    Meter::DATA_USAGE_METHOD_IMPORT,
                    Meter::DATA_USAGE_METHOD_EXPORT,
                ] : [
                    Meter::DATA_USAGE_METHOD_DEFAULT,
                ];
                foreach($data_usage_methods as $data_usage_method) {
                    $reportClass = ReportGeneratorTenantBills::class;
                    $reportClass::$data_usage_method  = $data_usage_method;
                    $parameters = array_merge($parameters,array_filter([
                                                   'group_use_percent' => $this->group_use_percent,
                                               ]));
                    $data = $reportClass::generate($this->from_date, $this->to_date, $site, $tenants, $parameters);
                    $this->_data[$data_usage_method]['values'] = $data;
                    $this->_data[$data_usage_method]['parameters'] = $parameters;
                    if(($reportErrors = $reportClass::getErrors()) != null) {
                        foreach($reportErrors as $reportError) {
                            $errors[$reportError['type']][] = $reportError['message'];
                        }
                    }
                }
                break;
            default:
                break;
        }
        Yii::$app->language = $currentLanguage;
        if($errors != null) {
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
                        if(!(Yii::$app->request instanceof \yii\console\Request)) {
                            if(!$this->skip_errors) {
                                Yii::$app->session->setFlash($type, implode("<br>", [
                                    implode("<br>", $message),
                                    Html::a(Yii::t('backend.report', 'Skip error'),
                                            Url::current(['skip_errors' => true]),
                                            ['class' => 'btn btn-default btn-sm']),
                                ]));
                                return false;
                            }
                            else {
                                return true;
                            }
                        }
                        else {
                            $this->addError('type', implode("\r\n", $message));
                        }
                        break;
                }
            }
        }
        return true;
    }


    public function save() {
        //
        if(!$this->validate()) return false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach($this->_data as $type => $data) {
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
                $model->data_usage_method = $type;
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
                /************** START REPLACE ********/
                /**
                 * Fixed payment
                 * This block adds the rate fixed payment to the data bundle
                 *
                 */
                $__from_date = explode('-', $this->from_date);
                $__to_date = explode('-', $this->to_date);
                // find one actually (latest) fixed_payment on rates
                $modelRates = Rate::find()
                                  ->where('((:from_date >= start_date and :from_date <= end_date) OR (:to_date >= start_date and :to_date <= end_date)) AND rate_type_id = :rate_type_id AND status = :status',
                                          [
                                              ':from_date' => mktime(0, 0, 0, $__from_date[1], $__from_date[0],
                                                                     $__from_date[2]),
                                              ':to_date' => mktime(0, 0, 0, $__to_date[1], $__to_date[0],
                                                                   $__to_date[2]),
                                              ':rate_type_id' => 4, // it 'namuch'
                                              ':status' => Rate::STATUS_ACTIVE,
                                          ])
                                  ->orderBy(['end_date' => SORT_DESC])
                                  ->one();
                // find ALL fixed_payment on rates
                $modelRatesAll = Rate::find()
                                     ->where('((:from_date >= start_date and :from_date <= end_date) OR (:to_date >= start_date and :to_date <= end_date)) AND rate_type_id = :rate_type_id AND status = :status',
                                             [
                                                 ':from_date' => mktime(0, 0, 0, $__from_date[1], $__from_date[0],
                                                                        $__from_date[2]),
                                                 ':to_date' => mktime(0, 0, 0, $__to_date[1], $__to_date[0],
                                                                      $__to_date[2]),
                                                 ':rate_type_id' => 4, // it 'namuch'
                                                 ':status' => Rate::STATUS_ACTIVE,
                                             ])
                                     ->all();
                // add fixed_payments on all rates for different date
                $fixed_payment_all = [];
                $x = 0;
                foreach($modelRatesAll as $value) {
                    $fixed_payment_all[$x]['fixed_payment'] = $value->fixed_payment;
                    $fixed_payment_all[$x]['start_date'] = $value->start_date;
                    $fixed_payment_all[$x]['end_date'] = $value->end_date;
                    $x++;
                }
                $data['parameters']['rates_fixed_payments'] = $modelRates->fixed_payment; // one actually rate
                $data['parameters']['rates_fixed_payments_all'] = $fixed_payment_all; // all rates on different date
                /************** END REPLACE ************************/
                /**
                 * Generate pdf
                 */
                if($this->format_pdf) {
                    $model->generatePdf($data['values'], $data['parameters']);
                }
                /**
                 * Generate Excel
                 */
                if($this->format_excel) {
                    $model->generateExcel($data['values'], $data['parameters']);
                }
                /**
                 * Generate DAT
                 */
                if($this->format_dat) {
                    $model->generateDat($data['values'], $data['parameters']);
                }
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
