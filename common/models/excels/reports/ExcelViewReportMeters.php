<?php

namespace common\models\excels\reports;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

use common\models\Tenant;
use common\components\data\ExcelView;
use common\models\helpers\reports\ReportGeneratorMeters;

/**
 * ExcelViewReportMeters is the class for view report meters excel.
 */
class ExcelViewReportMeters extends ExcelView
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
				$site->name,
				Yii::t('common.view', 'List of meters on site'),
				Yii::t('common.view', 'Date of report: {date}', [
					'date' => Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'),
				]),
			];

			foreach ($head as $c => $value) {
				$objPHPExcelActiveSheet->setCellValue(self::columnName(1) . $r, $value);
				$objPHPExcelActiveSheet->getStyle(self::columnName(1) . $r)->applyFromArray([
					'font' => [
						'size' => 10,
					],
				]);
				$objPHPExcelActiveSheet->getColumnDimension(self::columnName(1))->setAutoSize(true);

				$r++;
			}

			$r++;

			/* Columns */

			$columns = [
				Yii::t('common.view', 'Row number'),
				Yii::t('common.view', 'Meter ID'),
				Yii::t('common.view', 'Current multiplier'),
				Yii::t('common.view', 'Voltage multiplier'),
				Yii::t('common.view', 'Tenant name'),
				Yii::t('common.view', 'Breaker name'),
				Yii::t('common.view', 'To issue'),
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
			$index = 1;
			
			foreach ($params['data'] as $row) {
				/* Rows */

				$rows = [];
				$rows[] = $index;
				$index++;
				$rows[] = $row['rule_name'];
				$rows[] = $row['current_multiplier'];
				$rows[] = $row['voltage_multiplier'];
				
				$tenant_name = $row['tenant']->name;

				if ($entrance_date = $row['tenant']->getEntranceDateReport($report->from_date, $report->to_date)) {
					$tenant_name .= ' ' .Yii::t('common.view', 'Entry date: {date}', [
						'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
					]);
				}
				if ($exit_date = $row['tenant']->getExitDateReport($report->from_date, $report->to_date)) {
					$tenant_name .= ' ' .Yii::t('common.view', 'Exit date: {date}', [
						'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
					]);
				}
				
				$rows[] = $tenant_name;

				$rows[] = $row['meter']->breaker_name;
				$rows[] = ($row['to_issue']) ? Yii::t('common.view', 'Yes to issue') : Yii::t('common.view', 'Not to issue');
				
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