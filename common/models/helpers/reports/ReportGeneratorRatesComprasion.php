<?php

namespace common\models\helpers\reports;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

use common\widgets\Alert;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\RateType;

class ReportGeneratorRatesComprasion extends ReportGenerator implements IReportGenerator
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
    public static function generate($report_from_date, $report_to_date, $site, $tenants = [], array $params = []) {
		$site_owner = $site->relationUser;
		$vat_included = $site->getIncludeVat();
		$from_date = TimeManipulator::getStartOfDay($report_from_date);
		$to_date = TimeManipulator::getEndOfDay($report_to_date);

		$rules = [];
		$data = [];
		$data['site'] = $site;
		$data['site_owner'] = $site_owner;
		$data['total_pay_low'] = 0;
		$data['total_pay_high'] = 0;
		$data['vat_pay_low'] = 0;
		$data['vat_pay_high'] = 0;
		$data['vat_percentage'] = Yii::$app->formatter->getVat($from_date, $to_date);
		
		$total = [
			'total_consumption' => 0,
			'total_pay' => 0,
		];

		foreach ($tenants as $index => $tenant) {
			/**
			 * Generate single channel rules data (Low rate)
			 */
			$single_low = self::generateSingleChannelRules($tenant, $from_date, $to_date);

			if (!empty($single_low['rules'])) {
				/**
				 * Generate single channel rules data (High rate)
				 */
				$single_high = ($high_rate_type_id = RateType::getHighRateTypeId()) ? self::generateSingleChannelRules($tenant, $from_date, $to_date, [], $high_rate_type_id) : [];
				$rules[$tenant->id] = [
					'tenant_id' => $tenant->id,
					'tenant_name' => $tenant->name,
					'model_tenant' => $tenant,
					'rule_name' => [],
					'total_pay_low' => 0,
					'total_pay_high' => 0,
				];

				foreach ($single_low['rules'] as $rule_index => $single_low_rule) {
					$rules[$tenant->id]['rule_name'][] = $single_low_rule['rule']['meter_channel_name']. ' - ' .$single_low_rule['rule']['meter_name'];
					$rules[$tenant->id]['total_pay_low'] += $single_low_rule['total_pay'];
					$rules[$tenant->id]['total_pay_high'] += (!empty($single_high['rules'])) ? $single_high['rules'][$rule_index]['total_pay'] : 0;

					if ($single_low_rule['total_consumption'] < 0) {
                        self::addTotalConsumptionIsNegativeError($tenant,$site,$from_date,$to_date);
					}

					$data['total_pay_low'] += $single_low_rule['total_pay'];
					$data['total_pay_high'] += (!empty($single_high['rules'])) ? $single_high['rules'][$rule_index]['total_pay'] : 0;
					$total['total_consumption'] += $single_low_rule['total_consumption'];
				}
			}
		}

		$data['vat_pay_low'] = ($data['total_pay_low'] / 100) * $data['vat_percentage'];
		$data['vat_pay_high'] = ($data['total_pay_high'] / 100) * $data['vat_percentage'];	

		// $order_by = ArrayHelper::getValue($params, 'order_by', static::ORDER_BY_METER);
		
		// switch ($order_by) {
		// 	case static::ORDER_BY_TENANT:
		// 		usort($rules, function($a, $b) {
		// 			return strcmp($a['tenant_name'], $b['tenant_name']) ?: $a['rule_name'] - $b['rule_name'];
		// 		});
		// 		break;
			
		// 	case static::ORDER_BY_METER:
		// 	default:
		// 		usort($rules, function($a, $b) {
		// 			return strcmp($a['rule_name'], $b['rule_name']) ?: $a['tenant_name'] - $b['tenant_name'];
		// 		});
		// 		break;
		// }

		$data['rules'] = $rules;
		$data['total_pay_diff'] = $data['total_pay_low'] - $data['total_pay_high'];

        if($total['shefel_consumption'] < 0 || $total['geva_consumption'] < 0 || $total['pisga_consumption'] < 0 ||
           $total['total_consumption'] <= 0
        ) {
            self::addTotalConsumptionForTheSiteIsNegativeError($site, $from_date, $to_date);
        }

		return $data;
	}
}