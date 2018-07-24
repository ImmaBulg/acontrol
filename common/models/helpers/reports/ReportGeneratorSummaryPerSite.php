<?php

namespace common\models\helpers\reports;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

use common\widgets\Alert;
use common\models\Report;
use common\models\Tenant;

class ReportGeneratorSummaryPerSite extends ReportGenerator implements IReportGenerator
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
		
		$data = [];
		$params['site'] = $site;
		$params['site_owner'] = $site_owner;

		$total = [
			'shefel_consumption' => 0,
			'geva_consumption' => 0,
			'pisga_consumption' => 0,
			'total_consumption' => 0,
			'total_pay' => 0,
		];

		foreach ($tenants as $index => $tenant) {
			$vat_included = $site->getIncludeVat();

			// $data[$index]['tenant'] = $tenant;
			// $data[$index]['fixed_payment'] = $tenant->getFixedPayment();
			// $data[$index]['total_pay'] = 0;
			// $data[$index]['total_consumption'] = 0;
			// $data[$index]['vat_percentage'] = Yii::$app->formatter->getVat($from_date, $to_date);
			// $data[$index]['vat_included'] = $vat_included;

			/**
			 * Generate single channel rules data
			 */
			$single = self::generateSingleChannelRules($tenant, $from_date, $to_date);

			if (!empty($single['rules'])) {
				foreach ($single['rules'] as $rule) {
					$rates = $rule['rates'];

					foreach ($rates as $rate) {
						$data['rates'][$rate['id']][$tenant->id]['meter_name'] = $rule['rule']['name'];
						$data['rates'][$rate['id']][$tenant->id]['tenant_name'] = $tenant->name;
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['shefel']['reading_from'] = ArrayHelper::getValue($rate, 'shefel.reading_from', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['geva']['reading_from'] = ArrayHelper::getValue($rate, 'geva.reading_from', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['pisga']['reading_from'] = ArrayHelper::getValue($rate, 'pisga.reading_from', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['shefel']['reading_to'] = ArrayHelper::getValue($rate, 'shefel.reading_to', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['geva']['reading_to'] = ArrayHelper::getValue($rate, 'geva.reading_to', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['pisga']['reading_to'] = ArrayHelper::getValue($rate, 'pisga.reading_to', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['shefel']['reading_diff'] = ArrayHelper::getValue($rate, 'shefel.reading_diff', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['geva']['reading_diff'] = ArrayHelper::getValue($rate, 'geva.reading_diff', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['pisga']['reading_diff'] = ArrayHelper::getValue($rate, 'pisga.reading_diff', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['reading_diff'] = $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['shefel']['reading_diff'] + $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['geva']['reading_diff'] + $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['pisga']['reading_diff'];

						$data['rates'][$rate['id']][$tenant->id]['shefel']['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['shefel']['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['shefel']['total_diff'] + ArrayHelper::getValue($rate, 'shefel.reading_diff', 0)) : ArrayHelper::getValue($rate, 'shefel.reading_diff', 0);
						$data['rates'][$rate['id']][$tenant->id]['geva']['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['geva']['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['geva']['total_diff'] + ArrayHelper::getValue($rate, 'geva.reading_diff', 0)) : ArrayHelper::getValue($rate, 'geva.reading_diff', 0);
						$data['rates'][$rate['id']][$tenant->id]['pisga']['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['pisga']['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['pisga']['total_diff'] + ArrayHelper::getValue($rate, 'pisga.reading_diff', 0)) : ArrayHelper::getValue($rate, 'pisga.reading_diff', 0);

						$data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'] = (isset($data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'])) ? ($data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'] + ArrayHelper::getValue($rate, 'shefel.total_pay', 0)) : ArrayHelper::getValue($rate, 'shefel.total_pay', 0);
						$data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'] = (isset($data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'])) ? ($data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'] + ArrayHelper::getValue($rate, 'geva.total_pay', 0)) : ArrayHelper::getValue($rate, 'geva.total_pay', 0);
						$data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'] = (isset($data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'])) ? ($data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'] + ArrayHelper::getValue($rate, 'pisga.total_pay', 0)) : ArrayHelper::getValue($rate, 'pisga.total_pay', 0);
						
						$data['rates'][$rate['id']][$tenant->id]['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['total_diff'] + $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['reading_diff']) : $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_SINGLE_CHANNEL]['reading_diff'];
						$data['rates'][$rate['id']][$tenant->id]['total_pay'] = $data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'] + $data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'] + $data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'];
						
						$data['rates'][$rate['id']][$tenant->id]['tenant'] = $tenant;
						$data['rates'][$rate['id']][$tenant->id]['fixed_payment'] = $tenant->getFixedPayment();
					}

//					if ($rule['total_consumption'] < 0) {
//                        self::addTotalConsumptionIsNegativeError($tenant,$site,$from_date,$to_date);
//					}
				}

				$total['total_consumption'] += $single['total_consumption'];
			}

			/**
			 * Generate group load rules data
			 */
			$group = self::generateGroupLoadRules($tenant, $from_date, $to_date);

			if (!empty($group['rules'])) {
				foreach ($group['rules'] as $rule) {
					$rates = $rule['rates'];
					
					foreach ($rates as $rate) {
						$data['rates'][$rate['id']][$tenant->id]['meter_name'] = $rule['rule']['name'];
						$data['rates'][$rate['id']][$tenant->id]['tenant_name'] = $tenant->name;

						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_GROUP_LOAD]['shefel']['reading_diff'] = ArrayHelper::getValue($rate, 'shefel.reading_diff', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_GROUP_LOAD]['geva']['reading_diff'] = ArrayHelper::getValue($rate, 'geva.reading_diff', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_GROUP_LOAD]['pisga']['reading_diff'] = ArrayHelper::getValue($rate, 'pisga.reading_diff', 0);
						$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_GROUP_LOAD]['reading_diff'] = $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_GROUP_LOAD]['shefel']['reading_diff'] + $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_GROUP_LOAD]['geva']['reading_diff'] + $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_GROUP_LOAD]['pisga']['reading_diff'];

						$data['rates'][$rate['id']][$tenant->id]['shefel']['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['shefel']['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['shefel']['total_diff'] + ArrayHelper::getValue($rate, 'shefel.reading_diff', 0)) : ArrayHelper::getValue($rate, 'shefel.reading_diff', 0);
						$data['rates'][$rate['id']][$tenant->id]['geva']['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['geva']['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['geva']['total_diff'] + ArrayHelper::getValue($rate, 'geva.reading_diff', 0)) : ArrayHelper::getValue($rate, 'geva.reading_diff', 0);
						$data['rates'][$rate['id']][$tenant->id]['pisga']['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['pisga']['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['pisga']['total_diff'] + ArrayHelper::getValue($rate, 'pisga.reading_diff', 0)) : ArrayHelper::getValue($rate, 'pisga.reading_diff', 0);

						$data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'] = (isset($data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'])) ? ($data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'] + ArrayHelper::getValue($rate, 'shefel.total_pay', 0)) : ArrayHelper::getValue($rate, 'shefel.total_pay', 0);
						$data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'] = (isset($data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'])) ? ($data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'] + ArrayHelper::getValue($rate, 'geva.total_pay', 0)) : ArrayHelper::getValue($rate, 'geva.total_pay', 0);
						$data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'] = (isset($data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'])) ? ($data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'] + ArrayHelper::getValue($rate, 'pisga.total_pay', 0)) : ArrayHelper::getValue($rate, 'pisga.total_pay', 0);
					
						$data['rates'][$rate['id']][$tenant->id]['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['total_diff'] + $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_GROUP_LOAD]['reading_diff']) : $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_GROUP_LOAD]['reading_diff'];
						$data['rates'][$rate['id']][$tenant->id]['total_pay'] = $data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'] + $data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'] + $data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'];
					
						$data['rates'][$rate['id']][$tenant->id]['tenant'] = $tenant;
						$data['rates'][$rate['id']][$tenant->id]['fixed_payment'] = $tenant->getFixedPayment();
					}
				}

				$total['total_consumption'] += $group['total_consumption'];
			}

			/**
			 * Generate fixed load rules data
			 */
			$fixed = self::generateFixedLoadRules($tenant, $from_date, $to_date);

			if (!empty($fixed['rules'])) {
				foreach ($fixed['rules'] as $rule) {
					if (!empty($rule['rates'])) {
						$rates = $rule['rates'];
						
						foreach ($rates as $rate) {
							$data['rates'][$rate['id']][$tenant->id]['meter_name'] = Yii::t('common.view', 'Fixed addition');
							$data['rates'][$rate['id']][$tenant->id]['tenant_name'] = $tenant->name;

							$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['shefel']['reading_diff'] = ArrayHelper::getValue($rate, 'shefel.reading_diff', 0);
							$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['geva']['reading_diff'] = ArrayHelper::getValue($rate, 'geva.reading_diff', 0);
							$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['pisga']['reading_diff'] = ArrayHelper::getValue($rate, 'pisga.reading_diff', 0);
							$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['fixed']['reading_diff'] = ArrayHelper::getValue($rate, 'fixed.reading_diff', 0);
							$data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['reading_diff'] = $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['shefel']['reading_diff'] + $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['geva']['reading_diff'] + $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['pisga']['reading_diff'] + $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['fixed']['reading_diff'];

							$data['rates'][$rate['id']][$tenant->id]['shefel']['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['shefel']['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['shefel']['total_diff'] + ArrayHelper::getValue($rate, 'shefel.reading_diff', 0)) : ArrayHelper::getValue($rate, 'shefel.reading_diff', 0);
							$data['rates'][$rate['id']][$tenant->id]['geva']['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['geva']['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['geva']['total_diff'] + ArrayHelper::getValue($rate, 'geva.reading_diff', 0)) : ArrayHelper::getValue($rate, 'geva.reading_diff', 0);
							$data['rates'][$rate['id']][$tenant->id]['pisga']['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['pisga']['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['pisga']['total_diff'] + ArrayHelper::getValue($rate, 'pisga.reading_diff', 0)) : ArrayHelper::getValue($rate, 'pisga.reading_diff', 0);
							$data['rates'][$rate['id']][$tenant->id]['fixed']['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['fixed']['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['fixed']['total_diff'] + ArrayHelper::getValue($rate, 'fixed.reading_diff', 0)) : ArrayHelper::getValue($rate, 'fixed.reading_diff', 0);

							$data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'] = (isset($data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'])) ? ($data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'] + ArrayHelper::getValue($rate, 'shefel.total_pay', 0)) : ArrayHelper::getValue($rate, 'shefel.total_pay', 0);
							$data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'] = (isset($data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'])) ? ($data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'] + ArrayHelper::getValue($rate, 'geva.total_pay', 0)) : ArrayHelper::getValue($rate, 'geva.total_pay', 0);
							$data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'] = (isset($data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'])) ? ($data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'] + ArrayHelper::getValue($rate, 'pisga.total_pay', 0)) : ArrayHelper::getValue($rate, 'pisga.total_pay', 0);
							$data['rates'][$rate['id']][$tenant->id]['fixed']['total_pay'] = (isset($data['rates'][$rate['id']][$tenant->id]['fixed']['total_pay'])) ? ($data['rates'][$rate['id']][$tenant->id]['fixed']['total_pay'] + ArrayHelper::getValue($rate, 'fixed.total_pay', 0)) : ArrayHelper::getValue($rate, 'fixed.total_pay', 0);
						
							$data['rates'][$rate['id']][$tenant->id]['total_diff'] = (isset($data['rates'][$rate['id']][$tenant->id]['total_diff'])) ? ($data['rates'][$rate['id']][$tenant->id]['total_diff'] + $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['reading_diff']) : $data['rates'][$rate['id']][$tenant->id]['rules'][self::RULE_FIXED_LOAD]['reading_diff'];
							$data['rates'][$rate['id']][$tenant->id]['total_pay'] = $data['rates'][$rate['id']][$tenant->id]['shefel']['total_pay'] + $data['rates'][$rate['id']][$tenant->id]['geva']['total_pay'] + $data['rates'][$rate['id']][$tenant->id]['pisga']['total_pay'] + $data['rates'][$rate['id']][$tenant->id]['fixed']['total_pay'];
						
							$data['rates'][$rate['id']][$tenant->id]['tenant'] = $tenant;
						}
					} else {
						$data['rates'][$rate['id']][$tenant->id]['fixed_pay'] = (empty($data['rates'][$rate['id']][$tenant->id]['fixed_pay'])) ? $rule['total_pay'] : $data['rates'][$rate['id']][$tenant->id]['fixed_pay'] + $rule['total_pay'];
					}
				}

				$total['total_consumption'] += $fixed['total_consumption'];
			}
		}

        if($total['shefel_consumption'] < 0 || $total['geva_consumption'] < 0 || $total['pisga_consumption'] < 0 ||
           $total['total_consumption'] <= 0
        ) {
            self::addTotalConsumptionForTheSiteIsNegativeError($site, $from_date, $to_date);
        }

		$params['data'] = $data;
		return $params;
	}
}