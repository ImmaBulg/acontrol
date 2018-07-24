<?php

require_once(Yii::getAlias('@common/components/chart/pchart/class/pData.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pDraw.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pPie.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pImage.class.php'));
use common\helpers\CalculationHelper;
use yii\helpers\ArrayHelper;

use common\models\Tenant;
use common\helpers\Html;
use common\components\i18n\LanguageSelector;
use common\models\helpers\reports\ReportGeneratorKwhPerSite;

$direction = LanguageSelector::getAliasLanguageDirection();
?>
<?php if(!empty($data)): ?>
	<?php $first_tenant = key($data); ?>
	<?php foreach($data as $index => $tenant): ?>
		<?php if($index != $first_tenant): ?>
			<pagebreak />
		<?php endif; ?>
		<table dir="<?php echo $direction; ?>" style="width:100%;font-size:12px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td style="padding:5px;width:60%">
						<?php echo Yii::t('common.view', 'To'); ?>:
					</td>
					<td style="padding:5px;">
						<?php echo $site_owner->name; ?>
					</td>
				</tr>
				<tr>
					<td style="padding:5px;width:60%">
						<?php echo Yii::t('common.view', 'Yearly summary report for tenant'); ?>:
					</td>
					<td style="padding:5px;">
						<?php echo $tenant['model_tenant']->name; ?>
						<p>
							<?php if ($entrance_date = $tenant['model_tenant']->getEntranceDateReport($report->from_date, $report->to_date)): ?>
									<?php echo Yii::t('common.view', 'Entry date: {date}', [
										'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
									]); ?>
							<?php endif; ?>

							<?php if ($exit_date = $tenant['model_tenant']->getExitDateReport($report->from_date, $report->to_date)): ?>
									<?php echo Yii::t('common.view', 'Exit date: {date}', [
										'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
									]); ?>
							<?php endif; ?>
						</p>
					</td>
				</tr>
				<tr>
					<td style="padding:5px;width:60%">
						<?php echo Yii::t('common.view', 'Report range'); ?>: (<?php echo Yii::$app->formatter->asDate($report->from_date, 'dd/MM/yy'); ?> - <?php echo Yii::$app->formatter->asDate($report->to_date, 'dd/MM/yy'); ?>)
					</td>
					<td style="padding:5px;">
						<?php echo Yii::t('common.view', 'Issue date'); ?>: <?php echo Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'); ?>
					</td>
				</tr>
			</tbody>
		</table>


		<?php if(!empty($tenant['data'])): ?>
				<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
					<thead>
						<tr bgcolor="#7e7e7e">
							<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Month'); ?>
							</th>
							<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Pisga Kwh'); ?>
							</th>
							<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Geva Kwh'); ?>
							</th>
							<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Shefel Kwh'); ?>
							</th>
							<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Loads Kwh'); ?>
							</th>
							<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Extras Kwh'); ?>
							</th>
							<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Total Kwh'); ?>
							</th>
							<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Extras NIS'); ?>
							</th>

							<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Fixed payment'); ?>
							</th>
							<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Total NIS'); ?>
							</th>
                            <th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
                                <?php echo Yii::t('common.view', 'Max demand'); ?>
                            </th>
						</tr>
					</thead>
					<tbody>
						<?php
							$graphKwhAxisLabel = [];
							$graphKwhDataSetPisga = [];
							$graphKwhDataSetGeva = [];
							$graphKwhDataSetShefel = [];

							$graphNisAxisLabel = [];
							$graphNisDataSetPisga = [];
							$graphNisDataSetGeva = [];
							$graphNisDataSetShefel = [];

                            $_total_fixed_payment = 0;
						?>
						<?php foreach($tenant['data'] as $date => $row): ?>
							<?php
								$graphKwhAxisLabel[] = Yii::t('common.graph', Yii::$app->formatter->asDate($date, 'MMM'));
								$graphKwhDataSetPisga[] = $row['pisga'];
								$graphKwhDataSetGeva[] = $row['geva'];
								$graphKwhDataSetShefel[] = $row['shefel'];

								$graphNisAxisLabel[] = Yii::t('common.graph', Yii::$app->formatter->asDate($date, 'MMM'));
								$graphNisDataSetPisga[] = $row['pisga_pay'];
								$graphNisDataSetGeva[] = $row['geva_pay'];
								$graphNisDataSetShefel[] = $row['shefel_pay'];
							?>
							<tr>
								<td style="padding:5px;border:1px solid #000;">
									<?php echo Yii::t('common.common', Yii::$app->formatter->asDate($date, 'MMMM')); ?>
								</td>
								<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
									<?php echo Yii::$app->formatter->asNumberFormat($row['pisga_consumption']); ?>
								</td>
								<td style="padding:5px;border:1px solid #000;" align="center">
									<?php echo Yii::$app->formatter->asNumberFormat($row['geva_consumption']); ?>
								</td>
								<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
									<?php echo Yii::$app->formatter->asNumberFormat($row['shefel_consumption']); ?>
								</td>
								<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
									<?php echo Yii::$app->formatter->asNumberFormat($row['total_loads_consumption']); ?>
								</td>
								<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
									<?php echo Yii::$app->formatter->asNumberFormat($row['total_extras_consumption']); ?>
								</td>
								<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
									<?php echo Yii::$app->formatter->asNumberFormat($row['total_consumption']); ?>
								</td>
								<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
									<?php echo Yii::$app->formatter->asNumberFormat($row['total_extras_pay']); ?>
								</td>

                                <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">

                                    <?php
                                        /**
                                         * REPLACE fixed_payment
                                         *
                                         * show on priority `So Rate->Tenant->Site in order of priority`
                                         * if delete this block - than below line with tag `#uncomment`
                                         */
                                        // todo: refine the logic this report
                                        //

                                        //tenant is by default
                                        if (CalculationHelper::isCorrectFixedPayment($site->relationSiteBillingSetting['fixed_payment'])) // site
                                            $row['fixed_payment'] = $site->relationSiteBillingSetting['fixed_payment'];
                                        elseif (CalculationHelper::isCorrectFixedPayment($additional_parameters['rates_fixed_payments_all'])) // rates
                                        {
                                            foreach ($additional_parameters['rates_fixed_payments_all'] as $value)
                                            {
                                                $m = Yii::$app->formatter->asDate($value['start_date'], 'MM') * 1;
                                                $m2 = Yii::$app->formatter->asDate($date, 'MM') * 1;
                                                if ($m == $m2)
                                                    // REPLACE
                                                    $row['fixed_payment'] = $value['fixed_payment'];
                                                else
                                                    $row['fixed_payment'] = 0;
                                            }
                                        }

                                        $_total_fixed_payment += $row['fixed_payment'];
                                    ?>

									<?php echo Yii::$app->formatter->asNumberFormat($row['fixed_payment']); ?>
								</td>


								<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
									<?php echo Yii::$app->formatter->asNumberFormat($row['total_pay'] + $row['fixed_payment']); ?>
								</td>
                                <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                                    <?php echo Yii::$app->formatter->asNumberFormat($row['max_consumption']); ?>
                                </td>
							</tr>
						<?php endforeach; ?>
						<tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat($tenant['pisga_consumption']); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat($tenant['geva_consumption']); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat($tenant['shefel_consumption']); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat($tenant['total_loads_consumption']); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat($tenant['total_extras_consumption']); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat($tenant['total_consumption']); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat($tenant['total_extras_pay']); ?>
							</td>

							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php
                                    // #uncomment
                                    //echo Yii::$app->formatter->asNumberFormat($tenant['fixed_payment']);
                                    echo Yii::$app->formatter->asNumberFormat($_total_fixed_payment);
                                ?>
							</td>

							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php
                                    // #uncomment
                                    //echo Yii::$app->formatter->asNumberFormat($tenant['total_pay'] + $tenant['fixed_payment']);
                                    echo Yii::$app->formatter->asNumberFormat($data['total_pay'] + $_total_fixed_payment);
                                ?>
							</td>
                            <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                                <?php echo Yii::$app->formatter->asNumberFormat($tenant['max_consumption']); ?>
                            </td>
						</tr>
					</tbody>
				</table>
				<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:5px;" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<td style="padding:5px 60px; font-weight: bold; font-size: 10pt;"><?=Yii::t('common.graph', 'Kwh monthly summary')?></td>
							<td style="padding:5px 60px; font-weight: bold; font-size: 10pt;"><?=Yii::t('common.graph', 'Payments monthly summary')?></td>
						</tr>
						<tr>
							<td style="padding:5px;">
								<?php
									$MyData = new \pData();
									$MyData->addPoints($graphKwhDataSetPisga,Yii::t('common.graph', 'Pisga'));
									$MyData->addPoints($graphKwhDataSetGeva,Yii::t('common.graph', 'Geva'));
									$MyData->addPoints($graphKwhDataSetShefel,Yii::t('common.graph', 'Shefel'));
									$MyData->addPoints($graphKwhAxisLabel,"Labels");
									$MyData->setAbscissa("Labels");
									$MyData->setPalette(Yii::t('common.graph', 'Pisga'),array("R"=>196,"G"=>2,"B"=>51));
									$MyData->setPalette(Yii::t('common.graph', 'Geva'),array("R"=>0,"G"=>163,"B"=>104));
									$MyData->setPalette(Yii::t('common.graph', 'Shefel'),array("R"=>0,"G"=>136,"B"=>191));

									$myPicture = new \pImage(400,230,$MyData,TRUE);
									$myPicture->setFontProperties(array("FontName"=>Yii::getAlias('@common/components/chart/pchart/fonts/arimo.ttf'),"FontSize"=>8));
									$myPicture->setGraphArea(50,30,350,200);
									$myPicture->drawScale(array("DrawSubTicks"=>TRUE,"Mode"=>SCALE_MODE_ADDALL_START0));
									$myPicture->setShadow(FALSE);
									$myPicture->drawStackedBarChart(array("Surrounding"=>-15,"InnerSurrounding"=>15));
									$myPicture->drawLegend(0,220,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

									ob_start();
									imagepng($myPicture->Picture);
									$contents =  ob_get_contents();
									ob_end_clean();

									echo Html::img('data:image/png;base64,' .base64_encode($contents), ['scheme' => 'data']);
								?>
							</td>
							<td style="padding:5px;">
								<?php
									$MyData = new \pData();
									$MyData->addPoints($graphNisDataSetPisga,Yii::t('common.graph', 'Pisga'));
									$MyData->addPoints($graphNisDataSetGeva,Yii::t('common.graph', 'Geva'));
									$MyData->addPoints($graphNisDataSetShefel,Yii::t('common.graph', 'Shefel'));
									$MyData->addPoints($graphNisAxisLabel,"Labels");
									$MyData->setAbscissa("Labels");
									$MyData->setPalette(Yii::t('common.graph', 'Pisga'),array("R"=>196,"G"=>2,"B"=>51));
									$MyData->setPalette(Yii::t('common.graph', 'Geva'),array("R"=>0,"G"=>163,"B"=>104));
									$MyData->setPalette(Yii::t('common.graph', 'Shefel'),array("R"=>0,"G"=>136,"B"=>191));

									$myPicture = new \pImage(400,230,$MyData,TRUE);
									$myPicture->setFontProperties(array("FontName"=>Yii::getAlias('@common/components/chart/pchart/fonts/arimo.ttf'),"FontSize"=>8));
									$myPicture->setGraphArea(50,30,350,200);
									$myPicture->drawScale(array("DrawSubTicks"=>TRUE,"Mode"=>SCALE_MODE_ADDALL_START0));
									$myPicture->setShadow(FALSE);
									$myPicture->drawStackedBarChart(array("Surrounding"=>-15,"InnerSurrounding"=>15));
									$myPicture->drawLegend(0,220,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

									ob_start();
									imagepng($myPicture->Picture);
									$contents =  ob_get_contents();
									ob_end_clean();

									echo Html::img('data:image/png;base64,' .base64_encode($contents), ['scheme' => 'data']);
								?>
							</td>
						</tr>
					</tbody>
				</table>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
<htmlpagefooter name="HTMLFooter" style="display:none">
	<div style="font-size: 10px; color: #000;">
		<?php echo Yii::t('common.view', 'Page - {PAGENO}'); ?>
	</div>
</htmlpagefooter>