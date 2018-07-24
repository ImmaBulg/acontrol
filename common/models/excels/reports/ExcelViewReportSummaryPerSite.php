<?php

namespace common\models\excels\reports;

use common\helpers\CalculationHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\Rate;
use common\models\Tenant;
use common\components\data\ExcelView;
use common\models\helpers\reports\ReportGeneratorSummaryPerSite;

/**
 * ExcelViewReportSummaryPerSite is the class for view report suppary per site excel.
 */
class ExcelViewReportSummaryPerSite extends ExcelView
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
					'name' => Yii::t('common.view', 'Tenants readings summary report'),
					'value' => Yii::t('common.view', 'Issue date'). ': ' .Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'),
				],
				[
					'name' => Yii::t('common.view', 'To'),
					'value' => Yii::t('common.view', 'Current reading date'). ': ' .Yii::$app->formatter->asDate($report->to_date, 'dd/MM/yy'),
				],
				[
					'name' => $site->name,
					'value' => Yii::t('common.view', 'Previous reading date'). ': ' .Yii::$app->formatter->asDate($report->from_date, 'dd/MM/yy'),
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

				$objPHPExcelActiveSheet->setCellValue(self::columnName(14) . $r, $value['value']);
				$objPHPExcelActiveSheet->getStyle(self::columnName(14) . $r)->applyFromArray([
					'font' => [
						'size' => 10,
					],
				]);
				$objPHPExcelActiveSheet->getColumnDimension(self::columnName(14))->setAutoSize(true);

				$r++;
			}

			$r++;

			/* Columns */

			$columns = [
				Yii::t('common.view', 'Row number'),
				Yii::t('common.view', 'Tenant ID'),
				Yii::t('common.view', 'To Issue'),
				Yii::t('common.view', 'Tenant name'),
				null,
				Yii::t('common.view', 'Previous reading'),
				Yii::t('common.view', 'Current reading'),
				Yii::t('common.view', 'Consumption (Kwh)'),
				Yii::t('common.view', 'Relative load in Kwh'),
				Yii::t('common.view', 'Additions in Kwh'),
				Yii::t('common.view', 'Total consumption'),
				Yii::t('common.view', 'Total to pay'),
				Yii::t('common.view', 'Money addition'),
				Yii::t('common.view', 'Fixed payment'),
				Yii::t('common.view', 'Total to pay'),
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

			$name_single = ReportGeneratorSummaryPerSite::RULE_SINGLE_CHANNEL;
			$name_group = ReportGeneratorSummaryPerSite::RULE_GROUP_LOAD;
			$name_fixed = ReportGeneratorSummaryPerSite::RULE_FIXED_LOAD;

			$index = 0;
			$total_shefel_single_diff = 0;
			$total_shefel_group_diff = 0;
			$total_shefel_fixed_diff = 0;
			$total_shefel_pay = 0;

			$total_geva_single_diff = 0;
			$total_geva_group_diff = 0;
			$total_geva_fixed_diff = 0;
			$total_geva_pay = 0;

			$total_pisga_single_diff = 0;
			$total_pisga_group_diff = 0;
			$total_pisga_fixed_diff = 0;
			$total_pisga_pay = 0;

			$total_fixed_single_diff = 0;
			$total_fixed_group_diff = 0;
			$total_fixed_fixed_diff = 0;
			$total_fixed_pay = 0;

			$fixed_pay = 0;
			$fixed_payment = 0;


			foreach ($params['data'] as $rates)
            {

                /**
                 * REPLACE fixed_payment
                 * on priority `So Rate->Tenant->Site in order of priority`
                 */
                foreach ($rates as $rate_id => $rate)
                {
                    if (is_array($rate))
                    {
                        foreach($rate as $tenant_id => $tenant)
                        {

                            if (CalculationHelper::isCorrectFixedPayment($tenant['fixed_payment'])) // tenant
                                $rates[$rate_id][$tenant_id]['fixed_payment'] = $tenant['fixed_payment'];
                            elseif (CalculationHelper::isCorrectFixedPayment($site->relationSiteBillingSetting['fixed_payment'])) // site
                                $rates[$rate_id][$tenant_id]['fixed_payment'] = $site->relationSiteBillingSetting['fixed_payment'];
                            elseif (CalculationHelper::isCorrectFixedPayment($additional_parameters['rates_fixed_payments'])) // rates
                                 $rates[$rate_id][$tenant_id]['fixed_payment'] = $additional_parameters['rates_fixed_payments'];
                        }
                    }
                }
                /* end replace */


				foreach ($rates as $rate_id => $rate)
                {
					$model_rate = Rate::findOne($rate_id);
					$objPHPExcelActiveSheet->setCellValue(self::columnName(1) . $r, Yii::t('common.view', 'Rate type'). ' ' .$model_rate->getAliasRateType());
					$objPHPExcelActiveSheet->getStyle(self::columnName(1) . $r)->applyFromArray([
						'font' => [
							'size' => 10,
						],
						'borders' => [
							'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
						],
					]);
					$objPHPExcelActiveSheet->mergeCells(self::columnName(1).$r. ':' .self::columnName(14).$r);
					$objPHPExcelActiveSheet->getColumnDimension(self::columnName(1))->setAutoSize(true);

					$r++;

					foreach($rate as $tenant_id => $tenant)
                    {
						$total_shefel_single_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.shefel.reading_diff", 0));
						$total_shefel_group_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_group.shefel.reading_diff", 0));
						$total_shefel_fixed_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_fixed.shefel.reading_diff", 0));
						$total_shefel_pay += ArrayHelper::getValue($tenant, "shefel.total_pay", 0);

						$total_geva_single_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.geva.reading_diff", 0));
						$total_geva_group_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_group.geva.reading_diff", 0));
						$total_geva_fixed_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_fixed.geva.reading_diff", 0));
						$total_geva_pay += ArrayHelper::getValue($tenant, "geva.total_pay", 0);

						$total_pisga_single_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.pisga.reading_diff", 0));
						$total_pisga_group_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_group.pisga.reading_diff", 0));
						$total_pisga_fixed_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_fixed.pisga.reading_diff", 0));
						$total_pisga_pay += ArrayHelper::getValue($tenant, "pisga.total_pay", 0);

						$total_fixed_single_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.fixed.reading_diff", 0));
						$total_fixed_group_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_group.fixed.reading_diff", 0));
						$total_fixed_fixed_diff += Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_fixed.fixed.reading_diff", 0));
						$total_fixed_pay += ArrayHelper::getValue($tenant, "fixed.total_pay", 0);

						$fixed_pay += ArrayHelper::getValue($tenant, "fixed_pay", 0);
						$fixed_payment += ArrayHelper::getValue($tenant, "fixed_payment", 0);

						/* Rows */

						$rows = [];
						$index++;
						$rows[] = $index;
						$rows[] = $tenant['tenant']->id;
						
						switch ($tenant['tenant']->to_issue) {
							case Site::TO_ISSUE_AUTOMATIC:
							case Site::TO_ISSUE_MANUAL:
								$rows[] = Yii::t('common.view', 'To issue');
								break;
							
							default:
								$rows[] = Yii::t('common.view', 'Not to issue');
								break;
						}

						$tenant_name = $tenant['tenant_name'];
						if ($entrance_date = $tenant['tenant']->getEntranceDateReport($report->from_date, $report->to_date)) {
							$tenant_name .= ' ' .Yii::t('common.view', 'Entry date: {date}', [
								'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
							]);
						}
						if ($exit_date = $tenant['tenant']->getExitDateReport($report->from_date, $report->to_date)) {
							$tenant_name .= ' ' .Yii::t('common.view', 'Exit date: {date}', [
								'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
							]);
						}
						
						$rows[] = $tenant_name;
						$rows[] = Yii::t('common.view', 'Pisga');
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.pisga.reading_from", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.pisga.reading_to", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.pisga.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_group.pisga.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_fixed.pisga.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "pisga.total_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "pisga.total_pay", 0));
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;

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

						$rows = [];
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;
						$rows[] = Yii::t('common.view', 'Geva');
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.geva.reading_from", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.geva.reading_to", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.geva.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_group.geva.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_fixed.geva.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "geva.total_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "geva.total_pay", 0));
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;

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

						$rows = [];
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;
						$rows[] = Yii::t('common.view', 'Shefel');
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.shefel.reading_from", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.shefel.reading_to", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.shefel.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_group.shefel.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_fixed.shefel.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "shefel.total_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "shefel.total_pay", 0));
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;

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

						$rows = [];
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;
						$rows[] = null;
						$rows[] = Yii::t('common.view', 'Total');
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_single.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_group.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "rules.$name_fixed.reading_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "total_diff", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "total_pay", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "fixed_pay", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "fixed_payment", 0));
						$rows[] = Yii::$app->formatter->asRound(ArrayHelper::getValue($tenant, "total_pay", 0) + ArrayHelper::getValue($tenant, "fixed_pay", 0) + ArrayHelper::getValue($tenant, "fixed_payment", 0));

						foreach ($rows as $c => $row) {
							$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $row);
							$objPHPExcelActiveSheet->getStyle(self::columnName($c + 1) . $r)->applyFromArray([
								'font' => [
									'size' => 10,
									'bold' => true,
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
			}

			$rows = [];
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = Yii::t('common.view', 'Pisga');
			$rows[] = Yii::$app->formatter->asRound($total_pisga_single_diff);
			$rows[] = Yii::$app->formatter->asRound($total_pisga_group_diff);
			$rows[] = Yii::$app->formatter->asRound($total_pisga_fixed_diff);
			$rows[] = null;
			$rows[] = Yii::$app->formatter->asRound($total_pisga_pay);
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;

			foreach ($rows as $c => $row) {
				$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $row);
				$objPHPExcelActiveSheet->getStyle(self::columnName($c + 1) . $r)->applyFromArray([
					'font' => [
						'size' => 10,
						'bold' => true,
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

			$rows = [];
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = Yii::t('common.view', 'Geva');
			$rows[] = Yii::$app->formatter->asRound($total_geva_single_diff);
			$rows[] = Yii::$app->formatter->asRound($total_geva_group_diff);
			$rows[] = Yii::$app->formatter->asRound($total_geva_fixed_diff);
			$rows[] = null;
			$rows[] = Yii::$app->formatter->asRound($total_geva_pay);
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;

			foreach ($rows as $c => $row) {
				$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $row);
				$objPHPExcelActiveSheet->getStyle(self::columnName($c + 1) . $r)->applyFromArray([
					'font' => [
						'size' => 10,
						'bold' => true,
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

			$rows = [];
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = Yii::t('common.view', 'Shefel');
			$rows[] = Yii::$app->formatter->asRound($total_shefel_single_diff);
			$rows[] = Yii::$app->formatter->asRound($total_shefel_group_diff);
			$rows[] = Yii::$app->formatter->asRound($total_shefel_fixed_diff);
			$rows[] = null;
			$rows[] = Yii::$app->formatter->asRound($total_shefel_pay);
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;

			foreach ($rows as $c => $row) {
				$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $row);
				$objPHPExcelActiveSheet->getStyle(self::columnName($c + 1) . $r)->applyFromArray([
					'font' => [
						'size' => 10,
						'bold' => true,
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

			$rows = [];
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = null;
			$rows[] = Yii::t('common.view', 'Total');
			$rows[] = Yii::$app->formatter->asRound($total_shefel_single_diff + $total_geva_single_diff + $total_pisga_single_diff);
			$rows[] = Yii::$app->formatter->asRound($total_shefel_group_diff + $total_geva_group_diff + $total_pisga_group_diff);
			$rows[] = Yii::$app->formatter->asRound($total_shefel_fixed_diff + $total_geva_fixed_diff + $total_pisga_fixed_diff + $total_fixed_fixed_diff);
			$rows[] = null;
			$rows[] = Yii::$app->formatter->asRound($total_shefel_pay + $total_geva_pay + $total_pisga_pay + $total_fixed_pay);
			$rows[] = null;
			$rows[] = null;
			$rows[] = Yii::$app->formatter->asRound($total_shefel_pay + $total_geva_pay + $total_pisga_pay + $total_fixed_pay + $fixed_pay + $fixed_payment);

			foreach ($rows as $c => $row) {
				$objPHPExcelActiveSheet->setCellValue(self::columnName($c + 1) . $r, $row);
				$objPHPExcelActiveSheet->getStyle(self::columnName($c + 1) . $r)->applyFromArray([
					'font' => [
						'size' => 10,
						'bold' => true,
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