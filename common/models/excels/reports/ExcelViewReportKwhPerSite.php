<?php

namespace common\models\excels\reports;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\Tenant;
use common\components\data\ExcelView;
use common\models\helpers\reports\ReportGeneratorKwhPerSite;

/**
 * ExcelViewReportKwhPerSite is the class for view report kwh per site excel.
 */
class ExcelViewReportKwhPerSite extends ExcelView
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
		$site = $params['data']['site'];
		$site_owner = $params['data']['site_owner'];
		$additional_parameters = $params['additional_parameters'];

		if (!empty($params['tenants'])) {
			$r = 1;

			/* Begin Head Image */

			$objDrawing = new \PHPExcel_Worksheet_Drawing();
			$objDrawing->setName(Yii::$app->name);
			$objDrawing->setDescription(Yii::$app->name);
			$objDrawing->setPath(Yii::getAlias('@backend/web/images/pdf/report/horizontal-header.png'));
			$objDrawing->setResizeProportional(false);
			$objDrawing->setWidth(450);
			$objDrawing->setCoordinates(self::columnName(1) . $r);
			$objDrawing->setWorksheet($objPHPExcelActiveSheet);
			$r += 6;

			/* End Head Image */

			/* Head */

			$r++;
			
			$head = [
				[
					'name' => Yii::t('common.view', 'Kwh report'),
					'value' => null,
				],
				[
					'name' => Yii::t('common.view', 'To'). ': ' .$site_owner->name,
					'value' => null,
				],
				[
					'name' => Yii::t('common.view', 'Kwh summary report for'). ': ' .$site->name,
					'value' => Yii::t('common.view', 'Issue date'). ': ' .Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'),
				],
				[
					'name' => Yii::t('common.view', 'Report range'). ': ' .Yii::$app->formatter->asDate($report->from_date, 'dd/MM/yy') . ' - ' .Yii::$app->formatter->asDate($report->to_date, 'dd/MM/yy'),
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

				$objPHPExcelActiveSheet->setCellValue(self::columnName(9) . $r, $value['value']);
				$objPHPExcelActiveSheet->getStyle(self::columnName(9) . $r)->applyFromArray([
					'font' => [
						'size' => 10,
					],
				]);
				$objPHPExcelActiveSheet->getColumnDimension(self::columnName(9))->setAutoSize(true);

				$r++;
			}

			$r+=2;
            $columns = [
                Yii::t('common.view', 'Row number'),
                Yii::t('common.view', 'Tenant ID'),
                Yii::t('common.view', 'Tenant name'),
                Yii::t('common.view', 'Meter ID / Group Name'),
                Yii::t('common.view', 'Air consumption'),
                Yii::t('common.view', 'Electrical consumption'),
                Yii::t('common.view', 'Air consumption'),
                Yii::t('common.view', 'Electrical consumption'),
                Yii::t('common.view', 'Air consumption'),
                Yii::t('common.view', 'Electrical consumption'),
                Yii::t('common.view', 'Total Kwh'),
                Yii::t('common.view', 'Power factor'),
                Yii::t('common.view', 'Max demand'),
            ];
            $part = 'Pisga Kwh';
            foreach ($columns as $c => $column) {
                if ($column == 'Air consumption' || $column == 'Electrical consumption')
                {
                    if ($column == 'Air consumption')
                    {
                        $objPHPExcelActiveSheet->mergeCells(self::columnName($c + 1) . ($r - 1) . ':' . self::columnName($c + 2) . ($r - 1));
                        $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . ($r - 1), $part);
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
                        $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 1) . ($r - 1))->setAutoSize(true);
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
                    $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 1) . $r)->setAutoSize(true);
                }
                else {
                    $objPHPExcelActiveSheet->mergeCells(self::columnName($c + 1) . ($r - 1) . ':' . self::columnName($c + 1) . $r);
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
                    $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 1) . ($r - 1))->setAutoSize(true);
                }
            }

            /* Rows */

            $r++;
            $index = 1;
            //VarDumper::dump($params['data']['tenants'], 100, true);
			foreach ($params['data']['tenants'] as $tenant) {

					$total_tenant = [
						'pisga' => 0,
						'geva' => 0,
						'shefel' => 0,
						'total_consumption' => 0,
					];
                    $rows = [];
                    $rows[] = $index;
                    $rows[] = $tenant['tenant_id'];

                    $tenant_name = $tenant['tenant_name'];

                    if ($entrance_date = $tenant['entrance_date']) {
                        $tenant_name .= ' ' .Yii::t('common.view', 'Entry date: {date}', [
                            'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
                        ]);
                    }
                    if ($exit_date = $tenant['exit_date']) {
                        $tenant_name .= ' ' .Yii::t('common.view', 'Exit date: {date}', [
                            'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
                        ]);
                    }

                    $rows[] = $tenant_name;
                    $offset = 1;
                    $rows[] = $tenant['meter_id'];

                    $total_tenant['pisga_consumption'] += $tenant['pisga_consumption'];
                    $total_tenant['pisga_reading'] += $tenant['pisga_reading'];
                    $total_tenant['geva_consumption'] += $tenant['geva_consumption'];
                    $total_tenant['geva_reading'] += $tenant['geva_reading'];
                    $total_tenant['shefel_consumption'] += $tenant['shefel_consumption'];
                    $total_tenant['shefel_reading'] += $tenant['shefel_reading'];
                    $total_tenant['total_consumption'] += $tenant['consumption_total'];

                    $rows[] = Yii::$app->formatter->asRound($tenant['pisga_consumption']);
                    $rows[] = Yii::$app->formatter->asRound($tenant['pisga_reading']);
                    $rows[] = Yii::$app->formatter->asRound($tenant['geva_consumption']);
                    $rows[] = Yii::$app->formatter->asRound($tenant['geva_reading']);
                    $rows[] = Yii::$app->formatter->asRound($tenant['shefel_consumption']);
                    $rows[] = Yii::$app->formatter->asRound($tenant['shefel_reading']);
                    $rows[] = Yii::$app->formatter->asRound($tenant['consumption_total']);
                    $rows[] = Yii::$app->formatter->asRound($tenant['power_factor_value']);
                    $rows[] = (!empty($rule['max_consumption'])) ? Yii::$app->formatter->asRound($rule['max_consumption']) : null;

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
                        $objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + $offset))->setAutoSize(true);
                    }

                    $r++;

					$index++;

					/* Totals tenant */

					$total_tenants = [
						Yii::t('common.view', 'Total consumption'),
						Yii::$app->formatter->asRound($total_tenant['pisga_consumption']),
                        Yii::$app->formatter->asRound($total_tenant['pisga_reading']),
                        Yii::$app->formatter->asRound($total_tenant['geva_consumption']),
                        Yii::$app->formatter->asRound($total_tenant['pisga_reading']),
						Yii::$app->formatter->asRound($total_tenant['shefel_consumption']),
						Yii::$app->formatter->asRound($total_tenant['shefel_reading']),
						Yii::$app->formatter->asRound($total_tenant['total_consumption']),
					];

					foreach ($total_tenants as $c => $total_tenant) {
						$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 4) . $r, $total_tenant);
						$objPHPExcelActiveSheet->getStyle(self::columnName($c + 4) . $r)->applyFromArray([
							'font' => [
								'size' => 10,
							],
							'borders' => [
								'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
							],
							'fill' => [
								'type' => \PHPExcel_Style_Fill::FILL_SOLID,
								'color' => ['rgb' => 'e2e2e2'],
							],
							'alignment' => [
								'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							],
						]);
						$objPHPExcelActiveSheet->getColumnDimension(self::columnName($c + 4))->setAutoSize(true);
					}

					$r++;
				}
            $r++;

            /* Totals */

            $totals = [
                Yii::t('common.view', 'Total consumption'),
                Yii::$app->formatter->asRound($params['data']['diff']['pisga_consumption']),
                Yii::$app->formatter->asRound($params['data']['diff']['geva_consumption']),
                Yii::$app->formatter->asRound($params['data']['diff']['shefel_consumption']),
                Yii::$app->formatter->asRound($params['data']['diff']['consumption_total']),
            ];

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

            $r++;

            /* Additional parameters */

            if (!empty($additional_parameters)) {
                $additional_params_sum = ArrayHelper::getValue($additional_parameters, 'pisga_consumption', 0) + ArrayHelper::getValue($additional_parameters, 'geva_consumption', 0) + ArrayHelper::getValue($additional_parameters, 'shefel_consumption', 0);
                $additional_params = [
                    [
                        Yii::t('common.view', 'Electric company bill'),
                        (isset($additional_parameters['pisga_consumption'])) ? Yii::$app->formatter->asRound($additional_parameters['pisga_consumption']) : null,
                        (isset($additional_parameters['geva_consumption'])) ? Yii::$app->formatter->asRound($additional_parameters['geva_consumption']) : null,
                        (isset($additional_parameters['shefel_consumption'])) ? Yii::$app->formatter->asRound($additional_parameters['shefel_consumption']) : null,
                        Yii::$app->formatter->asRound($additional_params_sum),
                    ],
                    [
                        Yii::t('common.view', 'Diff in Kwh'),
                        (isset($additional_parameters['pisga_consumption'])) ? Yii::$app->formatter->asRound($params['data']['diff']['pisga_consumption'] - $additional_parameters['pisga_consumption']) : null,
                        (isset($additional_parameters['geva_consumption'])) ? Yii::$app->formatter->asRound($params['data']['diff']['geva_consumption'] - $additional_parameters['geva_consumption']) : null,
                        (isset($additional_parameters['shefel_consumption'])) ? Yii::$app->formatter->asRound($params['data']['diff']['shefel_consumption'] - $additional_parameters['shefel_consumption']) : null,
                        Yii::$app->formatter->asRound($params['data']['diff']['consumption_total'] - $additional_params_sum),
                    ],
                    [
                        Yii::t('common.view', 'Diff in %'),
                        (isset($additional_parameters['pisga_consumption']) && ($difference = $params['data']['diff']['pisga_consumption'] - $additional_parameters['pisga_consumption']) != 0) ? Yii::$app->formatter->asPercentage($difference / $additional_parameters['pisga_consumption'] * 100) : null,
                        (isset($additional_parameters['geva_consumption']) && ($difference = $params['data']['diff']['geva_consumption'] - $additional_parameters['geva_consumption']) != 0) ? Yii::$app->formatter->asPercentage($difference / $additional_parameters['geva_consumption'] * 100) : null,
                        (isset($additional_parameters['shefel_consumption']) && ($difference = $params['data']['diff']['shefel_consumption'] - $additional_parameters['shefel_consumption']) != 0) ? Yii::$app->formatter->asPercentage($difference / $additional_parameters['shefel_consumption'] * 100) : null,
                        (!empty($additional_params_sum) && ($difference = $params['data']['diff']['consumption_total'] - $additional_params_sum) != 0) ? Yii::$app->formatter->asPercentage($difference / $additional_params_sum * 100) : null,
                    ],
                ];

                foreach ($additional_params as $additional_parameter) {
                    foreach ($additional_parameter as $c => $additional_parameter_values) {
                        $objPHPExcelActiveSheet->setCellValue(self::columnName($c + 4) . $r, $additional_parameter_values);
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

                    $r++;
                }
            }

			/* Begin Footer Image */

			$objDrawing = new \PHPExcel_Worksheet_Drawing();
			$objDrawing->setName(Yii::$app->name);
			$objDrawing->setDescription(Yii::$app->name);
			$objDrawing->setPath(Yii::getAlias('@backend/web/images/pdf/report/horizontal-footer.png'));
			$objDrawing->setResizeProportional(false);
			$objDrawing->setWidth(450);
			$objDrawing->setCoordinates(self::columnName(1) . $r);
			$objDrawing->setWorksheet($objPHPExcelActiveSheet);

			/* End Footer Image */
		}
	}
}