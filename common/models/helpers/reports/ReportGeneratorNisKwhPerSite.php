<?php

namespace common\models\helpers\reports;

use common\helpers\TimeManipulator;
use common\models\Meter;
use common\models\Site;
use common\models\Tenant;
use Yii;
use yii\helpers\ArrayHelper;

class ReportGeneratorNisKwhPerSite extends ReportGenerator implements IReportGenerator
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
    public static function generate($report_from_date, $report_to_date, Site $site, $tenants = [], array $params = []) {
        $site_owner = $site->relationUser;
        $vat_included = $site->getIncludeVat();
        $from_date = TimeManipulator::getStartOfDay($report_from_date);
        $to_date = TimeManipulator::getEndOfDay($report_to_date);
        $rules = [];
        $data = [];
        $data['site'] = $site;
        $data['site_owner'] = $site_owner;
        $data['total'][Tenant::TYPE_TENANT] = [
            'shefel' => ['reading_diff' => 0, 'total_pay' => 0],
            'geva' => ['reading_diff' => 0, 'total_pay' => 0],
            'pisga' => ['reading_diff' => 0, 'total_pay' => 0],
            'total_consumption' => 0,
            'total_pay' => 0,
            'fixed_payment' => 0,
            'vat' => 0,
        ];
        $data['total'][Tenant::TYPE_TRANSPONDER] = [
            'shefel' => ['reading_diff' => 0, 'total_pay' => 0],
            'geva' => ['reading_diff' => 0, 'total_pay' => 0],
            'pisga' => ['reading_diff' => 0, 'total_pay' => 0],
            'total_consumption' => 0,
            'total_pay' => 0,
            'fixed_payment' => 0,
            'vat' => 0,
        ];
        $total = [
            'shefel_consumption' => 0,
            'geva_consumption' => 0,
            'pisga_consumption' => 0,
            'total_consumption' => 0,
            'total_pay' => 0,
            'power_factor_pay' => 0,
        ];
        $data['vat_included'] = $vat_included;
        $data['vat_percentage'] = Yii::$app->formatter->getVat($from_date, $to_date);
        $data_usage_methods = (ArrayHelper::getValue($params, 'is_import_export_separatly', false)) ? [
            Meter::DATA_USAGE_METHOD_IMPORT,
            Meter::DATA_USAGE_METHOD_EXPORT,
        ] : [
            Meter::DATA_USAGE_METHOD_DEFAULT,
        ];
        $index = 0;
        $total_consumption = 0;
        foreach($tenants as $index => $tenant) {
            foreach($data_usage_methods as $data_usage_method) {
                static::$data_usage_method = $data_usage_method;
                $rules[$tenant->type][$index] = [
                    'model_tenant' => $tenant,
                    'fixed_payment' => $tenant->getFixedPayment(),
                    'rules' => [],
                ];
                $data['total'][$tenant->type]['fixed_payment'] += $tenant->getFixedPayment();
                switch($data_usage_method) {
                    case Meter::DATA_USAGE_METHOD_IMPORT:
                    case Meter::DATA_USAGE_METHOD_IMPORT_PLUS_EXPORT:
                    case Meter::DATA_USAGE_METHOD_IMPORT_MINUS_EXPORT:
                    case Meter::DATA_USAGE_METHOD_EXPORT:
                        $rules[$tenant->type][$index]['identifier'] = implode(' ', [$tenant->name, '(' .
                                                                                                   ArrayHelper::getValue(Meter::getListDataUsageMethods(),
                                                                                                                         $data_usage_method) .
                                                                                                   ')']);
                        break;
                    default:
                        $rules[$tenant->type][$index]['identifier'] = $tenant->name;
                        break;
                }
                /**
                 * Generate single channel rules data
                 */
                $single = self::generateSingleChannelRules($tenant, $from_date, $to_date);
                if(!empty($single['rules'])) {
                    foreach($single['rules'] as $rule_index => $rule) {
                        self::checkForNegative($rule, $tenant, $site, $from_date, $to_date);
                        $rules[$tenant->type][$index]['rules'][$rule_index] = $rule;
                        $rules[$tenant->type][$index]['rules'][$rule_index]['power_factor_value'] =
                            $single['power_factor_value'];
                        $rules[$tenant->type][$index]['rules'][$rule_index]['power_factor_percent'] =
                            $single['power_factor_percent'];
                        $total_consumption += $rule['total_consumption'];
                    }
//                    if($total_consumption < 0) {
//                        self::addTotalConsumptionIsNegativeError($tenant, $site, $from_date, $to_date);
//                    }
                    $data['total'][$tenant->type]['shefel']['reading_diff'] += $single['shefel']['reading_diff'];
                    $data['total'][$tenant->type]['geva']['reading_diff'] += $single['geva']['reading_diff'];
                    $data['total'][$tenant->type]['pisga']['reading_diff'] += $single['pisga']['reading_diff'];
                    $data['total'][$tenant->type]['shefel']['total_pay'] += $single['shefel']['total_pay'];
                    $data['total'][$tenant->type]['geva']['total_pay'] += $single['geva']['total_pay'];
                    $data['total'][$tenant->type]['pisga']['total_pay'] += $single['pisga']['total_pay'];
                    $data['total'][$tenant->type]['total_consumption'] += $single['total_consumption'];
                    $data['total'][$tenant->type]['total_pay'] += $single['total_pay'];
                    $total['total_consumption'] += $single['total_consumption'];
                }
                /**
                 * Generate group load rules data
                 */
                $group = self::generateGroupLoadRules($tenant, $from_date, $to_date);
                if(!empty($group['rules'])) {
                    $rules[$tenant->type][$index]['rules'] =
                        ArrayHelper::merge($rules[$tenant->type][$index]['rules'], $group['rules']);
                    $data['total'][$tenant->type]['shefel']['reading_diff'] += $group['shefel']['reading_diff'];
                    $data['total'][$tenant->type]['geva']['reading_diff'] += $group['geva']['reading_diff'];
                    $data['total'][$tenant->type]['pisga']['reading_diff'] += $group['pisga']['reading_diff'];
                    $data['total'][$tenant->type]['shefel']['total_pay'] += $group['shefel']['total_pay'];
                    $data['total'][$tenant->type]['geva']['total_pay'] += $group['geva']['total_pay'];
                    $data['total'][$tenant->type]['pisga']['total_pay'] += $group['pisga']['total_pay'];
                    $data['total'][$tenant->type]['total_consumption'] += $group['total_consumption'];
                    $data['total'][$tenant->type]['total_pay'] += $group['total_pay'];
                    $total['total_consumption'] += $group['total_consumption'];
                }
                /**
                 * Generate fixed load rules data
                 */
                $fixed = self::generateFixedLoadRules($tenant, $from_date, $to_date);
                if(!empty($fixed['rules'])) {
                    $rules[$tenant->type][$index]['rules'] =
                        ArrayHelper::merge($rules[$tenant->type][$index]['rules'], $fixed['rules']);
                    $data['total'][$tenant->type]['shefel']['reading_diff'] += $fixed['shefel']['reading_diff'];
                    $data['total'][$tenant->type]['geva']['reading_diff'] += $fixed['geva']['reading_diff'];
                    $data['total'][$tenant->type]['pisga']['reading_diff'] += $fixed['pisga']['reading_diff'];
                    $data['total'][$tenant->type]['shefel']['total_pay'] += $fixed['shefel']['total_pay'];
                    $data['total'][$tenant->type]['geva']['total_pay'] += $fixed['geva']['total_pay'];
                    $data['total'][$tenant->type]['pisga']['total_pay'] += $fixed['pisga']['total_pay'];
                    $data['total'][$tenant->type]['total_consumption'] += $fixed['total_consumption'];
                    $data['total'][$tenant->type]['total_pay'] += $fixed['total_pay'];
                    $total['total_consumption'] += $fixed['total_consumption'];
                }
                $data['total'][$tenant->type]['total_pay'] += $tenant->getFixedPayment();
                $index++;
            }
        }
        if($total['shefel_consumption'] < 0 || $total['geva_consumption'] < 0 || $total['pisga_consumption'] < 0 ||
           $total['total_consumption'] <= 0
        ) {
            self::addTotalConsumptionForTheSiteIsNegativeError($site, $from_date, $to_date);
        }
        $data['data'] = $rules;
        return $data;
    }
}