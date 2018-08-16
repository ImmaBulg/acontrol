<?php

namespace common\models\excels\reports;

use Carbon\Carbon;
use common\components\calculators\data\RuleData;
use common\components\i18n\LanguageSelector;
use common\helpers\CalculationHelper;
use common\models\RateName;
use common\models\Site;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\BadRequestHttpException;

use common\models\Tenant;
use common\models\Meter;
use common\models\RateType;
use common\models\RuleFixedLoad;
use common\components\data\ExcelView;
use common\models\helpers\reports\ReportGeneratorTenantBills;

/**
 * ExcelViewReportTenantBills is the class for view report tenant bills excel.
 */
class ExcelViewReportTenantBills extends ExcelView
{	
	/**
	 * @inheritdoc
	 */

	public function setObjPHPExcelAttribute()
    {
        $objPHPExcel = $this->getObjPHPExcel();
        $objPHPExcelActiveSheet = $objPHPExcel->getActiveSheet();
        $params = $this->getParams();
        $data = $params['data'];
        //VarDumper::dump($data, 100, true);
        $direction = LanguageSelector::getAliasLanguageDirection();
        $first_tenant_key = key($data->getTenantData());

        //Print head img
        $r = 1;
        $objDrawing = new \PHPExcel_Worksheet_Drawing();
        $objDrawing->setName(Yii::$app->name);
        $objDrawing->setDescription(Yii::$app->name);
        $objDrawing->setPath(Yii::getAlias('@backend/web/images/pdf/report/horizontal-header.png'));
        $objDrawing->setResizeProportional(false);
        $objDrawing->setWidth(600);
        $objDrawing->setCoordinates(self::columnName(1) . $r);
        $objDrawing->setWorksheet($objPHPExcelActiveSheet);
        $r += 7;

        //Print table
        foreach ($data->getTenantData() as $index => $tenant_data)
        {
            //Print head
            if ($index != $first_tenant_key) $r += 5;

            $tenant_name = $tenant_data->getTenant()->name;
            $site_name = $params['data']->getSite()->name;
            $exit_date = $tenant_data->getTenant()->getExitDateReport($params['data']->getEndDate());
            $entrance_date = $tenant_data->getTenant()->getEntranceDateReport($params['data']->getStartDate());

            $head = [
                [
                    'name' => Yii::t('common.view', 'Tenant name'). ': ' . $tenant_name,
                    'value' => Yii::t('common.view', 'Issue date'). ': ' . Yii::$app->formatter->asDate(Carbon::now(), 'dd/MM/yy'),
                ],
                [
                    'name' => Yii::t('common.view', 'Site name'). ': ' . $site_name,
                    'value' => Yii::t('common.view', 'Current meter reading'). ': ' . Yii::$app->formatter->asDate($tenant_data->getEndDate(), 'dd/MM/yy'),
                ],
                [
                    'name' => null,
                    'value' => Yii::t('common.view', 'Previous meter reading'). ': ' .Yii::$app->formatter->asDate($tenant_data->getStartDate(), 'dd/MM/yy'),
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

            //Print table name
            $objPHPExcelActiveSheet->setCellValue(self::columnName(1) . $r, Yii::t('common.view', 'Dear customer, here is the billing report for the period of'). ' ' . Yii::$app->formatter->asDate($tenant_data->getEndDate(), 'dd/MM/yy'). ' - ' .Yii::$app->formatter->asDate($tenant_data->getStartDate(), 'dd/MM/yy'));
            $objPHPExcelActiveSheet->getStyle(self::columnName(1) . $r)->applyFromArray([
                'font' => [
                    'size' => 10,
                    'bold' => true,
                ],
            ]);
            $objPHPExcelActiveSheet->mergeCells(self::columnName(1).$r. ':' .self::columnName(7).$r);
            $objPHPExcelActiveSheet->getColumnDimension(self::columnName(1))->setAutoSize(true);
            $r += 2;

            //print table
            foreach($tenant_data->getRuleData() as $rule_data)
            {
                $objPHPExcelActiveSheet->setCellValue(self::columnName(1) . $r, implode(' - ', array_filter([
                    $rule_data->getRule()->name,
                    $rule_data->getRule()->getMeterName() . ' - ' .$rule_data->getRule()->getChannelName(),
                ])));
                $objPHPExcelActiveSheet->getStyle(self::columnName(1) . $r)->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'bold' => true,
                    ],
                ]);
                $objPHPExcelActiveSheet->mergeCells(self::columnName(1).$r. ':' .self::columnName(7).$r);
                $objPHPExcelActiveSheet->getColumnDimension(self::columnName(1))->setAutoSize(true);

                $r++;

                //table
                foreach ($rule_data->getData() as $type => $dt)
                {
                    if (!empty($dt))
                    {
                                                if ($type == 'irregular_data') {
                            if ($dt[0]->getMultipliedData()[0]->getPisgaConsumption() == 0
                                and
                                $dt[0]->getMultipliedData()[0]->getGevaConsumption() == 0
                                and
                                $dt[0]->getMultipliedData()[0]->getShefelConsumption() == 0) {
                                // Skip empty Irregular hours
                                continue;
                            }
                        }


                        foreach ($dt as $data_block)
                        {
                            foreach ($data_block->getMultipliedData() as $multipliedData)
                            {
                                $objPHPExcelActiveSheet->setCellValue(self::columnName(1) . $r, Yii::t('common.view', RuleData::getDataLabel($type)) . ' ' . $rule_data->getTimeRange($type));
                                $objPHPExcelActiveSheet->getStyle(self::columnName(1) . $r)->applyFromArray([
                                    'font' => [
                                        'size' => 10,
                                        'bold' => true,
                                    ],
                                ]);
                                $objPHPExcelActiveSheet->mergeCells(self::columnName(1).$r. ':' .self::columnName(2).$r);
                                $objPHPExcelActiveSheet->getColumnDimension(self::columnName(1))->setAutoSize(true);
                                $r++;
                                //columns
                                $columns = [];
                                $columns[] = Yii::t('common.view', 'Rate type');
                                $columns[] = Yii::t('common.view', 'Previous reading');
                                $columns[] = Yii::t('common.view', 'Current reading');
                                $columns[] = Yii::t('common.view', 'Consumption type');
                                $columns[] = Yii::t('common.view', 'Total air consumption');
                                $columns[] = Yii::t('common.view', 'Total consumption in Kwh');
                                $columns[] = Yii::t('common.view', 'Price per 1 Kwh in Agorot');
                                $columns[] = Yii::t('common.view', 'Total to pay');
                                $columns[] = Yii::t('common.view', 'COP');

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

                                $rows = [];
                                $rows[] = Yii::t('common.view', 'Bill details for dates');
                                $rows[] = Yii::$app->formatter->asDate($multipliedData->getStartDate(), 'dd/MM/yy');
                                $rows[] = Yii::$app->formatter->asDate($multipliedData->getEndDate(), 'dd/MM/yy');
                                $rows[] = null;
                                $rows[] = null;
                                $rows[] = null;
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
                                $rate_name = RateName::find()->where(['name' => $data_block->getRate()->rate_name])->one();
                                if ($rate_name->is_taoz)
                                {
                                    $rows[] = null;
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getReadingFrom());
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getReadingTo());
                                    $rows[] = Yii::t('common.view', 'Pisga');
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getAirPisgaConsumption());
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getPisgaConsumption());
                                    $rows[] = Yii::$app->formatter->asRound($data_block->getPisgaPrice());
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getPisgaPay());
                                    $rows[] = Yii::$app->formatter->asRound($rule_data->cop_pisga);

                                    foreach ($rows as $c => $row) {
                                        if ($c == 1 || $c == 2)
                                            $objPHPExcelActiveSheet->mergeCells(self::columnName($c + 1) . $r . ':' . self::columnName($c + 1) . ($r + 2));
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
                                    $rows[] = Yii::t('common.view', 'Geva');
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getAirGevaConsumption());
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getGevaConsumption());
                                    $rows[] = Yii::$app->formatter->asRound($data_block->getGevaPrice());
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getGevaPay());
                                    $rows[] = Yii::$app->formatter->asRound($rule_data->cop_geva);

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
                                    $rows[] = Yii::t('common.view', 'Shefel');
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getAirShefelConsumption());
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getShefelConsumption());
                                    $rows[] = Yii::$app->formatter->asRound($data_block->getShefelPrice());
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getShefelPay());
                                    $rows[] = Yii::$app->formatter->asRound($rule_data->cop_shefel);

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
                                    $rows[] = Yii::t('common.view', 'Total');
                                    $rows[] = null;
                                    $rows[] = null;
                                    $rows[] = null;
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getAirPisgaConsumption() + $multipliedData->getAirGevaConsumption() + $multipliedData->getAirShefelConsumption());
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getPisgaConsumption() + $multipliedData->getGevaConsumption() + $multipliedData->getShefelConsumption());
                                    $rows[] = null;
                                    $rows[] = Yii::$app->formatter->asRound($multipliedData->getPisgaPay() + $multipliedData->getGevaPay() + $multipliedData->getShefelPay());

                                    foreach ($rows as $c => $row) {
                                        if ($c == 0)
                                            $objPHPExcelActiveSheet->mergeCells(self::columnName($c + 1) . $r . ':' . self::columnName($c + 4) . $r);
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
                            }
                        }
                    }
                }
            }

            //total
            $r++;
            $objPHPExcelActiveSheet->setCellValue(self::columnName(1) . $r, Yii::t('common.view', 'Total consumption for tenant'));
            $objPHPExcelActiveSheet->getStyle(self::columnName(1) . $r)->applyFromArray([
                'font' => [
                    'size' => 10,
                    'bold' => true,
                ],
            ]);
            $objPHPExcelActiveSheet->mergeCells(self::columnName(1).$r. ':' .self::columnName(7).$r);
            $objPHPExcelActiveSheet->getColumnDimension(self::columnName(1))->setAutoSize(true);
            $r++;

            //print total table head
            $columns = [];
            $columns[] = null;
            $columns[] = Yii::t('common.view', 'Consumption pisga');
            $columns[] = Yii::t('common.view', 'Consumption geva');
            $columns[] = Yii::t('common.view', 'Consumption shefel');
            $columns[] = Yii::t('common.view', 'Total consumption in Kwh');
            $columns[] = Yii::t('common.view', 'Total (without VAT)');

            //print total table row
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

            $rows = [];
            $rows[] = null;
            $rows[] = Yii::$app->formatter->asRound($tenant_data->getAirPisgaConsumption());
            $rows[] = Yii::$app->formatter->asRound($tenant_data->getAirGevaConsumption());
            $rows[] = Yii::$app->formatter->asRound($tenant_data->getAirShefelConsumption());
            $rows[] = Yii::$app->formatter->asRound($tenant_data->getTotalConsumption());
            $rows[] = Yii::$app->formatter->asRound($tenant_data->getTotalPay());

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
            $r+=2;

            //print fixed payment table
            $rows = [];
            $rows[] = null;
            $rows[] = Yii::t('common.view', 'Fixed payment');
            $rows[] = Yii::$app->formatter->asRound($tenant_data->getFixedPrice());
            foreach ($rows as $c => $row) {
                if ($c == 0)
                    $objPHPExcelActiveSheet->mergeCells(self::columnName($c + 1) . $r . ':' . self::columnName($c + 1) . ($r + 1));
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
            $rows[] = Yii::t('common.view', 'VAT 17%');
            $rows[] = Yii::$app->formatter->asRound($tenant_data->getVat());
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
            $rows[] = Yii::t('common.view', 'Includes VAT');
            $rows[] = Yii::t('common.view', 'Total to pay');
            $rows[] = Yii::$app->formatter->asRound($tenant_data->getTotalPayWithVat());
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
            $r+=4;


        }

        //Print footer img
        $objDrawing = new \PHPExcel_Worksheet_Drawing();
        $objDrawing->setName(Yii::$app->name);
        $objDrawing->setDescription(Yii::$app->name);
        $objDrawing->setPath(Yii::getAlias('@backend/web/images/pdf/report/horizontal-footer.png'));
        $objDrawing->setResizeProportional(false);
        $objDrawing->setWidth(600);
        $objDrawing->setCoordinates(self::columnName(1) . $r);
        $objDrawing->setWorksheet($objPHPExcelActiveSheet);
    }
}