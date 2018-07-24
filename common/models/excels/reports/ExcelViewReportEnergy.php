<?php

namespace common\models\excels\reports;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

use common\models\Tenant;
use common\components\data\ExcelView;
use common\models\helpers\reports\ReportGeneratorEnergy;

/**
 * ExcelViewReportEnergy is the class for view report energy excel.
 */
class ExcelViewReportEnergy extends ExcelView
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

		if (!empty($params['data'])) {
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
					'name' => Yii::t('common.view', 'Summary of Kwh usage for site'),
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

				$objPHPExcelActiveSheet->setCellValue(self::columnName(7) . $r, $value['value']);
				$objPHPExcelActiveSheet->getStyle(self::columnName(7) . $r)->applyFromArray([
					'font' => [
						'size' => 10,
					],
				]);
				$objPHPExcelActiveSheet->getColumnDimension(self::columnName(7))->setAutoSize(true);

				$r++;
			}

			$r++;

			foreach ($params['data']['rows'] as $index => $rows) {

				/* Columns */

				$columns = [
					Yii::t('common.view', 'Grandparent Meter ID'),
					Yii::t('common.view', 'Grandparent Total Kwh'),
					Yii::t('common.view', 'Parent Meter ID'),
					Yii::t('common.view', 'Parent Total Kwh'),
					Yii::t('common.view', 'Tenant name'),
					Yii::t('common.view', 'Meter ID'),
					Yii::t('common.view', 'Total Kwh'),
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

				/* Rows */

				$r++;

				foreach($rows as $row) {
					$rows = [];
					$rows[] = ArrayHelper::getValue($row, 'grandparent_name');
					$rows[] = ArrayHelper::getValue($row, 'grandparent_consumption.total');
					$rows[] = ArrayHelper::getValue($row, 'parent_name');
					$rows[] = ArrayHelper::getValue($row, 'parent_consumption.total');
					$rows[] = ArrayHelper::getValue($row, 'tenants');
					$rows[] = ArrayHelper::getValue($row, 'children_name');
					$rows[] = ArrayHelper::getValue($row, 'children_consumption.total');

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

					$r++;
				}

				/* Totals */

				$totals_kwh = [
					null,
					null,
					null,
					null,
					null,
					Yii::t('common.view', 'Total Kwh of all tenants'),
					ArrayHelper::getValue($params['data'], "totals.$index.children_consumption.total", 0),
				];

				foreach ($totals_kwh as $c => $total) {
					$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $total);
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

				$totals_difference = [
					null,
					null,
					null,
					null,
					null,
					Yii::t('common.view', 'Difference between parent and total of all tenants'),
					ArrayHelper::getValue($params['data'], "totals.$index.parent_consumption.total", 0) - ArrayHelper::getValue($params['data'], "totals.$index.children_consumption.total", 0),
				];

				foreach ($totals_difference as $c => $total) {
					$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $total);
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

			$r++;

			/* Total table */

			$columns_total = [
				Yii::t('common.view', 'Meter ID'),
				Yii::t('common.view', 'Pisga'),
				Yii::t('common.view', 'Geva'),
				Yii::t('common.view', 'Shefel'),
				Yii::t('common.view', 'Total Kwh'),
			];

			foreach ($columns_total as $c => $column) {
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

			foreach($params['data']['totals'] as $index => $totals) {
				$rows = [];
				$rows[] = ArrayHelper::getValue($totals, 'parent_name');
				$rows[] = ArrayHelper::getValue($totals, 'parent_consumption.pisga', 0);
				$rows[] = ArrayHelper::getValue($totals, 'parent_consumption.geva', 0);
				$rows[] = ArrayHelper::getValue($totals, 'parent_consumption.shefel', 0);
				$rows[] = ArrayHelper::getValue($totals, 'parent_consumption.total', 0);

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

				$r++;
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