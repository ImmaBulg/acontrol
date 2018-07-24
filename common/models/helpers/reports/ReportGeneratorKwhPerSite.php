<?php

namespace common\models\helpers\reports;

use \DateTime;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use common\widgets\Alert;
use common\models\Tenant;
use common\models\Meter;
use common\models\Site;
use common\models\SiteBillingSetting;

class ReportGeneratorKwhPerSite extends ReportGenerator implements IReportGenerator
{
    /**
     * Generate report
     *
     * @param string|integer $report_from_date
     * @param string|integer $report_to_date
     * @param Site $site
     * @param array|Tenant $tenants
     * @param array $params
     * @return array
     */
    public static function generate($report_from_date, $report_to_date, $site, $tenants = [], array $params = []) {
        $site_owner = $site->relationUser;
        $from_date = TimeManipulator::getStartOfDay($report_from_date);
        $to_date = TimeManipulator::getEndOfDay($report_to_date);
        $rules = [];
        $data = [];
        $data['site'] = $site;
        $data['site_owner'] = $site_owner;
        $data_usage_methods = (ArrayHelper::getValue($params, 'is_import_export_separatly', false)) ? [
            Meter::DATA_USAGE_METHOD_IMPORT,
            Meter::DATA_USAGE_METHOD_EXPORT,
        ] : [
            Meter::DATA_USAGE_METHOD_DEFAULT,
        ];
        $data['total'][Tenant::TYPE_TENANT] = [
            'shefel' => ['reading_diff' => 0],
            'geva' => ['reading_diff' => 0],
            'pisga' => ['reading_diff' => 0],
            'total_consumption' => 0,
        ];
        $data['total'][Tenant::TYPE_TRANSPONDER] = [
            'shefel' => ['reading_diff' => 0],
            'geva' => ['reading_diff' => 0],
            'pisga' => ['reading_diff' => 0],
            'total_consumption' => 0,
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
        foreach($tenants as $tenant) {
            foreach($data_usage_methods as $data_usage_method) {
                static::$data_usage_method = $data_usage_method;
                $rules[$tenant->type][$index] = [
                    'model_tenant' => $tenant,
                    'fixed_payment' => $tenant->getFixedPayment(),
                    'rules' => [],
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
                $single = self::generateSingleChannelRules($tenant, $from_date, $to_date);
                if(!empty($single['rules'])) {
                    foreach($single['rules'] as $rule) {
                        self::checkForNegative($rule, $tenant, $site, $from_date, $to_date);
                        $rules[$tenant->type][$index]['rules'][] = $rule;
                        $total_consumption += $rule['total_consumption'];
                    }
//					if ($total_consumption <0) {
//							self::addTotalConsumptionIsNegativeError($tenant,$site,$from_date,$to_date);
//					}
                    $data['total'][$tenant->type]['shefel']['reading_diff'] += $single['shefel']['reading_diff'];
                    $data['total'][$tenant->type]['geva']['reading_diff'] += $single['geva']['reading_diff'];
                    $data['total'][$tenant->type]['pisga']['reading_diff'] += $single['pisga']['reading_diff'];
                    $data['total'][$tenant->type]['total_consumption'] += $single['total_consumption'];
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
                    $data['total'][$tenant->type]['total_consumption'] += $group['total_consumption'];
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
                    $data['total'][$tenant->type]['total_consumption'] += $fixed['total_consumption'];
                    $total['total_consumption'] += $fixed['total_consumption'];
                }
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