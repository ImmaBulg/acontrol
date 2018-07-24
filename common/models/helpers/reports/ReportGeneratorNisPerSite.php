<?php

namespace common\models\helpers\reports;

use common\models\Meter;
use common\models\Site;
use Yii;
use yii\helpers\ArrayHelper;

class ReportGeneratorNisPerSite extends ReportGenerator implements IReportGenerator
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
     * @internal param array $params
     */
    public static function generate($report_from_date, $report_to_date, Site $site, $tenants = [], array $params = []) {
        $site_owner = $site->relationUser;
        $vat_included = $site->getIncludeVat();
        $from_date = static::getStartOfDay($report_from_date);
        $to_date = static::getEndOfDay($report_to_date);
        $rules = [];
        $data = [];
        $data['site'] = $site;
        $data['site_owner'] = $site_owner;
        $data['vat_included'] = $vat_included;
        $data['vat_percentage'] = Yii::$app->formatter->getVat($from_date, $to_date);
        $data_usage_methods = (ArrayHelper::getValue($params, 'is_import_export_separatly', false)) ? [
            Meter::DATA_USAGE_METHOD_IMPORT,
            Meter::DATA_USAGE_METHOD_EXPORT,
        ] : [
            Meter::DATA_USAGE_METHOD_DEFAULT,
        ];
        $total = [
            'shefel_consumption' => 0,
            'geva_consumption' => 0,
            'pisga_consumption' => 0,
            'total_consumption' => 0,
            'total_pay' => 0,
        ];
        $index = 0;
        $total_consumption = 0;
        foreach($tenants as  $tenant) {
            foreach($data_usage_methods as $data_usage_method) {
                static::$data_usage_method = $data_usage_method;
                $rules[$tenant->type][$index] = [
                    'model_tenant' => $tenant,
                    'fixed_payment' => $tenant->getFixedPayment(),
                    static::RULE_SINGLE_CHANNEL => 0,
                    static::RULE_GROUP_LOAD => 0,
                    static::RULE_FIXED_LOAD => 0,
                    'total_pay' => 0
                ];
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
                $single = static::generateSingleChannelRules($tenant, $from_date, $to_date);
                if(!empty($single['rules'])) {
                    foreach($single['rules'] as $rule) {
                        self::checkForNegative($rule, $tenant, $site, $from_date, $to_date);
                        $rules[$tenant->type][$index]['rules'][] =
                            $rule['rule']['meter_name'] . ' - ' . $rule['rule']['meter_channel_name'];
                        $total_consumption += $rule['total_consumption'];
                    }

                    $rules[$tenant->type][$index][static::RULE_SINGLE_CHANNEL] += $single['total_pay'];
                    $total['total_consumption'] += $single['total_consumption'];
                    $rules[$tenant->type][$index]['power_factor_value'] = $single['power_factor_value'];
                    $rules[$tenant->type][$index]['power_factor_percent'] = $single['power_factor_percent'];
                }
                /**
                 * Generate group load rules data
                 */
                $group = static::generateGroupLoadRules($tenant, $from_date, $to_date);
                if(!empty($group['rules'])) {
                    $rules[$tenant->type][$index][static::RULE_GROUP_LOAD] += $group['total_pay'];
                    $total['total_consumption'] += $group['total_consumption'];
                }
                /**
                 * Generate fixed load rules data
                 */
                $fixed = static::generateFixedLoadRules($tenant, $from_date, $to_date);
                if(!empty($fixed['rules'])) {
                    $rules[$tenant->type][$index][static::RULE_FIXED_LOAD] += $fixed['total_pay'];
                    $total['total_consumption'] += $fixed['total_consumption'];
                }

                $index++;
            }
        }
        $data['data'] = $rules;
        if($total['shefel_consumption'] < 0 || $total['geva_consumption'] < 0 || $total['pisga_consumption'] < 0 ||
           $total['total_consumption'] <= 0
        ) {
            self::addTotalConsumptionForTheSiteIsNegativeError($site, $from_date, $to_date);
        }
        return $data;
    }
}