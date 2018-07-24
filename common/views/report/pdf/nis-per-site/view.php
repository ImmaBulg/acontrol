<?php
use common\helpers\CalculationHelper;
use common\models\Tenant;
use common\helpers\Html;
use common\components\i18n\LanguageSelector;
use common\models\Site;
use common\models\RuleFixedLoad;
use common\models\helpers\reports\ReportGeneratorNisPerSite;
use yii\helpers\ArrayHelper;

$direction = LanguageSelector::getAliasLanguageDirection();
$power_factor_visibility = (!empty($additional_parameters)) ?
    ArrayHelper::getValue($additional_parameters, 'power_factor_visibility', Site::POWER_FACTOR_DONT_SHOW) :
    Site::POWER_FACTOR_DONT_SHOW;
?>
<table dir="<?php echo $direction; ?>" style="width:100%;font-size:12px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td style="padding:5px;font-size:14px;" colspan="2">
				<strong><?php echo Yii::t('common.view', 'Financial report'); ?></strong>
			</td>
		</tr>
		<tr>
			<td style="padding:5px;" colspan="2">
				<?php echo Yii::t('common.view', 'To'); ?>: <?php echo $site_owner->name; ?>
			</td>
		</tr>
		<tr>
			<td style="padding:5px;width:60%;">
				<?php echo Yii::t('common.view', 'Summary report for site'); ?>: <?php echo $site->name; ?>
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
	<?php foreach($data as $type => $rules): ?>
		<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
			<thead>
				<tr bgcolor="#7e7e7e">
					<th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;width:6%;" align="center">
						<?php echo Yii::t('common.view', 'Row number'); ?>
					</th>
					<th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Tenant ID'); ?>
					</th>
					<th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Tenant name'); ?>
					</th>
					<th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;width:12%;" align="center">
						<?php echo Yii::t('common.view', 'Meter ID'); ?>
					</th>
					<?php if (!empty($additional_parameters['column_total_pay_single_channel_rules'])): ?>
						<th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'Total to pay based on Single rules'); ?>
						</th>
					<?php endif; ?>
					<?php if (!empty($additional_parameters['column_total_pay_group_load_rules'])): ?>
						<th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'Total to pay based on Group load rules'); ?>
						</th>
					<?php endif; ?>
					<?php if (!empty($additional_parameters['column_total_pay_fixed_load_rules'])): ?>
						<th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'Total to pay based on Fixed load rules'); ?>
						</th>
					<?php endif; ?>
                    <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                                 Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                        <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                            <?php echo Yii::t('common.view', 'Power factor'); ?>
                        </th>
                    <?php endif;?>
                    <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                                 Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                        <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                            <?php echo Yii::t('common.view', 'Power factor addition'); ?>
                        </th>
                    <?php endif;?>
					<?php if (!empty($additional_parameters['column_fixed_payment'])): ?>
						<th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'Fixed payment'); ?>
						</th>
					<?php endif; ?>

                    <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Total'); ?>
					</th>
					<?php if (!empty($additional_parameters['is_vat_included'])): ?>
						<th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'VAT'); ?>
						</th>
						<th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'Total (including VAT)'); ?>
						</th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
				<?php $index = 1; ?>
				<?php $tenants_total_to_pay = 0; ?>
				<?php foreach($rules as $tenant_id => $rule): ?>
					<?php $tenant_total_to_pay = $rule[ReportGeneratorNisPerSite::RULE_SINGLE_CHANNEL]; ?>
					<tr>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo $index; ?>
							<?php $index++; ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo $rule['model_tenant']->id; ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo $rule['identifier']; ?>
							<p>
								<?php if ($entrance_date = $rule['model_tenant']->getEntranceDateReport($report->from_date, $report->to_date)): ?>
									<?php echo Yii::t('common.view', 'Entry date: {date}', [
										'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
									]); ?>
								<?php endif; ?>

								<?php if ($exit_date = $rule['model_tenant']->getExitDateReport($report->from_date, $report->to_date)): ?>
									<?php echo Yii::t('common.view', 'Exit date: {date}', [
										'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
									]); ?>
								<?php endif; ?>
							</p>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo implode(", ", $rule['rules']); ?>
						</td>
						<?php if (!empty($additional_parameters['column_total_pay_single_channel_rules'])): ?>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asPrice($rule[ReportGeneratorNisPerSite::RULE_SINGLE_CHANNEL]); ?>
							</td>
						<?php endif; ?>
						<?php if (!empty($additional_parameters['column_total_pay_group_load_rules'])): ?>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php $tenant_total_to_pay += $rule[ReportGeneratorNisPerSite::RULE_GROUP_LOAD]; ?>
								<?php echo Yii::$app->formatter->asPrice($rule[ReportGeneratorNisPerSite::RULE_GROUP_LOAD]); ?>
							</td>
						<?php endif; ?>
						<?php if (!empty($additional_parameters['column_total_pay_fixed_load_rules'])): ?>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php $tenant_total_to_pay += $rule[ReportGeneratorNisPerSite::RULE_FIXED_LOAD]; ?>
								<?php echo Yii::$app->formatter->asPrice($rule[ReportGeneratorNisPerSite::RULE_FIXED_LOAD]); ?>
							</td>
						<?php endif; ?>

                        <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                                     Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>

                            <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                                <?= Yii::$app->formatter->asNumberFormat($rule['power_factor_value'],3)?>
                            </td>
                        <?php endif; ?>
                        <?php $power_factor_addition = 0; ?>
                        <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS])):
                            $power_factor_addition = $tenant_total_to_pay / 100 * $rule['power_factor_percent'];
                            $tenant_total_to_pay += $power_factor_addition;
                        endif ?>
                        <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                                     Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                            <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                                <?= Yii::$app->formatter->asPrice($power_factor_addition)?>
                            </td>
                        <?php endif; ?>
                        <?php if (!empty($additional_parameters['column_fixed_payment'])): ?>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">

                                <?php
                                    /**
                                     * REPLACE / SHOW fixed_payments on rates on priority `So Rate->Tenant->Site in order of priority`
                                     */
                                    $_fp = 0;

                                    if (CalculationHelper::isCorrectFixedPayment($rule['fixed_payment'])) //tenant
                                        $_fp = $rule['fixed_payment'];
                                    elseif (CalculationHelper::isCorrectFixedPayment($site->relationSiteBillingSetting['fixed_payment'])) //site
                                        $_fp = $site->relationSiteBillingSetting['fixed_payment'];
                                    elseif (CalculationHelper::isCorrectFixedPayment($additional_parameters['rates_fixed_payments'])) // rates
                                         $_fp = $additional_parameters['rates_fixed_payments'];

                                    // replace
                                    $rule['fixed_payment'] = $_fp;
                                ?>


								<?php $tenant_total_to_pay += $rule['fixed_payment']; ?>
								<?php echo Yii::$app->formatter->asPrice($rule['fixed_payment']); ?>
							</td>
						<?php endif; ?>

						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asPrice($tenant_total_to_pay); ?>
						</td>
						<?php if (!empty($additional_parameters['is_vat_included'])): ?>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asPrice(($tenant_total_to_pay / 100) * $vat_percentage); ?>
							</th>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asPrice($tenant_total_to_pay + (($tenant_total_to_pay / 100) * $vat_percentage)); ?>
							</th>
						<?php endif; ?>
					</tr>
					<?php $tenants_total_to_pay += $tenant_total_to_pay; ?>
				<?php endforeach; ?>

				<?php if ($type == Tenant::TYPE_TENANT): ?>
					<tr>
						<td style="padding:15px;"></td>
					</tr>
					<tr>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
                        <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                                     Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                            <td style="padding:5px;"></td>
                            <td style="padding:5px;"></td>
                        <?php endif;?>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php if (!empty($additional_parameters['is_vat_included'])): ?>
								<?php echo Yii::t('common.view', 'Total (without VAT)'); ?>
							<?php else: ?>
								<?php echo Yii::t('common.view', 'Total'); ?>
							<?php endif; ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asPrice($tenants_total_to_pay); ?>
						</td>
					</tr>
					<tr>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
                        <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                                     Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                            <td style="padding:5px;"></td>
                            <td style="padding:5px;"></td>
                        <?php endif;?>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'VAT'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asPercentage($vat_percentage); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php $tenants_vat = ($tenants_total_to_pay / 100) * $vat_percentage; ?>
							<?php echo Yii::$app->formatter->asPrice($tenants_vat); ?>
						</td>
					</tr>
					<tr>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
						<td style="padding:5px;"></td>
                        <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                                     Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                            <td style="padding:5px;"></td>
                            <td style="padding:5px;"></td>
                        <?php endif;?>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'Total (including VAT)'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php $tenants_total_to_pay += $tenants_vat; ?>
							<?php echo Yii::$app->formatter->asPrice($tenants_total_to_pay); ?>
						</td>
					</tr>
					<?php if(!empty($additional_parameters)): ?>
						<tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
                            <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                                         Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                                <td style="padding:5px;"></td>
                                <td style="padding:5px;"></td>
                            <?php endif;?>
							<td style="padding:5px;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Electric company bill'); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center"></td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if (isset($additional_parameters['electric_company_price'])): ?>
									<?php echo Yii::$app->formatter->asPrice($additional_parameters['electric_company_price']); ?>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
                            <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                                         Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                                <td style="padding:5px;"></td>
                                <td style="padding:5px;"></td>
                            <?php endif;?>
							<td style="padding:5px;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Diff in NIS'); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center"></td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if (isset($additional_parameters['electric_company_price'])): ?>
									<?php echo Yii::$app->formatter->asPrice($tenants_total_to_pay - $additional_parameters['electric_company_price']); ?>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
							<td style="padding:5px;"></td>
                            <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                                         Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                                <td style="padding:5px;"></td>
                                <td style="padding:5px;"></td>
                            <?php endif;?>
							<td style="padding:5px;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Diff in %'); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center"></td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php if (!empty($additional_parameters['electric_company_price']) && ($difference = $tenants_total_to_pay - $additional_parameters['electric_company_price'])): ?>
									<?php echo Yii::$app->formatter->asPercentage($difference / $additional_parameters['electric_company_price'] * 100); ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>
			</tbody>
		</table>
	<?php endforeach; ?>
<?php endif; ?>
<htmlpagefooter name="HTMLFooter" style="display:none">
	<div style="font-size: 10px; color: #000;">
		<?php echo Yii::t('common.view', 'Page - {PAGENO}'); ?>
	</div>
</htmlpagefooter>