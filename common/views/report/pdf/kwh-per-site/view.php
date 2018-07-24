<?php

require_once(Yii::getAlias('@common/components/chart/pchart/class/pData.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pDraw.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pPie.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pImage.class.php'));

use yii\helpers\ArrayHelper;

use common\models\Site;
use common\models\Tenant;
use common\helpers\Html;
use common\components\i18n\LanguageSelector;
use common\models\helpers\reports\ReportGeneratorKwhPerSite;

$direction = LanguageSelector::getAliasLanguageDirection();
?>
<table dir="<?php echo $direction; ?>" style="width:100%;font-size:12px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td style="padding:5px;font-size:14px;" colspan="2">
				<strong><?php echo Yii::t('common.view', 'Kwh report'); ?></strong>
			</td>
		</tr>
		<tr>
			<td style="padding:5px;" colspan="2">
				<?php echo Yii::t('common.view', 'To'); ?>: <?php echo $site_owner->name; ?>
			</td>
		</tr>
		<tr>
			<td style="padding:5px;width:60%">
				<?php echo Yii::t('common.view', 'Kwh summary report for'); ?>: <?php echo $site->name; ?>
			</td>
			<td style="padding:5px;width:40%;">
				<?php echo Yii::t('common.view', 'Issue date'); ?>: <?php echo Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'); ?>
			</td>
		</tr>
		<tr>
			<td style="padding:5px;" colspan="2">
				<?php echo Yii::t('common.view', 'Report range'); ?>: (<?php echo Yii::$app->formatter->asDate($report->from_date, 'dd/MM/yy'); ?> - <?php echo Yii::$app->formatter->asDate($report->to_date, 'dd/MM/yy'); ?>)
			</td>
		</tr>
	</tbody>
</table>
<?php if(!empty($data)): ?>
	<?php foreach($data as $type => $tenants): ?>
		<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
			<thead>
				<tr bgcolor="#7e7e7e">
					<th style="color:#fff;padding:5px;width:8%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Row number'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:8%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Tenant ID'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Tenant name'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Meter ID / Group Name'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Pisga Kwh'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Geva Kwh'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Shefel Kwh'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Total Kwh'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Max demand'); ?>
					</th>
                    <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
                        <?php echo Yii::t('common.view', 'Power factor'); ?>
                    </th>
				</tr>
			</thead>
			<tbody>
				<?php $index = 1; ?>
				<?php foreach($tenants as $tenant): ?>
					<?php
						$total_tenant = [
							'pisga' => 0,
							'geva' => 0,
							'shefel' => 0,
							'total_consumption' => 0,
						];
					?>
					<?php foreach($tenant['rules'] as $rule_index => $rule): ?>
						<tr>
							<?php if (!$rule_index): ?>
								<td style="padding:5px;border:1px solid #000;" align="center">
									<?php echo $index; ?>
								</td>
								<td style="padding:5px;border:1px solid #000;" align="center">
									<?php echo $tenant['model_tenant']->id; ?>
								</td>
								<td style="padding:5px;border:1px solid #000;" align="center">
									<?php echo $tenant['identifier']; ?>
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
							<?php else: ?>
								<td style="padding:5px;"></td>
								<td style="padding:5px;"></td>
								<td style="padding:5px;"></td>
							<?php endif; ?>

							<td style="padding:5px;border:1px solid #000;" align="center">
								<?php
									switch ($rule['rule']['type']) {
										case ReportGeneratorKwhPerSite::RULE_GROUP_LOAD:
											echo $rule['rule']['name'];
											if(!empty($additional_parameters)) {
												if(isset($additional_parameters['group_use_percent'])) {
													echo "<br />" . implode("<br />", [
														Yii::t('common.view', 'Pisga usage percentage: {value}', [
															'value' => Yii::$app->formatter->asPercentage(ArrayHelper::getValue($rule, 'percent.pisga', 100)),
														]),
														Yii::t('common.view', 'Geva usage percentage: {value}', [
															'value' => Yii::$app->formatter->asPercentage(ArrayHelper::getValue($rule, 'percent.geva', 100)),
														]),
														Yii::t('common.view', 'Shefel usage percentage: {value}', [
															'value' => Yii::$app->formatter->asPercentage(ArrayHelper::getValue($rule, 'percent.shefel', 100)),
														]),
													]);
												}
											}
											break;
										case ReportGeneratorKwhPerSite::RULE_SINGLE_CHANNEL:
											echo $rule['rule']['meter_channel_name']. ' - ' .$rule['rule']['meter_name'];
											break;
										default:
											echo $rule['rule']['name'];
											break;
									}
								?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php $total_tenant['pisga'] += $rule['pisga']; ?>
								<?php echo Yii::$app->formatter->asNumberFormat($rule['pisga']); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php $total_tenant['geva'] += $rule['geva']; ?>
								<?php echo Yii::$app->formatter->asNumberFormat($rule['geva']); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php $total_tenant['shefel'] += $rule['shefel']; ?>
								<?php echo Yii::$app->formatter->asNumberFormat($rule['shefel']); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php $total_tenant['total_consumption'] += $rule['total_consumption']; ?>
								<?php echo Yii::$app->formatter->asNumberFormat($rule['total_consumption']); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(!empty($rule['max_consumption'])): ?>
									<?php echo Yii::$app->formatter->asNumberFormat($rule['max_consumption']); ?>
								<?php endif; ?>
							</td>
                            <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                                <?php if(!empty($rule['power_factor_value'])): ?>
                                    <?php echo Yii::$app->formatter->asNumberFormat($rule['power_factor_value']); ?>
                                <?php endif; ?>
                            </td>
						</tr>
					<?php endforeach; ?>
					<?php $index++; ?>
					<tr>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;border:1px solid #000;" align="center" bgcolor="#e2e2e2">
							<?php echo Yii::t('common.view', 'Total consumption'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" bgcolor="#e2e2e2" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($total_tenant['pisga']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" bgcolor="#e2e2e2" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($total_tenant['geva']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" bgcolor="#e2e2e2" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($total_tenant['shefel']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" bgcolor="#e2e2e2" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($total_tenant['total_consumption']); ?>
						</td>
					</tr>
				<?php endforeach; ?>

				<?php if ($type == Tenant::TYPE_TENANT): ?>
					<tr>
						<td style="padding:5px;"></td>
					</tr>
					<tr>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'Total consumption'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($total[$type]['pisga']['reading_diff']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($total[$type]['geva']['reading_diff']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($total[$type]['shefel']['reading_diff']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($total[$type]['total_consumption']); ?>
						</td>
					</tr>
					<?php if(!empty($additional_parameters)): ?>
						<?php
							$additional_parameters_sum = ArrayHelper::getValue($additional_parameters, 'electric_company_pisga', 0) + ArrayHelper::getValue($additional_parameters, 'electric_company_geva', 0) + ArrayHelper::getValue($additional_parameters, 'electric_company_shefel', 0);
						?>
						<tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Electric company bill'); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(isset($additional_parameters['electric_company_pisga'])): ?>
									<?php echo Yii::$app->formatter->asNumberFormat($additional_parameters['electric_company_pisga']); ?>
								<?php endif; ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(isset($additional_parameters['electric_company_geva'])): ?>
									<?php echo Yii::$app->formatter->asNumberFormat($additional_parameters['electric_company_geva']); ?>
								<?php endif; ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(isset($additional_parameters['electric_company_shefel'])): ?>
									<?php echo Yii::$app->formatter->asNumberFormat($additional_parameters['electric_company_shefel']); ?>
								<?php endif; ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat($additional_parameters_sum); ?>
							</td>
						</tr>
						<tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Diff in Kwh'); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(isset($additional_parameters['electric_company_pisga'])): ?>
									<?php echo Yii::$app->formatter->asNumberFormat($total[$type]['pisga']['reading_diff'] - $additional_parameters['electric_company_pisga']); ?>
								<?php endif; ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(isset($additional_parameters['electric_company_geva'])): ?>
									<?php echo Yii::$app->formatter->asNumberFormat($total[$type]['geva']['reading_diff'] - $additional_parameters['electric_company_geva']); ?>
								<?php endif; ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(isset($additional_parameters['electric_company_shefel'])): ?>
									<?php echo Yii::$app->formatter->asNumberFormat($total[$type]['shefel']['reading_diff'] - $additional_parameters['electric_company_shefel']); ?>
								<?php endif; ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat($total[$type]['total_consumption'] - $additional_parameters_sum); ?>
							</td>
						</tr>
						<tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Diff in %'); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(!empty($additional_parameters['electric_company_pisga']) && ($difference = $total[$type]['pisga']['reading_diff'] - $additional_parameters['electric_company_pisga'])): ?>
									<?php echo Yii::$app->formatter->asPercentage($difference / $additional_parameters['electric_company_pisga'] * 100); ?>
								<?php endif; ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(!empty($additional_parameters['electric_company_geva']) && ($difference = $total[$type]['geva']['reading_diff'] - $additional_parameters['electric_company_geva'])): ?>
									<?php echo Yii::$app->formatter->asPercentage($difference / $additional_parameters['electric_company_geva'] * 100); ?>
								<?php endif; ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(!empty($additional_parameters['electric_company_shefel']) && ($difference = $total[$type]['shefel']['reading_diff'] - $additional_parameters['electric_company_shefel'])): ?>
									<?php echo Yii::$app->formatter->asPercentage($difference / $additional_parameters['electric_company_shefel'] * 100); ?>
								<?php endif; ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if(!empty($additional_parameters_sum) && ($difference = $total[$type]['total_consumption'] - $additional_parameters_sum)): ?>
									<?php echo Yii::$app->formatter->asPercentage($difference / $additional_parameters_sum * 100); ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php if (Yii::$app->params['is_add_graph'] && $type == Tenant::TYPE_TENANT): ?>
			<?php
				$graphPieKwhDataSet = [
					$total[$type]['pisga']['reading_diff'],
					$total[$type]['geva']['reading_diff'],
					$total[$type]['shefel']['reading_diff'],
				];
			?>
			<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:5px;" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<td style="padding:5px;">
							<?php
								$MyData = new \pData();
								$MyData->addPoints($graphPieKwhDataSet,"ScoreA");
								$MyData->addPoints([
									Yii::t('common.graph', 'Pisga ({value} Kwh)', [
										'value' => Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($graphPieKwhDataSet, 0, 0)),
									]),
									Yii::t('common.graph', 'Geva ({value} Kwh)', [
										'value' => Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($graphPieKwhDataSet, 1, 0)),
									]),
									Yii::t('common.graph', 'Shefel ({value} Kwh)', [
										'value' => Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($graphPieKwhDataSet, 2, 0)),
									]),
								],"Labels");
								$MyData->setAbscissa("Labels");

								$myPicture = new \pImage(300,230,$MyData,TRUE);
								$myPicture->setFontProperties(array("FontName"=>Yii::getAlias('@common/components/chart/pchart/fonts/arimo.ttf'),"FontSize"=>8,"R"=>80,"G"=>80,"B"=>80));

								$PieChart = new \pPie($myPicture,$MyData);
								$PieChart->setSliceColor(0,array("R"=>196,"G"=>2,"B"=>51));
								$PieChart->setSliceColor(1,array("R"=>0,"G"=>163,"B"=>104));
								$PieChart->setSliceColor(2,array("R"=>0,"G"=>136,"B"=>191));
								$myPicture->setShadow(FALSE);
								$PieChart->draw3DPie(125,120,array("WriteValues"=>TRUE, "Radius"=>100,"DataGapAngle"=>12,"DataGapRadius"=>10,"Border"=>FALSE));
								$myPicture->setFontProperties(array("FontName"=>Yii::getAlias('@common/components/chart/pchart/fonts/arimo.ttf'),"FontSize"=>8,"R"=>0,"G"=>0,"B"=>0));
								$PieChart->drawPieLegend(0,195,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL)); 

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