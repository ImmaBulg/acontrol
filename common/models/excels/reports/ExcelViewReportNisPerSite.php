<?php

namespace common\models\excels\reports;

use common\helpers\CalculationHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use common\models\Site;
use common\models\Tenant;
use common\components\data\ExcelView;
use common\models\helpers\reports\ReportGeneratorNisPerSite;

/**
 * ExcelViewReportNisPerSite is the class for view report nis per site excel.
 */
class ExcelViewReportNisPerSite extends ExcelView
{
    /**
     * @inheritdoc
     */
    public function setObjPHPExcelAttribute() {
        $objPHPExcel = $this->getObjPHPExcel();
        $objPHPExcelActiveSheet = $objPHPExcel->getActiveSheet();
        $params = $this->getParams();
        $report = $params['report'];
        $site = $params['data']['site'];
        $site_owner = $params['data']['site_owner'];
        $additional_parameters = $params['additional_parameters'];
        $power_factor_visibility = (!empty($additional_parameters)) ?
            ArrayHelper::getValue($additional_parameters, 'power_factor_visibility', Site::POWER_FACTOR_DONT_SHOW) :
            Site::POWER_FACTOR_DONT_SHOW;
        if(!empty($params['data'])) {
            $r = 1;
            /* Begin Head Image */
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName(Yii::$app->name);
            $objDrawing->setDescription(Yii::$app->name);
            $objDrawing->setPath(Yii::getAlias('@backend/web/images/pdf/report/horizontal-header.png'));
            $objDrawing->setResizeProportional(false);
            $objDrawing->setWidth(350);
            $objDrawing->setCoordinates(self::columnName(1) . $r);
            $objDrawing->setWorksheet($objPHPExcelActiveSheet);
            $r += 6;
            /* End Head Image */
            $r++;
            $head = [
                [
                    'name' => Yii::t('common.view', 'Financial report'),
                    'value' => null,
                ],
                [
                    'name' => Yii::t('common.view', 'To') . ': ' . $site_owner,
                    'value' => null,
                ],
                [
                    'name' => Yii::t('common.view', 'Summary report for site') . ': ' . $site,
                    'value' => Yii::t('common.view', 'Issue date') . ': ' .
                               Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'),
                ],
                [
                    'name' => Yii::t('common.view', 'Report range') . ': ' .
                              Yii::$app->formatter->asDate($report->from_date, 'dd/MM/yy') . ' - ' .
                              Yii::$app->formatter->asDate($report->to_date, 'dd/MM/yy'),
                    'value' => null,
                ],
            ];
            $column_shift = 5;
            if(!empty($additional_parameters['column_total_pay_single_channel_rules'])) {
                $column_shift++;
            }
            if(!empty($additional_parameters['column_total_pay_group_load_rules'])) {
                $column_shift++;
            }
            if(!empty($additional_parameters['column_total_pay_fixed_load_rules'])) {
                $column_shift++;
            }
            if(!empty($additional_parameters['column_fixed_payment'])) {
                $column_shift++;
            }
            if(!empty($additional_parameters['is_vat_included'])) {
                $column_shift += 2;
            }
            foreach($head as $c => $value) {
                $objPHPExcelActiveSheet->setCellValue(self::columnName(1) . $r, $value['name']);
                $objPHPExcelActiveSheet->getStyle(self::columnName(1) . $r)->applyFromArray([
                                                                                                'font' => [
                                                                                                    'size' => 10,
                                                                                                ],
                                                                                            ]);
                $objPHPExcelActiveSheet->getColumnDimension(self::columnName(1))->setAutoSize(true);
                $objPHPExcelActiveSheet->setCellValue(self::columnName($column_shift) . $r, $value['value']);
                $objPHPExcelActiveSheet->getStyle(self::columnName($column_shift) . $r)->applyFromArray([
                                                                                                            'font' => [
                                                                                                                'size' => 10,
                                                                                                            ],
                                                                                                        ]);
                $objPHPExcelActiveSheet->getColumnDimension(self::columnName($column_shift))->setAutoSize(true);
                $r++;
            }
            $r++;
            $columns = [];
            $columns[] = Yii::t('common.view', 'Row number');
            $columns[] = Yii::t('common.view', 'Tenant ID');
            $columns[] = Yii::t('common.view', 'Tenant name');
            $columns[] = Yii::t('common.view', 'Meter ID');
            if(!empty($additional_parameters['column_total_pay_single_channel_rules'])) {
                $columns[] = Yii::t('common.view', 'Total to pay based on Single rules');
            }
            if(!empty($additional_parameters['column_total_pay_group_load_rules'])) {
                $columns[] = Yii::t('common.view', 'Total to pay based on Group load rules');
            }
            if(!empty($additional_parameters['column_total_pay_fixed_load_rules'])) {
                $columns[] = Yii::t('common.view', 'Total to pay based on Fixed load rules');
            }
            if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])) {
                $columns[] = Yii::t('common.view', 'Power factor');
                $columns[] = Yii::t('common.view', 'Power factor addition');
            }
            if(!empty($additional_parameters['column_fixed_payment'])) {
                $columns[] = Yii::t('common.view', 'Fixed payment');
            }
            $columns[] = Yii::t('common.view', 'Total');
            if(!empty($additional_parameters['is_vat_included'])) {
                $columns[] = Yii::t('common.view', 'VAT');
                $columns[] = Yii::t('common.view', 'Total (including VAT)');
            }
            foreach($columns as $c => $column) {
                $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $column);
                $objPHPExcelActiveSheet->getStyle(self::columnName($c + 1) . $r)->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => 'ffffff'],
                    ],
                    'fill' => [
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => ['rgb' => '7e7e7e'],
                    ],
                    'borders' => [
                        'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 1))->setAutoSize(true);
            }
            /* Rows */
            $r++;
            $index = 1;
            $tenants_total_to_pay = 0;
            foreach($params['data']['tenants'] as $tenant) {

                $tenant_total_to_pay = $tenant['total_single_rules'];
                $rows = [];
                $rows[] = $index;
                $index++;
                $rows[] = $tenant['tenant_id'];
                $tenant_name = $tenant['tenant_name'];
                /*if ($entrance_date =
                    $tenant['entrance_date']
                ) {
                    $tenant_name .= ' ' . Yii::t('common.view', 'Entry date: {date}', [
                            'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
                        ]);
                }
                if ($exit_date = $tenant['exit_date']) {
                    $tenant_name .= ' ' . Yii::t('common.view', 'Exit date: {date}', [
                            'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
                        ]);
                }*/
                $rows[] = $tenant_name;
                $rows[] = $tenant['meter_id'];
                //$rows[] = $tenant['total_single_rules'];
                //$rows[] = $tenant['total_group_rules'];
                //$rows[] = $tenant['total_fixed_rules'];
                //$rows[] = $tenant['fixed_payment'];
                //$rows[] = $tenant['total'];
                if (!empty($additional_parameters['column_total_pay_single_channel_rules'])) {
                    $tenant_total_to_pay += $tenant['total_single_rules'];
                    $rows[] = Yii::$app->formatter->asRound($tenant['total_single_rules']);
                }
                if (!empty($additional_parameters['column_total_pay_group_load_rules'])) {
                    $tenant_total_to_pay += $tenant['total_group_rules'];
                    $rows[] = Yii::$app->formatter->asRound($tenant['total_group_rules']);
                }
                if (!empty($additional_parameters['column_total_pay_fixed_load_rules'])) {
                    $tenant_total_to_pay += (int)$tenant['total_fixed_rules'];
                    $rows[] = Yii::$app->formatter->asRound($tenant['total_fixed_rules']);
                }
                if (in_array($power_factor_visibility,
                    [Site::POWER_FACTOR_SHOW_ADD_FUNDS, Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])) {
                    $rows[] = Yii::$app->formatter->asNumberFormat($tenant['power_factor_value'], 3);
                }
                $power_factor_addition = 0;
                if (in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS])) {
                    $power_factor_addition = $tenant_total_to_pay / 100 * $tenant['power_factor_percent'];
                    $tenant_total_to_pay += $power_factor_addition;
                }
                if (in_array($power_factor_visibility,
                    [Site::POWER_FACTOR_SHOW_ADD_FUNDS, Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])) {
                    $rows[] = Yii::$app->formatter->asPrice($power_factor_addition);
                }
                if (!empty($additional_parameters['column_fixed_payment'])) {
                    /**
                     * REPLACE / SHOW fixed_payments on rates on priority `So Rate->Tenant->Site in order of priority`
                     */
                    $_fp = 0;
                    if (CalculationHelper::isCorrectFixedPayment($tenant['fixed_payment'])) //tenant
                    {
                        $_fp = $tenant['fixed_payment'];
                    } else if (CalculationHelper::isCorrectFixedPayment($params['data']['site_fixed_payment'])) //site
                    {
                        $_fp = $params['data']['site_fixed_payment'];
                    }
                    /*else if(CalculationHelper::isCorrectFixedPayment($additional_parameters['rates_fixed_payments'])) //rate
                    {
                        $_fp = $additional_parameters['rates_fixed_payments'];
                    }*/
                    // replace
                    $tenant['fixed_payment'] = $_fp;
                    $tenant_total_to_pay += $tenant['fixed_payment'];
                    $rows[] = Yii::$app->formatter->asRound($tenant['fixed_payment']);
                }
                $rows[] = Yii::$app->formatter->asRound($tenant_total_to_pay);
                //VarDumper::dump($rows, 100, true);
                if (!empty($additional_parameters['is_vat_included'])) {
                    $rows[] =
                        Yii::$app->formatter->asRound(($tenant_total_to_pay / 100) * $params['vat_percentage']);
                    $rows[] = Yii::$app->formatter->asRound($tenant_total_to_pay + (($tenant_total_to_pay / 100) *
                            $params['vat_percentage']));
                }
                foreach ($rows as $c => $row) {
                    $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $row);
                    $objPHPExcelActiveSheet->getStyle(self::columnName($c + 1) . $r)->applyFromArray([
                        'font' => [
                            'size' => 10,
                        ],
                        'borders' => [
                            'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                        ],
                        'alignment' => [
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ],
                    ]);
                    $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 1))->setAutoSize(true);
                }
                $tenants_total_to_pay += $tenant_total_to_pay;
                $r++;
                /* Columns */
            }
            $r++;
            /* Totals without VAT */
            $totals_without_vat = [];
            if(!empty($additional_parameters['is_vat_included'])) {
                $totals_without_vat[] = Yii::t('common.view', 'Total (without VAT)');
            }
            else {
                $totals_without_vat[] = Yii::t('common.view', 'Total');
            }

            if(in_array($power_factor_visibility,
                [Site::POWER_FACTOR_SHOW_ADD_FUNDS, Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])) {
                $totals_without_vat[] = null;
                $totals_without_vat[] = null;
            }
            $totals_without_vat[] = null;
            $totals_without_vat[] = Yii::$app->formatter->asRound($tenants_total_to_pay);
            foreach($totals_without_vat as $c => $total) {
                $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 7) . $r, $total);
                $objPHPExcelActiveSheet->getStyle(self::columnName($c + 7) . $r)->applyFromArray([
                    'font' => [
                        'size' => 10,
                    ],
                    'borders' => [
                        'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 7))->setAutoSize(true);
            }
            $r++;
            /* Totals with VAT */
            $totals_vat = [];
            $totals_vat[] = Yii::t('common.view', 'VAT');
            if(in_array($power_factor_visibility,
                [Site::POWER_FACTOR_SHOW_ADD_FUNDS, Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])) {
                $totals_vat[] = null;
                $totals_vat[] = null;
            }
            $totals_vat[] = Yii::$app->formatter->asPercentage($params['vat_percentage']);
            $tenants_vat = ($tenants_total_to_pay / 100) * $params['vat_percentage'];
            $totals_vat[] = Yii::$app->formatter->asRound($tenants_vat);
            foreach($totals_vat as $c => $total) {
                $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 7) . $r, $total);
                $objPHPExcelActiveSheet->getStyle(self::columnName($c + 7) . $r)->applyFromArray([
                    'font' => [
                        'size' => 10,
                    ],
                    'borders' => [
                        'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 7))->setAutoSize(true);
            }
            $r++;
            $totals_with_vat = [];
            $totals_with_vat[] = Yii::t('common.view', 'Total (including VAT)');
            if(in_array($power_factor_visibility,
                [Site::POWER_FACTOR_SHOW_ADD_FUNDS, Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])) {
                $totals_with_vat[] = null;
                $totals_with_vat[] = null;
            }
            $totals_with_vat[] = null;
            $tenants_total_to_pay += $tenants_vat;
            $totals_with_vat[] = Yii::$app->formatter->asRound($tenants_total_to_pay);
            foreach($totals_with_vat as $c => $total) {
                $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 7) . $r, $total);
                $objPHPExcelActiveSheet->getStyle(self::columnName($c + 7) . $r)->applyFromArray([
                    'font' => [
                        'size' => 10,
                    ],
                    'borders' => [
                        'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 7))->setAutoSize(true);
            }
            $r++;
            /* Additional parameters */
            if(!empty($additional_parameters)) {
                $additional_params_bill = [];
                $additional_params_bill[] = Yii::t('common.view', 'Electric company bill');
                if(in_array($power_factor_visibility,
                    [Site::POWER_FACTOR_SHOW_ADD_FUNDS, Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])) {
                    $additional_params_bill[] = null;
                    $additional_params_bill[] = null;
                }
                $additional_params_bill[] = null;
                if(isset($additional_parameters['electric_company_price'])) {
                    $additional_params_bill[] =
                        Yii::$app->formatter->asRound($additional_parameters['electric_company_price']);
                }
                else {
                    $additional_params_bill[] = null;
                }
                foreach($additional_params_bill as $c => $value) {
                    $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 7) . $r, $value);
                    $objPHPExcelActiveSheet->getStyle(self::columnName($c + 7) . $r)->applyFromArray([
                        'font' => [
                            'size' => 10,
                        ],
                        'borders' => [
                            'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                        ],
                        'alignment' => [
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ],
                    ]);
                    $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 7))->setAutoSize(true);
                }
                $r++;
                $additional_params_diff_nis = [];
                $additional_params_diff_nis[] = Yii::t('common.view', 'Diff in NIS');
                if(in_array($power_factor_visibility,
                    [Site::POWER_FACTOR_SHOW_ADD_FUNDS, Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])) {
                    $additional_params_diff_nis[] = null;
                    $additional_params_diff_nis[] = null;
                }
                $additional_params_diff_nis[] = null;
                if(isset($additional_parameters['electric_company_price'])) {
                    $additional_params_diff_nis[] = Yii::$app->formatter->asRound($tenants_total_to_pay -
                        $additional_parameters['electric_company_price']);
                }
                else {
                    $additional_params_diff_nis[] = null;
                }
                foreach($additional_params_diff_nis as $c => $value) {
                    $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 7) . $r, $value);
                    $objPHPExcelActiveSheet->getStyle(self::columnName($c + 7) . $r)->applyFromArray([
                        'font' => [
                            'size' => 10,
                        ],
                        'borders' => [
                            'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                        ],
                        'alignment' => [
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ],
                    ]);
                    $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 7))->setAutoSize(true);
                }
                $r++;
                $additional_params_diff_percent = [];
                $additional_params_diff_percent[] = Yii::t('common.view', 'Diff in %');
                if(in_array($power_factor_visibility,
                    [Site::POWER_FACTOR_SHOW_ADD_FUNDS, Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])) {
                    $additional_params_diff_percent[] = null;
                    $additional_params_diff_percent[] = null;
                }
                $additional_params_diff_percent[] = null;
                if(!empty($additional_parameters['electric_company_price']) &&
                    ($difference = $tenants_total_to_pay - $additional_parameters['electric_company_price'])
                ) {
                    $additional_params_diff_percent[] = Yii::$app->formatter->asPercentage($difference /
                        $additional_parameters['electric_company_price'] *
                        100);
                }
                else {
                    $additional_params_diff_percent[] = null;
                }
                foreach($additional_params_diff_percent as $c => $value) {
                    $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 7) . $r, $value);
                    $objPHPExcelActiveSheet->getStyle(self::columnName($c + 7) . $r)->applyFromArray([
                        'font' => [
                            'size' => 10,
                        ],
                        'borders' => [
                            'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                        ],
                        'alignment' => [
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ],
                    ]);
                    $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 7))->setAutoSize(true);
                }
                $r++;
            }
        $r++;
            /* Begin Footer Image */
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName(Yii::$app->name);
            $objDrawing->setDescription(Yii::$app->name);
            $objDrawing->setPath(Yii::getAlias('@backend/web/images/pdf/report/horizontal-footer.png'));
            $objDrawing->setResizeProportional(false);
            $objDrawing->setWidth(350);
            $objDrawing->setCoordinates(self::columnName(1) . $r);
            $objDrawing->setWorksheet($objPHPExcelActiveSheet);
            /* End Footer Image */
        }
    }
}