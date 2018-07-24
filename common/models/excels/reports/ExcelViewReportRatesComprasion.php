<?php

namespace common\models\excels\reports;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\Tenant;
use common\components\data\ExcelView;
use common\models\helpers\reports\ReportGeneratorRatesComprasion;

/**
 * ExcelViewReportRatesComprasion is the class for view report rates comprasion excel.
 */
class ExcelViewReportRatesComprasion extends ExcelView
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

		if (!empty($params['rules'])) {
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
					'name' => Yii::t('common.view', 'Comparison report for'),
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

				$objPHPExcelActiveSheet->setCellValue(self::columnName(8) . $r, $value['value']);
				$objPHPExcelActiveSheet->getStyle(self::columnName(8) . $r)->applyFromArray([
					'font' => [
						'size' => 10,
					],
				]);
				$objPHPExcelActiveSheet->getColumnDimension(self::columnName(8))->setAutoSize(true);

				$r++;
			}

			$r++;

			/* Columns */

			$columns = [
				Yii::t('common.view', 'Row number'),
				Yii::t('common.view', 'Tenant ID'),
				Yii::t('common.view', 'To issue'),
				Yii::t('common.view', 'Tenant name'),
				Yii::t('common.view', 'Meter ID / Group Name'),
				Yii::t('common.view', 'High rate'),
				Yii::t('common.view', 'Low rate'),
				Yii::t('common.view', 'Diff in NIS'),
				Yii::t('common.view', 'Diff in %'),
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

			/* Rows */

			$index = 0;
			foreach($params['rules'] as $rule) {
				$rows = [];
				$rows[] = ($index + 1);
				$index++;
				$rows[] = $rule['tenant_id'];

				switch ($rule['model_tenant']->to_issue) {
					case Site::TO_ISSUE_AUTOMATIC:
					case Site::TO_ISSUE_MANUAL:
						$rows[] = Yii::t('common.view', 'To issue');
						break;
					
					default:
						$rows[] = Yii::t('common.view', 'Not to issue');
						break;
				}

				$tenant_name = $rule['tenant_name'];
				if ($entrance_date = $rule['model_tenant']->getEntranceDateReport($report->from_date, $report->to_date)) {
					$tenant_name .= ' ' .Yii::t('common.view', 'Entry date: {date}', [
						'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
					]);
				}
				if ($exit_date = $rule['model_tenant']->getExitDateReport($report->from_date, $report->to_date)) {
					$tenant_name .= ' ' .Yii::t('common.view', 'Exit date: {date}', [
						'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
					]);
				}
				
				$rows[] = $tenant_name;
				$rows[] = implode(', ', $rule['rule_name']);
				$rows[] = Yii::$app->formatter->asRound($rule['total_pay_high']);
				$rows[] = Yii::$app->formatter->asRound($rule['total_pay_low']);
				$rows[] = Yii::$app->formatter->asRound($rule['total_pay_low'] - $rule['total_pay_high']);
				$rows[] = Yii::$app->formatter->asPercentage(($rule['total_pay_low']) ? (100 - ($rule['total_pay_high'] * 100) / $rule['total_pay_low']) : $rule['total_pay_low']);

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

			$totals_without_vat = [
				null,
				null,
				null,
				Yii::t('common.view', 'Total'),
				null,
				Yii::$app->formatter->asRound($params['total_pay_high']),
				Yii::$app->formatter->asRound($params['total_pay_low']),
				Yii::$app->formatter->asRound($params['total_pay_diff']),
				null,
			];

			foreach ($totals_without_vat as $c => $total) {
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

			$totals_with_vat = [
				null,
				null,
				null,
				Yii::t('common.view', 'Total (including VAT)'),
				null,
				Yii::$app->formatter->asRound($params['total_pay_high'] + $params['vat_pay_high']),
				Yii::$app->formatter->asRound($params['total_pay_low'] + $params['vat_pay_low']),
				Yii::$app->formatter->asRound(($params['total_pay_low'] + $params['vat_pay_low']) - ($params['total_pay_high'] + $params['vat_pay_high'])),
				null,
			];

			foreach ($totals_with_vat as $c => $total) {
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

			/* Additional parameters */

			if (!empty($additional_parameters)) {
				$additional_params = [
					[
						null,
						null,
						null,
						Yii::t('common.view', 'Electric company'),
						null,
						null,
						(isset($additional_parameters['electric_company_rate_low'])) ? Yii::$app->formatter->asRound($additional_parameters['electric_company_rate_low']) : null,
						null,
						null,
					],
					[
						null,
						null,
						null,
						Yii::t('common.view', 'Diff in NIS'),
						null,
						null,
						(isset($additional_parameters['electric_company_rate_low'])) ? Yii::$app->formatter->asRound($additional_parameters['electric_company_rate_low'] - ($params['total_pay_low'] + $params['vat_pay_low'])) : null,
						null,
						null,
					],
				];

				foreach ($additional_params as $additional_parameter) {
					foreach ($additional_parameter as $c => $additional_parameter_values) {
						$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $additional_parameter_values);
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