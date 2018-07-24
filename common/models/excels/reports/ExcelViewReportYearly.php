<?php

namespace common\models\excels\reports;

use common\helpers\CalculationHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

use common\models\Report;
use common\models\Tenant;
use common\components\data\ExcelView;
use common\models\helpers\reports\ReportGeneratorYearly;

/**
 * ExcelViewReportYearly is the class for view report yearly excel.
 */
class ExcelViewReportYearly extends ExcelView
{
	/**
	 * @inheritdoc
	 */
	public function setObjPHPExcelAttribute()
	{
		$params = $this->getParams();
		$report = $params['report'];

		switch ($report->level) {
			case Report::LEVEL_SITE:
				$this->generateSiteData($params);
				break;
			
			default:
				$this->generateTenantData($params);
				break;
		}
	}

	protected function generateSiteData($params)
	{
		$objPHPExcel = $this->getObjPHPExcel();
		$objPHPExcelActiveSheet = $objPHPExcel->getActiveSheet();

		$report = $params['report'];
		$site = $params['site'];
		$site_owner = $params['site_owner'];
		$additional_parameters = $params['additional_parameters'];

		if (!empty($params['data']['data'])) {
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

			$r++;

			$head = [
				[
					'name' => Yii::t('common.view', 'To'),
					'value' => $site_owner->name,
				],
				[
					'name' => Yii::t('common.view', 'Yearly summary report for site'),
					'value' => $site->name,
				],
				[
					'name' => Yii::t('common.view', 'Report range'). ': ' .Yii::$app->formatter->asDate($report->from_date, 'dd/MM/yy') . ' - ' .Yii::$app->formatter->asDate($report->to_date, 'dd/MM/yy'),
					'value' => Yii::t('common.view', 'Issue date'). ': ' .Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'),
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

			$r++;

			/* Columns */

			$columns = [
				Yii::t('common.view', 'Month'),
				Yii::t('common.view', 'Pisga Kwh'),
				Yii::t('common.view', 'Geva Kwh'),
				Yii::t('common.view', 'Shefel Kwh'),
				Yii::t('common.view', 'Loads Kwh'),
				Yii::t('common.view', 'Extras Kwh'),
				Yii::t('common.view', 'Total Kwh'),
				Yii::t('common.view', 'Extras NIS'),
				Yii::t('common.view', 'Fixed payment'),
				Yii::t('common.view', 'Total NIS'),
				Yii::t('common.view', 'Max demand'),
			];

			foreach ($columns as $c => $column) {
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

			$r++;

			foreach ($params['data']['data'] as $date => $row) {
				/* Rows */

				$rows = [];
				$rows[] = Yii::t('common.common', Yii::$app->formatter->asDate($date, 'MMMM'));
				$rows[] = Yii::$app->formatter->asRound($row['pisga_consumption']);
				$rows[] = Yii::$app->formatter->asRound($row['geva_consumption']);
				$rows[] = Yii::$app->formatter->asRound($row['shefel_consumption']);
				$rows[] = Yii::$app->formatter->asRound($row['total_loads_consumption']);
				$rows[] = Yii::$app->formatter->asRound($row['total_extras_consumption']);
				$rows[] = Yii::$app->formatter->asRound($row['total_consumption']);
				$rows[] = Yii::$app->formatter->asRound($row['total_extras_pay']);
				$rows[] = Yii::$app->formatter->asRound($row['fixed_payment']);
				$rows[] = Yii::$app->formatter->asRound($row['total_pay'] + $row['fixed_payment']);
				$rows[] = Yii::$app->formatter->asRound($row['max_consumption']);

				foreach ($rows as $c => $value) {
					$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $value);
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

				$r++;
			}

			/* Totals */

			$totals = [];
			$totals[] = null;
			$totals[] = Yii::$app->formatter->asRound($params['data']['pisga_consumption']);
			$totals[] = Yii::$app->formatter->asRound($params['data']['geva_consumption']);
			$totals[] = Yii::$app->formatter->asRound($params['data']['shefel_consumption']);
			$totals[] = Yii::$app->formatter->asRound($params['data']['total_loads_consumption']);
			$totals[] = Yii::$app->formatter->asRound($params['data']['total_extras_consumption']);
			$totals[] = Yii::$app->formatter->asRound($params['data']['total_consumption']);
			$totals[] = Yii::$app->formatter->asRound($params['data']['total_extras_pay']);
			$totals[] = Yii::$app->formatter->asRound($params['data']['fixed_payment']);
			$totals[] = Yii::$app->formatter->asRound($params['data']['total_pay'] + $params['data']['fixed_payment']);
			$totals[] = Yii::$app->formatter->asRound($params['data']['max_consumption']);

			foreach ($totals as $c => $value) {
				$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $value);
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

			$r++;

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

	protected function generateTenantData($params)
	{
		$objPHPExcel = $this->getObjPHPExcel();
		$objPHPExcelActiveSheet = $objPHPExcel->getActiveSheet();

		$report = $params['report'];
		$site = $params['site'];
		$site_owner = $params['site_owner'];
		$additional_parameters = $params['additional_parameters'];

		if (!empty($params['data']))
        {
            /**
             * REPLACE / SHOW fixed_payments on rates on priority `So Rate->Tenant->Site in order of priority`
             */
            foreach ($params['data'] as $tenant_id => $tenants)
            {
                foreach ($tenants as $value)
                {
                    $_fp = 0;

                    if (CalculationHelper::isCorrectFixedPayment($value['fixed_payment'])) // tenant
                        $_fp = $value['fixed_payment'];
                    elseif (CalculationHelper::isCorrectFixedPayment($site->relationSiteBillingSetting['fixed_payment'])) // site
                        $_fp = $site->relationSiteBillingSetting['fixed_payment'];
                    elseif (CalculationHelper::isCorrectFixedPayment($additional_parameters['rates_fixed_payments'])) // rates
                        $_fp = $additional_parameters['rates_fixed_payments'];

                    // replace
                    $params['data'][$tenant_id]['fixed_payment'] = $_fp;


                    /*********************************************/
                    if ($tenants['data'])
                    {
                        foreach ($tenants['data'] as $id_tenant => $data_tenant)
                        {
                            $_fp = 0;

                            if (CalculationHelper::isCorrectFixedPayment($data_tenant['fixed_payment'])) // tenant
                                $_fp = $data_tenant['fixed_payment'];
                            elseif (CalculationHelper::isCorrectFixedPayment($site->relationSiteBillingSetting['fixed_payment'])) // site
                                $_fp = $site->relationSiteBillingSetting['fixed_payment'];
                            elseif (CalculationHelper::isCorrectFixedPayment($additional_parameters['rates_fixed_payments'])) // rates
                                  $_fp = $additional_parameters['rates_fixed_payments'];

                            // replace
                            $params['data'][$tenant_id]['data'][$id_tenant]['fixed_payment'] = $_fp;
                        }
                    }
                }
            }



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

			foreach ($params['data'] as $index => $tenant) {
				if($index) $r++;

				/* Head */

				$r++;

				$tenant_name = $tenant['model_tenant']->name;
				if ($entrance_date = $tenant['model_tenant']->getEntranceDateReport($report->from_date, $report->to_date)) {
					$tenant_name .= ' ' .Yii::t('common.view', 'Entry date: {date}', [
						'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
					]);
				}
				if ($exit_date = $tenant['model_tenant']->getExitDateReport($report->from_date, $report->to_date)) {
					$tenant_name .= ' ' .Yii::t('common.view', 'Exit date: {date}', [
						'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
					]);
				}

				$head = [
					[
						'name' => Yii::t('common.view', 'To'),
						'value' => $site_owner->name,
					],
					[
						'name' => Yii::t('common.view', 'Yearly summary report for tenant'),
						'value' => $tenant_name,
					],
					[
						'name' => Yii::t('common.view', 'Report range'). ' ' .Yii::$app->formatter->asDate($report->from_date, 'dd/MM/yy') . ' - ' .Yii::$app->formatter->asDate($report->to_date, 'dd/MM/yy'),
						'value' => Yii::t('common.view', 'Issue date'). ' ' .Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'),
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

					$objPHPExcelActiveSheet->setCellValue(self::columnName(3) . $r, $value['value']);
					$objPHPExcelActiveSheet->getStyle(self::columnName(3) . $r)->applyFromArray([
						'font' => [
							'size' => 10,
						],
					]);
					$objPHPExcelActiveSheet->getColumnDimension(self::columnName(3))->setAutoSize(true);

					$r++;
				}

				$r++;

				if (!empty($tenant['data'])) {
					/* Columns */

					$columns = [
						Yii::t('common.view', 'Month'),
						Yii::t('common.view', 'Pisga Kwh'),
						Yii::t('common.view', 'Geva Kwh'),
						Yii::t('common.view', 'Shefel Kwh'),
						Yii::t('common.view', 'Loads Kwh'),
						Yii::t('common.view', 'Extras Kwh'),
						Yii::t('common.view', 'Total Kwh'),
						Yii::t('common.view', 'Extras NIS'),
						Yii::t('common.view', 'Fixed payment'),
						Yii::t('common.view', 'Total NIS'),
						Yii::t('common.view', 'Max demand'),
					];

					foreach ($columns as $c => $column) {
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

					$r++;

					foreach ($tenant['data'] as $date => $row) {
						/* Rows */

						$rows = [];
						$rows[] = Yii::t('common.common', Yii::$app->formatter->asDate($date, 'MMMM'));
						$rows[] = Yii::$app->formatter->asRound($row['pisga_consumption']);
						$rows[] = Yii::$app->formatter->asRound($row['geva_consumption']);
						$rows[] = Yii::$app->formatter->asRound($row['shefel_consumption']);
						$rows[] = Yii::$app->formatter->asRound($row['total_loads_consumption']);
						$rows[] = Yii::$app->formatter->asRound($row['total_extras_consumption']);
						$rows[] = Yii::$app->formatter->asRound($row['total_consumption']);
						$rows[] = Yii::$app->formatter->asRound($row['total_extras_pay']);
						$rows[] = Yii::$app->formatter->asRound($row['fixed_payment']);
						$rows[] = Yii::$app->formatter->asRound($row['total_pay'] + $row['fixed_payment']);
						$rows[] = Yii::$app->formatter->asRound($row['max_consumption']);

						foreach ($rows as $c => $value) {
							$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $value);
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

						$r++;
					}

					/* Totals */

					$totals = [];
					$totals[] = null;
					$totals[] = Yii::$app->formatter->asRound($tenant['pisga_consumption']);
					$totals[] = Yii::$app->formatter->asRound($tenant['geva_consumption']);
					$totals[] = Yii::$app->formatter->asRound($tenant['shefel_consumption']);
					$totals[] = Yii::$app->formatter->asRound($tenant['total_loads_consumption']);
					$totals[] = Yii::$app->formatter->asRound($tenant['total_extras_consumption']);
					$totals[] = Yii::$app->formatter->asRound($tenant['total_consumption']);
					$totals[] = Yii::$app->formatter->asRound($tenant['total_extras_pay']);
					$totals[] = Yii::$app->formatter->asRound($tenant['fixed_payment']);

                    // todo: requires clarification - is it necessary to change here or not?
					$totals[] = Yii::$app->formatter->asRound($tenant['total_pay'] + $tenant['fixed_payment']);
                    $totals[] = Yii::$app->formatter->asRound($tenant['max_consumption']);

					foreach ($totals as $c => $value) {
						$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $value);
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

					$r++;
				}
			}

			$r++;

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