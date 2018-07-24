<?php

namespace common\models\helpers\reports;

use common\helpers\TimeManipulator;
use common\widgets\Alert;
use DateTime;
use Yii;

class ReportGeneratorYearly extends ReportGenerator implements IReportGenerator
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
        $data['vat_included'] = $vat_included;
        $data['vat_percentage'] = Yii::$app->formatter->getVat($from_date, $to_date);
        $data['data'] =
            (count($tenants) > 1) ? self::generateSiteData($report_from_date, $report_to_date, $tenants, $site) :
                self::generateTenantData($report_from_date, $report_to_date, $tenants, $site);
        return $data;
    }


    protected static function generateSiteData($report_from_date, $report_to_date, $tenants, $site) {
        $data = [
            'shefel_consumption' => 0,
            'geva_consumption' => 0,
            'pisga_consumption' => 0,
            'shefel_total_pay' => 0,
            'geva_total_pay' => 0,
            'pisga_total_pay' => 0,
            'total_consumption' => 0,
            'total_loads_consumption' => 0,
            'total_loads_pay' => 0,
            'total_extras_consumption' => 0,
            'total_extras_pay' => 0,
            'total_pay' => 0,
            'fixed_payment' => 0,
            'data' => [],
        ];
        $total_consumption = 0;
        $from_date = TimeManipulator::getStartOfDay($report_from_date);
        $to_date = TimeManipulator::getEndOfDay($report_to_date);
        $date_diff = date_diff((new DateTime())->setTimestamp($from_date), (new DateTime())->setTimestamp($to_date));
        $max_consumption = 0;
        while($from_date <= $to_date) {
            $start = (new DateTime())->setTimestamp($from_date)->modify('first day of this month')->getTimestamp();
            $end = (new DateTime())->setTimestamp($from_date)->modify('last day of this month')->getTimestamp();
            if($start < $from_date) $start = $from_date;
            if($end > $to_date) $end = $to_date;
            $data['data'][$start] = [
                'shefel' => 0,
                'geva' => 0,
                'pisga' => 0,
                'shefel_pay' => 0,
                'geva_pay' => 0,
                'pisga_pay' => 0,
                'shefel_consumption' => 0,
                'geva_consumption' => 0,
                'pisga_consumption' => 0,
                'shefel_total_pay' => 0,
                'geva_total_pay' => 0,
                'pisga_total_pay' => 0,
                'total_consumption' => 0,
                'total_loads_consumption' => 0,
                'total_loads_pay' => 0,
                'total_extras_consumption' => 0,
                'total_extras_pay' => 0,
                'total_pay' => 0,
                'fixed_payment' => 0,
            ];
            foreach($tenants as $tenant) {
                $data['data'][$start]['fixed_payment'] += $tenant->getFixedPayment();
                /**
                 * Generate single channel rules data
                 */
                $single = self::generateSingleChannelRules($tenant, $start, $end);
                if(!empty($single['rules'])) {
                    $data['data'][$start]['shefel_consumption'] += $single['shefel']['reading_diff'];
                    $data['data'][$start]['geva_consumption'] += $single['geva']['reading_diff'];
                    $data['data'][$start]['pisga_consumption'] += $single['pisga']['reading_diff'];
                    $data['data'][$start]['shefel_total_pay'] += $single['shefel']['total_pay'];
                    $data['data'][$start]['geva_total_pay'] += $single['geva']['total_pay'];
                    $data['data'][$start]['pisga_total_pay'] += $single['pisga']['total_pay'];
                    $data['data'][$start]['shefel'] += $single['shefel']['reading_diff'];
                    $data['data'][$start]['geva'] += $single['geva']['reading_diff'];
                    $data['data'][$start]['pisga'] += $single['pisga']['reading_diff'];
                    $data['data'][$start]['shefel_pay'] += $single['shefel']['total_pay'];
                    $data['data'][$start]['geva_pay'] += $single['geva']['total_pay'];
                    $data['data'][$start]['pisga_pay'] += $single['pisga']['total_pay'];
                    $data['data'][$start]['total_consumption'] += $single['total_consumption'];
                    if(isset($data['data'][$start]['max_consumption'])) {
                        $data['data'][$start]['max_consumption'] =
                            max($data['data'][$start]['max_consumption'], $single['max_consumption']);
                    }
                    else {
                        $data['data'][$start]['max_consumption'] = $single['max_consumption'];
                    }
                    $data['data'][$start]['total_pay'] += $single['total_pay'];
                    $total_consumption += $single['total_consumption'];
                }
                /**
                 * Generate group load rules data
                 */
                $group = self::generateGroupLoadRules($tenant, $start, $end);
                if(!empty($group['rules'])) {
                    $data['data'][$start]['shefel'] += $group['shefel']['reading_diff'];
                    $data['data'][$start]['geva'] += $group['geva']['reading_diff'];
                    $data['data'][$start]['pisga'] += $group['pisga']['reading_diff'];
                    $data['data'][$start]['shefel_pay'] += $group['shefel']['total_pay'];
                    $data['data'][$start]['geva_pay'] += $group['geva']['total_pay'];
                    $data['data'][$start]['pisga_pay'] += $group['pisga']['total_pay'];
                    $data['data'][$start]['total_consumption'] += $group['total_consumption'];
                    $data['data'][$start]['total_loads_consumption'] += $group['total_consumption'];
                    $data['data'][$start]['total_loads_pay'] += $group['total_pay'];
                    $data['data'][$start]['total_pay'] += $group['total_pay'];
                    if(isset($data['data'][$start]['max_consumption'])) {
                        $data['data'][$start]['max_consumption'] =
                            max($data['data'][$start]['max_consumption'], $group['max_consumption']);
                    }
                    else {
                        $data['data'][$start]['max_consumption'] = $group['max_consumption'];
                    }
                    $total_consumption += $group['total_consumption'];
                }
                /**
                 * Generate fixed load rules data
                 */
                $fixed = self::generateFixedLoadRules($tenant, $start, $end);
                if(!empty($fixed['rules'])) {
                    $data['data'][$start]['shefel'] += $fixed['shefel']['reading_diff'];
                    $data['data'][$start]['geva'] += $fixed['geva']['reading_diff'];
                    $data['data'][$start]['pisga'] += $fixed['pisga']['reading_diff'];
                    $data['data'][$start]['shefel_pay'] += $fixed['shefel']['total_pay'];
                    $data['data'][$start]['geva_pay'] += $fixed['geva']['total_pay'];
                    $data['data'][$start]['pisga_pay'] += $fixed['pisga']['total_pay'];
                    $data['data'][$start]['total_consumption'] += $fixed['total_consumption'];
                    $data['data'][$start]['total_extras_consumption'] += $fixed['total_consumption'];
                    $data['data'][$start]['total_extras_pay'] += $fixed['total_pay'];
                    $data['data'][$start]['total_pay'] += $fixed['total_pay'];
                    if(isset($data['data'][$start]['max_consumption'])) {
                        $data['data'][$start]['max_consumption'] =
                            max($data['data'][$start]['max_consumption'], $fixed['max_consumption']);
                    }
                    else {
                        $data['data'][$start]['max_consumption'] = $fixed['max_consumption'];
                    }
                    $total_consumption += $fixed['total_consumption'];
                }
                $max_consumption = max($max_consumption, $data['data'][$start]['max_consumption']);
            }
            $data['shefel_consumption'] += $data['data'][$start]['shefel_consumption'];
            $data['geva_consumption'] += $data['data'][$start]['geva_consumption'];
            $data['pisga_consumption'] += $data['data'][$start]['pisga_consumption'];
            $data['shefel_total_pay'] += $data['data'][$start]['shefel_total_pay'];
            $data['geva_total_pay'] += $data['data'][$start]['geva_total_pay'];
            $data['pisga_total_pay'] += $data['data'][$start]['pisga_total_pay'];
            $data['total_pay'] += $data['data'][$start]['total_pay'];
            $data['total_consumption'] += $data['data'][$start]['total_consumption'];
            $data['total_loads_consumption'] += $data['data'][$start]['total_loads_consumption'];
            $data['total_loads_pay'] += $data['data'][$start]['total_loads_pay'];
            $data['total_extras_consumption'] += $data['data'][$start]['total_extras_consumption'];
            $data['total_extras_pay'] += $data['data'][$start]['total_extras_pay'];
            $data['fixed_payment'] += $data['data'][$start]['fixed_payment'];
            $from_date = (new DateTime())->setTimestamp($from_date)->modify('first day of next month')->getTimestamp();
        }
        $data['max_consumption'] = $max_consumption;
        if($total_consumption <= 0) {
            $from_date = TimeManipulator::getStartOfDay($report_from_date);
            $to_date = TimeManipulator::getEndOfDay($report_to_date);
            self::addError(Yii::t('common.report',
                                  'The total consumption is negative or equal to {n} for the site {site} during the report period ({dates}).',
                                  [
                                      'n' => 0,
                                      'site' => $site->name,
                                      'dates' => implode(' - ', [Yii::$app->formatter->asDate($from_date),
                                                                 Yii::$app->formatter->asDate($to_date)]),
                                  ]), Alert::ALERT_WARNING);
        }
        return $data;
    }


    protected static function generateTenantData($report_from_date, $report_to_date, $tenants, $site) {
        $result = [];
        $total_consumption = 0;
        $max_consumption = 0;
        foreach($tenants as $index => $tenant) {
            $data = [
                'shefel_consumption' => 0,
                'geva_consumption' => 0,
                'pisga_consumption' => 0,
                'shefel_total_pay' => 0,
                'geva_total_pay' => 0,
                'pisga_total_pay' => 0,
                'total_consumption' => 0,
                'total_loads_consumption' => 0,
                'total_loads_pay' => 0,
                'total_extras_consumption' => 0,
                'total_extras_pay' => 0,
                'total_pay' => 0,
                'fixed_payment' => 0,
                'data' => [],
                'model_tenant' => $tenant,
            ];
            $from_date = TimeManipulator::getStartOfDay($report_from_date);
            $to_date = TimeManipulator::getEndOfDay($report_to_date);
            $date_diff =
                date_diff((new DateTime())->setTimestamp($from_date), (new DateTime())->setTimestamp($to_date));
            while($from_date <= $to_date) {
                $start = (new DateTime())->setTimestamp($from_date)->modify('first day of this month')->getTimestamp();
                $end = (new DateTime())->setTimestamp($from_date)->modify('last day of this month')->getTimestamp();
                if($end > $to_date) $end = $to_date;
                $data['data'][$start] = [
                    'shefel' => 0,
                    'geva' => 0,
                    'pisga' => 0,
                    'shefel_pay' => 0,
                    'geva_pay' => 0,
                    'pisga_pay' => 0,
                    'shefel_consumption' => 0,
                    'geva_consumption' => 0,
                    'pisga_consumption' => 0,
                    'shefel_total_pay' => 0,
                    'geva_total_pay' => 0,
                    'pisga_total_pay' => 0,
                    'total_consumption' => 0,
                    'total_loads_consumption' => 0,
                    'total_loads_pay' => 0,
                    'total_extras_consumption' => 0,
                    'total_extras_pay' => 0,
                    'total_pay' => 0,
                    'fixed_payment' => $tenant->getFixedPayment(),
                ];
                /**
                 * Generate single channel rules data
                 */
                $single = self::generateSingleChannelRules($tenant, $start, $end);
                if(!empty($single['rules'])) {
                    $data['data'][$start]['shefel_consumption'] += $single['shefel']['reading_diff'];
                    $data['data'][$start]['geva_consumption'] += $single['geva']['reading_diff'];
                    $data['data'][$start]['pisga_consumption'] += $single['pisga']['reading_diff'];
                    $data['data'][$start]['shefel_total_pay'] += $single['shefel']['total_pay'];
                    $data['data'][$start]['geva_total_pay'] += $single['geva']['total_pay'];
                    $data['data'][$start]['pisga_total_pay'] += $single['pisga']['total_pay'];
                    $data['data'][$start]['shefel'] += $single['shefel']['reading_diff'];
                    $data['data'][$start]['geva'] += $single['geva']['reading_diff'];
                    $data['data'][$start]['pisga'] += $single['pisga']['reading_diff'];
                    $data['data'][$start]['shefel_pay'] += $single['shefel']['total_pay'];
                    $data['data'][$start]['geva_pay'] += $single['geva']['total_pay'];
                    $data['data'][$start]['pisga_pay'] += $single['pisga']['total_pay'];
                    $data['data'][$start]['total_consumption'] += $single['total_consumption'];
                    $data['data'][$start]['total_pay'] += $single['total_pay'];
                    if(isset($data['data'][$start]['max_consumption'])) {
                        $data['data'][$start]['max_consumption'] =
                            max($data['data'][$start]['max_consumption'], $single['max_consumption']);
                    }
                    else {
                        $data['data'][$start]['max_consumption'] = $single['max_consumption'];
                    }
                    $total_consumption += $single['total_consumption'];
                }
                /**
                 * Generate group load rules data
                 */
                $group = self::generateGroupLoadRules($tenant, $start, $end);
                if(!empty($group['rules'])) {
                    $data['data'][$start]['shefel'] += $group['shefel']['reading_diff'];
                    $data['data'][$start]['geva'] += $group['geva']['reading_diff'];
                    $data['data'][$start]['pisga'] += $group['pisga']['reading_diff'];
                    $data['data'][$start]['shefel_pay'] += $group['shefel']['total_pay'];
                    $data['data'][$start]['geva_pay'] += $group['geva']['total_pay'];
                    $data['data'][$start]['pisga_pay'] += $group['pisga']['total_pay'];
                    $data['data'][$start]['total_consumption'] += $group['total_consumption'];
                    $data['data'][$start]['total_loads_consumption'] += $group['total_consumption'];
                    $data['data'][$start]['total_loads_pay'] += $group['total_pay'];
                    $data['data'][$start]['total_pay'] += $group['total_pay'];
                    if(isset($data['data'][$start]['max_consumption'])) {
                        $data['data'][$start]['max_consumption'] =
                            max($data['data'][$start]['max_consumption'], $group['max_consumption']);
                    }
                    else {
                        $data['data'][$start]['max_consumption'] = $group['max_consumption'];
                    }
                    $total_consumption += $group['total_consumption'];
                }
                /**
                 * Generate fixed load rules data
                 */
                $fixed = self::generateFixedLoadRules($tenant, $start, $end);
                if(!empty($fixed['rules'])) {
                    $data['data'][$start]['shefel'] += $fixed['shefel']['reading_diff'];
                    $data['data'][$start]['geva'] += $fixed['geva']['reading_diff'];
                    $data['data'][$start]['pisga'] += $fixed['pisga']['reading_diff'];
                    $data['data'][$start]['shefel_pay'] += $fixed['shefel']['total_pay'];
                    $data['data'][$start]['geva_pay'] += $fixed['geva']['total_pay'];
                    $data['data'][$start]['pisga_pay'] += $fixed['pisga']['total_pay'];
                    $data['data'][$start]['total_consumption'] += $fixed['total_consumption'];
                    $data['data'][$start]['total_extras_consumption'] += $fixed['total_consumption'];
                    $data['data'][$start]['total_extras_pay'] += $fixed['total_pay'];
                    $data['data'][$start]['total_pay'] += $fixed['total_pay'];
                    if(isset($data['data'][$start]['max_consumption'])) {
                        $data['data'][$start]['max_consumption'] =
                            max($data['data'][$start]['max_consumption'], $fixed['max_consumption']);
                    }
                    else {
                        $data['data'][$start]['max_consumption'] = $fixed['max_consumption'];
                    }
                    $total_consumption += $fixed['total_consumption'];
                }
                $max_consumption = max($max_consumption, $data['data'][$start]['max_consumption']);
                $data['shefel_consumption'] += $data['data'][$start]['shefel_consumption'];
                $data['geva_consumption'] += $data['data'][$start]['geva_consumption'];
                $data['pisga_consumption'] += $data['data'][$start]['pisga_consumption'];
                $data['shefel_total_pay'] += $data['data'][$start]['shefel_total_pay'];
                $data['geva_total_pay'] += $data['data'][$start]['geva_total_pay'];
                $data['pisga_total_pay'] += $data['data'][$start]['pisga_total_pay'];
                $data['total_pay'] += $data['data'][$start]['total_pay'];
                $data['total_consumption'] += $data['data'][$start]['total_consumption'];
                $data['total_loads_consumption'] += $data['data'][$start]['total_loads_consumption'];
                $data['total_loads_pay'] += $data['data'][$start]['total_loads_pay'];
                $data['total_extras_consumption'] += $data['data'][$start]['total_extras_consumption'];
                $data['total_extras_pay'] += $data['data'][$start]['total_extras_pay'];
                $data['fixed_payment'] += $data['data'][$start]['fixed_payment'];
                $from_date =
                    (new DateTime())->setTimestamp($from_date)->modify('first day of next month')->getTimestamp();
            }
            $data['max_consumption'] = $max_consumption;
            $result[$index] = $data;
        }
        if($total_consumption <= 0) {
            $from_date = TimeManipulator::getStartOfDay($report_from_date);
            $to_date = TimeManipulator::getEndOfDay($report_to_date);
            self::addError(Yii::t('common.report',
                                  'The total consumption is negative or equal to {n} for the site {site} during the report period ({dates}).',
                                  [
                                      'n' => 0,
                                      'site' => $site->name,
                                      'dates' => implode(' - ', [Yii::$app->formatter->asDate($from_date),
                                                                 Yii::$app->formatter->asDate($to_date)]),
                                  ]), Alert::ALERT_WARNING);
        }
        return $result;
    }
}