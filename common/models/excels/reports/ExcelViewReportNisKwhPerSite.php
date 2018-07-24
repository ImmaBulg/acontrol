<?php

namespace common\models\excels\reports;

use common\helpers\CalculationHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;
use common\models\Site;
use common\models\Tenant;
use common\components\data\ExcelView;
use common\models\helpers\reports\ReportGeneratorNisKwhPerSite;

/**
 * ExcelViewReportNisKwhPerSite is the class for view report nis + kwh per site excel.
 */
class ExcelViewReportNisKwhPerSite extends ExcelView
{
    /**
     * @inheritdoc
     */
    public function setObjPHPExcelAttribute()
    {
        $objPHPExcel = $this->getObjPHPExcel();
        $objPHPExcelActiveSheet = $objPHPExcel->getActiveSheet();
        $params = $this->getParams();
        $report = $params['report'];
        $site = $params['site'];
        $site_owner = $params['site_owner'];
        $additional_parameters = $params['additional_parameters'];
        $power_factor_visibility = (!empty($additional_parameters)) ?
            ArrayHelper::getValue($additional_parameters, 'power_factor_visibility', Site::POWER_FACTOR_DONT_SHOW) :
            Site::POWER_FACTOR_DONT_SHOW;
        if (!empty($params['data'])) {
            $r = 1;
            /* Begin Head Image */
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName(Yii::$app->name);
            $objDrawing->setDescription(Yii::$app->name);
            $objDrawing->setPath(Yii::getAlias('@backend/web/images/pdf/report/horizontal-header.png'));
            $objDrawing->setResizeProportional(false);
            $objDrawing->setWidth(900);
            $objDrawing->setCoordinates(self::columnName(1) . $r);
            $objDrawing->setWorksheet($objPHPExcelActiveSheet);
            $r += 6;
            /* End Head Image */
            /* Head */
            $r++;
            $head = [
                [
                    'name' => Yii::t('common.view', 'NIS + Kwh report'),
                    'value' => null,
                ],
                [
                    'name' => Yii::t('common.view', 'To') . ': ' . $site_owner->name,
                    'value' => null,
                ],
                [
                    'name' => Yii::t('common.view', 'Kwh summary report for') . ': ' . $site->name,
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
            foreach ($head as $c => $value) {
                $objPHPExcelActiveSheet->setCellValue(self::columnName(1) . $r, $value['name']);
                $objPHPExcelActiveSheet->getStyle(self::columnName(1) . $r)->applyFromArray([
                    'font' => [
                        'size' => 10,
                    ],
                ]);
                $objPHPExcelActiveSheet->getColumnDimension(self::columnName(1))->setAutoSize(true);
                $objPHPExcelActiveSheet->setCellValue(self::columnName(10) . $r, $value['value']);
                $objPHPExcelActiveSheet->getStyle(self::columnName(10) . $r)->applyFromArray([
                    'font' => [
                        'size' => 10,
                    ],
                ]);
                $objPHPExcelActiveSheet->getColumnDimension(self::columnName(10))->setAutoSize(true);
                $r++;
            }
            $r += 2;
            foreach ($params['total'] as &$total) {
                $total['total_pay'] = 0;
            }

            /* Columns */
            $columns = [];
            $columns[] = Yii::t('common.view', 'No');
            $columns[] = Yii::t('common.view', 'Tenant ID');
            $columns[] = Yii::t('common.view', 'Tenant name');
            $columns[] = Yii::t('common.view', 'Meter ID / Group Name');
            $columns[] = Yii::t('common.view', 'Air consumption');
            $columns[] = Yii::t('common.view', 'Electrical consumption');
            $columns[] = Yii::t('common.view', 'To pay before VAT');
            $columns[] = Yii::t('common.view', 'Air consumption');
            $columns[] = Yii::t('common.view', 'Electrical consumption');
            $columns[] = Yii::t('common.view', 'To pay before VAT');
            $columns[] = Yii::t('common.view', 'Air consumption');
            $columns[] = Yii::t('common.view', 'Electrical consumption');
            $columns[] = Yii::t('common.view', 'To pay before VAT');
            $columns[] = Yii::t('common.view', 'Total consumption in Kwh');
            $columns[] = Yii::t('common.view', 'Group loads in Kwh');
            $columns[] = Yii::t('common.view', 'Money addition');
            $columns[] = Yii::t('common.view', 'Fixed payment');
            $columns[] = Yii::t('common.view', 'Total to pay before VAT');
            $columns[] = Yii::t('common.view', 'VAT');
            $columns[] = Yii::t('common.view', 'Total to pay');
            $part = 'Pisga Kwh';
            foreach ($columns as $c => $column) {
                if ($column == Yii::t('common.view', 'Air consumption') || $column == Yii::t('common.view', 'Electrical consumption') || $column == Yii::t('common.view', 'To pay before VAT')) {
                    if ($column == Yii::t('common.view', 'Air consumption')) {
                        $objPHPExcelActiveSheet->mergeCells(self::columnName($c + 1) . ($r - 1) . ':' . self::columnName($c + 3) . ($r - 1));
                        $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . ($r - 1), Yii::t('common.view', $part));
                        $objPHPExcelActiveSheet->getStyle(self::columnName($c + 1) . ($r - 1))->applyFromArray([
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
                        $objPHPExcelActiveSheet->getStyle(self::columnName($c + 2) . ($r - 1))->applyFromArray([
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
                        $objPHPExcelActiveSheet->getStyle(self::columnName($c + 3) . ($r - 1))->applyFromArray([
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
                        $part = ($part == 'Pisga Kwh') ? 'Geva Kwh' : 'Shefel Kwh';
                    }
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
                } else {
                    $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . ($r - 1), $column);
                    $objPHPExcelActiveSheet->getStyle(self::columnName($c + 1) . ($r - 1))->applyFromArray([
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
                    $objPHPExcelActiveSheet->mergeCells(self::columnName($c + 1) . ($r - 1) . ':' . self::columnName($c + 1) . $r);
                }
            }
            $index = 1;
            foreach ($params['data']['tenants'] as $tenant) {
                /* Rows */
                $r++;
                $total_tenant = [
                    'pisga_consumption' => 0,
                    'pisga_reading' => 0,
                    'pisga_pay' => 0,
                    'geva_consumption' => 0,
                    'geva_reading' => 0,
                    'geva_pay' => 0,
                    'shefel_consumption' => 0,
                    'shefel_reading' => 0,
                    'shefel_pay' => 0,
                ];
                $tenant_total_pay = 0;
                foreach ($tenant['rules'] as $rule_index => $rule) {
                    $rows = [];
                    foreach ($rule as $rule_type => $rul) {
                        if ($rule_type == 'regular' || $rule_type == 'irregular') {
                            $rows = [];
                            $rows[] = $index;
                            $rows[] = $tenant['tenant_id'];
                            $tenant_name = $tenant['tenant_name'];
                            if ($entrance_date =
                                $tenant['entrance_date']
                            ) {
                                $tenant_name .= ' ' . Yii::t('common.view', 'Entry date: {date}', [
                                        'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
                                    ]);
                            }
                            if ($exit_date =
                                $tenant['exit_date']
                            ) {
                                $tenant_name .= ' ' . Yii::t('common.view', 'Exit date: {date}', [
                                        'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
                                    ]);
                            }
                            $rows[] = $tenant_name;
                            $offset = 1;
                            $rows[] = $rule['meter'] . ' - ' . $rule['channel'];

                            $rows[] = Yii::$app->formatter->asRound($rul['pisga_consumption']);
                            $rows[] = Yii::$app->formatter->asRound($rul['pisga_reading']);
                            $total_tenant['pisga_reading'] += $rul['pisga_reading'];
                            $rows[] = Yii::$app->formatter->asRound($rul['pisga_pay']);
                            $rows[] = Yii::$app->formatter->asRound($rul['geva_consumption']);
                            $rows[] = Yii::$app->formatter->asRound($rul['geva_reading']);
                            $total_tenant['geva_reading'] += $rul['geva_reading'];
                            $rows[] = Yii::$app->formatter->asRound($rul['geva_pay']);
                            $rows[] = Yii::$app->formatter->asRound($rul['shefel_consumption']);
                            $rows[] = Yii::$app->formatter->asRound($rul['shefel_reading']);
                            $total_tenant['shefel_reading'] += $rul['shefel_reading'];
                            $rows[] = Yii::$app->formatter->asRound($rul['shefel_pay']);
                            $rows[] = Yii::$app->formatter->asRound($rul['pisga_consumption'] + $rul['geva_consumption'] + $rul['shefel_consumption']);
                            $rows[] = null;
                            $rows[] = Yii::$app->formatter->asRound($rul['fixed_rules']);
                            $tenant_total_pay = $rul['total_pay'] + $rul['fixed_rules'];
                            $rows[] = Yii::$app->formatter->asRound($rule['fixed_payment']);
                            $rows[] = Yii::$app->formatter->asRound($tenant_total_pay);
                            $rows[] = Yii::$app->formatter->asRound($additional_parameters['tax']);
                            if (!empty($additional_parameters['is_vat_included'])) {
                                $rows[] = Yii::$app->formatter->asRound($tenant_total_pay + (($tenant_total_pay / 100) *
                                        $params['vat_percentage']));
                            }
                            $rows[] = Yii::$app->formatter->asRound($tenant_total_pay + $additional_parameters['tax'] + $rule['fixed_payment']);
                            foreach ($rows as $c => $row) {
                                $objPHPExcelActiveSheet->setCellValue(self::columnName($c + $offset) . $r, $row);
                                $objPHPExcelActiveSheet->getStyle(self::columnName($c + $offset) . $r)->applyFromArray([
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
                                $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + $offset))
                                                       ->setAutoSize(true);
                            }
                            $r++;
                            $index++;
                        }
                    }
                }
                $total_tenant['pisga_consumption'] += $tenant['total']['pisga_consumption'];
                $total_tenant['pisga_pay'] += $tenant['total']['pisga_pay'];
                $total_tenant['geva_consumption'] += $tenant['total']['geva_consumption'];
                $total_tenant['geva_pay'] += $tenant['total']['geva_pay'];
                $total_tenant['shefel_consumption'] += $tenant['total']['shefel_consumption'];
                $total_tenant['shefel_pay'] += $tenant['total']['shefel_pay'];
                $total_tenant['total_consumption'] += ($tenant['total']['pisga_consumption'] + $tenant['total']['geva_consumption'] + $tenant['total']['shefel_consumption']);
                $total_tenant['total_fixed_rule'] = $tenant['total']['total_fixed_rules'];
                $index++;
                /* Totals tenant */
                $total_tenants = [
                    Yii::t('common.view', 'Total'),
                    Yii::$app->formatter->asRound($total_tenant['pisga_consumption']),
                    Yii::$app->formatter->asRound($total_tenant['pisga_reading']),
                    Yii::$app->formatter->asRound($total_tenant['pisga_pay']),
                    Yii::$app->formatter->asRound($total_tenant['geva_consumption']),
                    Yii::$app->formatter->asRound($total_tenant['geva_reading']),
                    Yii::$app->formatter->asRound($total_tenant['geva_pay']),
                    Yii::$app->formatter->asRound($total_tenant['shefel_consumption']),
                    Yii::$app->formatter->asRound($total_tenant['shefel_reading']),
                    Yii::$app->formatter->asRound($total_tenant['shefel_pay']),
                ];
                $total_tenants[] = Yii::$app->formatter->asRound($total_tenant['total_consumption']);
                $total_tenants[] = 0;
                $total_tenants[] = Yii::$app->formatter->asRound($total_tenant['total_fixed_rule']);
                $tenant_total_pay += $total_tenant['total_fixed_rule'];
                if (!empty($additional_parameters['fixed_payment'])) {
                    /**
                     * REPLACE / SHOW fixed_payments on rates on priority `So Rate->Tenant->Site in order of priority`
                     */
                    $_fp = 0;
                    if (CalculationHelper::isCorrectFixedPayment($total_tenant['fixed_payment'])) //tenant
                    {
                        $_fp = $tenant['fixed_payment'];
                    } else {
                        if (CalculationHelper::isCorrectFixedPayment($site->relationSiteBillingSetting['fixed_payment'])) // site
                        {
                            $_fp = $site->relationSiteBillingSetting['fixed_payment'];
                        } else {
                            if (CalculationHelper::isCorrectFixedPayment($additional_parameters['rates_fixed_payments'])) //rate
                            {
                                $_fp = $additional_parameters['rates_fixed_payments'];
                            }
                        }
                    }
                    // replace
                    $total_tenant['fixed_payment'] = $_fp;
                    $total_tenants[] = Yii::$app->formatter->asRound($total_tenant['fixed_payment']);
                }
                $tenant_total_pay += $total_tenant['fixed_payment'];
                $total_tenants[] =
                    Yii::$app->formatter->asRound($tenant_total_pay);
                $total_tenants[] = Yii::$app->formatter->asRound($additional_parameters['tax']);
                $total_tenants[] = Yii::$app->formatter->asRound($tenant_total_pay + $additional_parameters['tax']);
                $params['total']['test']['total_pay'] += $tenant_total_pay;
                foreach ($total_tenants as $c => $total_tenant) {
                    $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 4) . $r, $total_tenant);
                    $objPHPExcelActiveSheet->getStyle(self::columnName($c + 4) . $r)->applyFromArray([
                        'font' => [
                            'size' => 10,
                        ],
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'e2e2e2'],
                        ],
                        'borders' => [
                            'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                        ],
                        'alignment' => [
                            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        ],
                    ]);
                    $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 4))->setAutoSize(true);
                }
                $r+=2;

                /* Totals */
                /*$totals = [
                    Yii::t('common.view', 'Total'),
                    Yii::$app->formatter->asRound($params['total'][$type]['pisga']['reading_diff']),
                    Yii::$app->formatter->asRound($params['total'][$type]['geva']['reading_diff']),
                    Yii::$app->formatter->asRound($params['total'][$type]['shefel']['reading_diff']),
                    Yii::$app->formatter->asRound($params['total'][$type]['total_consumption']),
                ];
                if (in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                    Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])) {
                    $totals[] = null;
                    $totals[] = null;
                }
                if (!empty($additional_parameters['column_fixed_payment'])) {
                    $totals[] = Yii::$app->formatter->asRound($params['total'][$type]['fixed_payment']);
                }
                // todo: requires clarification - is it necessary to change here or not?
                $totals[] = Yii::$app->formatter->asRound($params['total'][$type]['total_pay']);
                if (!empty($additional_parameters['is_vat_included'])) {
                    $totals[] = Yii::$app->formatter->asRound(($params['total'][$type]['total_pay'] / 100) *
                        $params['vat_percentage']);
                    $totals[] = Yii::$app->formatter->asRound($params['total'][$type]['total_pay'] +
                        (($params['total'][$type]['total_pay'] / 100) *
                            $params['vat_percentage']));
                }
                $totals[] = Yii::$app->formatter->asRound($params['total'][$type]['power_factor_pay']);
                foreach ($totals as $c => $total) {
                    $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 4) . $r, $total);
                    $objPHPExcelActiveSheet->getStyle(self::columnName($c + 4) . $r)->applyFromArray([
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
                    $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 4))->setAutoSize(true);
                }
                $r++;*/
            }
            /* Additional parameters */
            if (!empty($additional_parameters)) {
                $additional_params_sum =
                    ArrayHelper::getValue($additional_parameters, 'electric_company_pisga', 0) +
                    ArrayHelper::getValue($additional_parameters, 'electric_company_geva', 0) +
                    ArrayHelper::getValue($additional_parameters, 'electric_company_shefel', 0);
                if (!empty($additional_parameters['fixed_payment'])) {
                    $additional_params = [
                        [
                            Yii::t('common.view', 'Electric company bill'),
                            (isset($additional_parameters['electric_company_pisga'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['electric_company_pisga']) :
                                null,
                            (isset($additional_parameters['electric_company_geva'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['electric_company_geva']) :
                                null,
                            (isset($additional_parameters['electric_company_shefel'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['electric_company_shefel']) :
                                null,
                            Yii::$app->formatter->asRound($additional_params_sum),
                            null,
                            (isset($additional_parameters['electric_company_price'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['electric_company_price']) :
                                null,
                        ],
                        [
                            Yii::t('common.view', 'Diff in Kwh'),
                            (isset($additional_parameters['electric_company_pisga'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['diff']['pisga']) :
                                null,
                            (isset($additional_parameters['electric_company_geva'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['diff']['geva']) :
                                null,
                            (isset($additional_parameters['electric_company_shefel'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['diff']['shefel']) :
                                null,
                            (isset($additional_parameters['electric_company_price'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['diff']['price']) :
                                null,
                        ],
                        [
                            Yii::t('common.view', 'Diff in %'),
                            (!empty($additional_parameters['electric_company_pisga']) && ($difference =
                                    $additional_parameters['diff']['pisga'] -
                                    $additional_parameters['electric_company_pisga'])) ?
                                Yii::$app->formatter->asPercentage($difference /
                                    $additional_parameters['electric_company_pisga'] *
                                    100) : null,
                            (!empty($additional_parameters['electric_company_geva']) && ($difference =
                                    $additional_parameters['diff']['geva'] -
                                    $additional_parameters['electric_company_geva'])) ?
                                Yii::$app->formatter->asPercentage($difference /
                                    $additional_parameters['electric_company_geva'] *
                                    100) : null,
                            (!empty($additional_parameters['electric_company_shefel']) && ($difference =
                                    $additional_parameters['diff']['shefel'] -
                                    $additional_parameters['electric_company_shefel'])) ?
                                Yii::$app->formatter->asPercentage($difference /
                                    $additional_parameters['electric_company_shefel'] *
                                    100) : null,
                            (!empty($additional_params_sum) && ($difference =
                                    $additional_parameters['total_electricity_consumption'] - $additional_params_sum)) ?
                                Yii::$app->formatter->asPercentage($difference / $additional_params_sum * 100) :
                                null,
                            null,
                            (!empty($additional_parameters['electric_company_price']) && ($difference =
                                    $additional_parameters['diff']['price'] -
                                    $additional_parameters['electric_company_price'])) ?
                                Yii::$app->formatter->asPercentage($difference /
                                    $additional_parameters['electric_company_price'] *
                                    100) : null,
                        ],
                    ];
                } else {
                    $additional_params = [
                        [
                            Yii::t('common.view', 'Electric company bill'),
                            (isset($additional_parameters['electric_company_pisga'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['electric_company_pisga']) :
                                null,
                            (isset($additional_parameters['electric_company_geva'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['electric_company_geva']) :
                                null,
                            (isset($additional_parameters['electric_company_shefel'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['electric_company_shefel']) :
                                null,
                            Yii::$app->formatter->asRound($additional_params_sum),
                            (isset($additional_parameters['electric_company_price'])) ?
                                Yii::$app->formatter->asRound($additional_parameters['electric_company_price']) :
                                null,
                        ],
                        [
                            Yii::t('common.view', 'Diff in Kwh'),
                            (isset($additional_parameters['electric_company_pisga'])) ?
                                Yii::$app->formatter->asRound($params['total'][$type]['pisga']['reading_diff'] -
                                    $additional_parameters['electric_company_pisga']) :
                                null,
                            (isset($additional_parameters['electric_company_geva'])) ?
                                Yii::$app->formatter->asRound($params['total'][$type]['geva']['reading_diff'] -
                                    $additional_parameters['electric_company_geva']) :
                                null,
                            (isset($additional_parameters['electric_company_shefel'])) ?
                                Yii::$app->formatter->asRound($params['total'][$type]['shefel']['reading_diff'] -
                                    $additional_parameters['electric_company_shefel']) :
                                null,
                            Yii::$app->formatter->asRound($params['total'][$type]['total_consumption'] -
                                $additional_params_sum),
                            (isset($additional_parameters['electric_company_price'])) ?
                                Yii::$app->formatter->asRound($params['total'][$type]['total_pay'] -
                                    $additional_parameters['electric_company_price']) :
                                null,
                        ],
                        [
                            Yii::t('common.view', 'Diff in %'),
                            (!empty($additional_parameters['electric_company_pisga']) && ($difference =
                                    $params['total'][$type]['pisga']['reading_diff'] -
                                    $additional_parameters['electric_company_pisga'])) ?
                                Yii::$app->formatter->asPercentage($difference /
                                    $additional_parameters['electric_company_pisga'] *
                                    100) : null,
                            (!empty($additional_parameters['electric_company_geva']) && ($difference =
                                    $params['total'][$type]['geva']['reading_diff'] -
                                    $additional_parameters['electric_company_geva'])) ?
                                Yii::$app->formatter->asPercentage($difference /
                                    $additional_parameters['electric_company_geva'] *
                                    100) : null,
                            (!empty($additional_parameters['electric_company_shefel']) && ($difference =
                                    $params['total'][$type]['shefel']['reading_diff'] -
                                    $additional_parameters['electric_company_shefel'])) ?
                                Yii::$app->formatter->asPercentage($difference /
                                    $additional_parameters['electric_company_shefel'] *
                                    100) : null,
                            (!empty($additional_params_sum) && ($difference =
                                    $params['total'][$type]['total_consumption'] - $additional_params_sum)) ?
                                Yii::$app->formatter->asPercentage($difference / $additional_params_sum * 100) :
                                null,
                            (!empty($additional_parameters['electric_company_price']) && ($difference =
                                    $params['total'][$type]['total_pay'] -
                                    $additional_parameters['electric_company_price'])) ?
                                Yii::$app->formatter->asPercentage($difference /
                                    $additional_parameters['electric_company_price'] *
                                    100) : null,
                        ],
                    ];
                }
                foreach ($additional_params as $additional_parameter) {
                    foreach ($additional_parameter as $c => $additional_parameter_values) {
                        $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 4) . $r,
                            $additional_parameter_values);
                        $objPHPExcelActiveSheet->getStyle(self::columnName($c + 4) . $r)->applyFromArray([
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
                        $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 4))
                                               ->setAutoSize(true);
                    }
                    $r++;
                }
            }
        }
        $r++;
        /* Begin Footer Image */
        $objDrawing = new \PHPExcel_Worksheet_Drawing();
        $objDrawing->setName(Yii::$app->name);
        $objDrawing->setDescription(Yii::$app->name);
        $objDrawing->setPath(Yii::getAlias('@backend/web/images/pdf/report/horizontal-footer.png'));
        $objDrawing->setResizeProportional(false);
        $objDrawing->setWidth(900);
        $objDrawing->setCoordinates(self::columnName(1) . $r);
        $objDrawing->setWorksheet($objPHPExcelActiveSheet);
        /* End Footer Image */
    }
}