<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\models\Site;
use common\components\i18n\LanguageSelector;
use common\models\helpers\reports\ReportGeneratorRatesComprasion;

$direction = LanguageSelector::getAliasLanguageDirection();
?>
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
				<?php echo Yii::t('common.view', 'Comparison report for'); ?>:
			</td>
			<td style="padding:5px;">
				<?php echo $site->name; ?>
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
<?php if(!empty($rules)): ?>
	<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0" cellspacing="0">
		<thead>
			<tr bgcolor="#7e7e7e">
				<th style="border:1px solid #000;color:#fff;font-weight:normal;padding:5px;width:8%;" align="center">
					<?php echo Yii::t('common.view', 'Row number'); ?>
				</th>
				<th style="border:1px solid #000;color:#fff;font-weight:normal;padding:5px;width:8%;" align="center">
					<?php echo Yii::t('common.view', 'Tenant ID'); ?>
				</th>
				<th style="border:1px solid #000;color:#fff;font-weight:normal;padding:5px;width:10%;" align="center">
					<?php echo Yii::t('common.view', 'To issue'); ?>
				</th>
				<th style="border:1px solid #000;color:#fff;font-weight:normal;padding:5px;width:12%;" align="center">
					<?php echo Yii::t('common.view', 'Tenant name'); ?>
				</th>
				<th style="border:1px solid #000;color:#fff;font-weight:normal;padding:5px;width:16%;" align="center">
					<?php echo Yii::t('common.view', 'Meter ID / Group Name'); ?>
				</th>
				<th style="border:1px solid #000;color:#fff;font-weight:normal;padding:5px;width:12%;" align="center">
					<?php echo Yii::t('common.view', 'High rate'); ?>
				</th>
				<th style="border:1px solid #000;color:#fff;font-weight:normal;padding:5px;width:12%;" align="center">
					<?php echo Yii::t('common.view', 'Low rate'); ?>
				</th>
				<th style="border:1px solid #000;color:#fff;font-weight:normal;padding:5px;width:12%;" align="center">
					<?php echo Yii::t('common.view', 'Diff in NIS'); ?>
				</th>
				<th style="border:1px solid #000;color:#fff;font-weight:normal;padding:5px;width:12%;" align="center">
					<?php echo Yii::t('common.view', 'Diff in %'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php $index = 0; ?>
			<?php foreach($rules as $rule): ?>
				<tr>
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php echo ($index + 1); ?>
						<?php $index++; ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php echo $rule['tenant_id']; ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php switch ($rule['model_tenant']->to_issue) {
							case Site::TO_ISSUE_AUTOMATIC:
							case Site::TO_ISSUE_MANUAL:
								echo Yii::t('common.view', 'To issue');
								break;
							
							default:
								echo Yii::t('common.view', 'Not to issue');
								break;
						} ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php echo $rule['tenant_name']; ?>
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
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php echo implode(', ', $rule['rule_name']); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo Yii::$app->formatter->asPrice($rule['total_pay_high']); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo Yii::$app->formatter->asPrice($rule['total_pay_low']); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo Yii::$app->formatter->asPrice($rule['total_pay_low'] - $rule['total_pay_high']); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo Yii::$app->formatter->asPercentage($rule['total_pay_low'] ? (100 - ($rule['total_pay_high'] * 100) / $rule['total_pay_low']) : $rule['total_pay_low']); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<tr>
				<td style="padding:5px;"></td>
				<td style="padding:5px;"></td>
				<td style="padding:5px;"></td>
				<td style="padding:5px;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Total'); ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center"></td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asPrice($total_pay_high); ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asPrice($total_pay_low); ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asPrice($total_pay_diff); ?>
				</td>
				<td></td>
			</tr>
			<tr>
				<td style="padding:5px;"></td>
				<td style="padding:5px;"></td>
				<td style="padding:5px;"></td>
				<td style="padding:5px;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Total (including VAT)'); ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center"></td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asPrice($total_pay_high + $vat_pay_high); ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asPrice($total_pay_low + $vat_pay_low); ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asPrice(($total_pay_low + $vat_pay_low) - ($total_pay_high + $vat_pay_high)); ?>
				</td>
				<td></td>
			</tr>
			<?php if(!empty($additional_parameters)): ?>
				<tr>
					<td style="padding:5px;"></td>
					<td style="padding:5px;"></td>
					<td style="padding:5px;"></td>
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Electric company'); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php if (isset($additional_parameters['electric_company_rate_low'])): ?>
							<?php echo Yii::$app->formatter->asPrice($additional_parameters['electric_company_rate_low']); ?>
						<?php endif; ?>
					</td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td style="padding:5px;"></td>
					<td style="padding:5px;"></td>
					<td style="padding:5px;"></td>
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Diff in NIS'); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php if (isset($additional_parameters['electric_company_rate_low'])): ?>
							<?php echo Yii::$app->formatter->asPrice($additional_parameters['electric_company_rate_low'] - ($total_pay_low + $vat_pay_low)); ?>
						<?php endif; ?>
					</td>
					<td></td>
					<td></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
<?php endif; ?>
<htmlpagefooter name="HTMLFooter" style="display:none">
	<div style="font-size: 10px; color: #000;">
		<?php echo Yii::t('common.view', 'Page - {PAGENO}'); ?>
	</div>
</htmlpagefooter>