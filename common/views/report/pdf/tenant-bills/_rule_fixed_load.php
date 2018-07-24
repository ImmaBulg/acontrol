<?php
use common\components\i18n\LanguageSelector;
use common\models\RateType;
use common\models\RuleFixedLoad;
use common\models\helpers\reports\ReportGeneratorTenantBills;

$direction = LanguageSelector::getAliasLanguageDirection();
?>
<?php
	switch ($rule['rule']['use_type']) {
		case RuleFixedLoad::USE_TYPE_KWH_TAOZ:
		case RuleFixedLoad::USE_TYPE_KWH_FIXED:
?>
	<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<td style="padding:5px;" colspan="5">
					<strong>
						<?php echo $rule['rule']['name']; ?>
					</strong>
				</td>
			</tr>
			<tr bgcolor="#7e7e7e">
				<th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Rate type'); ?>
				</th>
				<th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Consumption type'); ?>
				</th>
				<th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Previous reading'); ?>
				</th>
				<th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Current reading'); ?>
				</th>
				<th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Total consumption in Kwh'); ?>
				</th>
				<th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Price per 1 Kwh in Agorot'); ?>
				</th>
				<th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Total to pay'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($rule['rates'] as $rate): ?>
				<tr>
					<td style="padding:10px 5px 5px;border:1px solid #000;" colspan="2">
						<?php echo Yii::t('common.view', 'Bill details for dates'); ?>:
					</td>
					<td style="padding:10px 5px 5px;border:1px solid #000;" align="center">
						<?php echo Yii::$app->formatter->asDate($rate['reading_from_date'], 'dd/MM/yy'); ?>
					</td>
					<td style="padding:10px 5px 5px;border:1px solid #000;" align="center">
						<?php echo Yii::$app->formatter->asDate($rate['reading_to_date'], 'dd/MM/yy'); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" colspan="3"></td>
				</tr>

				<?php if ($rule['rule']['use_type'] == RuleFixedLoad::USE_TYPE_KWH_TAOZ): ?>
					<tr>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo $rate['pisga']['identifier']; ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'Pisga'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($rate['pisga']['reading_diff']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($rate['pisga']['price']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asPrice($rate['pisga']['total_pay']); ?>
						</td>
					</tr>
					<tr>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo $rate['geva']['identifier']; ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'Geva'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($rate['geva']['reading_diff']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($rate['geva']['price']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asPrice($rate['geva']['total_pay']); ?>
						</td>
					</tr>
					<tr>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo $rate['shefel']['identifier']; ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo Yii::t('common.view', 'Shefel'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($rate['shefel']['reading_diff']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($rate['shefel']['price']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asPrice($rate['shefel']['total_pay']); ?>
						</td>
					</tr>
				<?php else: ?>
					<tr>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo $rate['fixed']['identifier']; ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center"></td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($rate['fixed']['reading_diff']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asNumberFormat($rate['fixed']['price']); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo Yii::$app->formatter->asPrice($rate['fixed']['total_pay']); ?>
						</td>
					</tr>
				<?php endif; ?>
			<?php
                break;
                endforeach; ?>
		</tbody>
	</table>
	<?php if ($rule['rule']['description'] != null): ?>
		<table dir="<?php echo $direction; ?>" style="width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td style="padding:5px;">
						<?php echo $rule['rule']['description']; ?>
					</td>
				</tr>
			</tbody>
		</table>
	<?php endif; ?>
<?php
	break;
		case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
?>
	<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<td style="padding:5px;" colspan="2">
					<strong>
						<?php echo $rule['rule']['name']; ?>
					</strong>
				</td>
			</tr>
			<tr bgcolor="#7e7e7e">
				<th style="color:#fff;font-weight:normal;padding:5px;width:85%;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Comment'); ?>
				</th>
				<th style="color:#fff;font-weight:normal;padding:5px;width:15%;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Total to pay'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="padding:5px;border:1px solid #000;">
					<?php echo $rule['rule']['description']; ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asPrice($rule['total_pay']); ?>
				</td>
			</tr>
		</tbody>
	</table>
<?php
	break;
		case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
?>
	<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<td style="padding:5px;" colspan="4">
					<strong>
						<?php echo $rule['rule']['name']; ?>
					</strong>
				</td>
			</tr>
			<tr bgcolor="#7e7e7e">
				<th style="color:#fff;font-weight:normal;padding:5px;width:55%;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Comment'); ?>
				</th>
				<th style="color:#fff;font-weight:normal;padding:5px;width:15%;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Total consumption'); ?>
				</th>
				<th style="color:#fff;font-weight:normal;padding:5px;width:15%;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Price per 1 Kwh in'); ?>
				</th>
				<th style="color:#fff;font-weight:normal;padding:5px;width:15%;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Total to pay'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="padding:5px;border:1px solid #000;">
					<?php echo $rule['rule']['description']; ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asNumberFormat($rule['total_consumption']); ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asNumberFormat($rule['price']); ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asPrice($rule['total_pay']); ?>
				</td>
			</tr>
		</tbody>
	</table>
<?php
	break;
		case RuleFixedLoad::USE_TYPE_MONEY:
		default:
?>
	<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<td style="padding:5px;" colspan="2">
					<strong>
						<?php echo $rule['rule']['name']; ?>
					</strong>
				</td>
			</tr>
			<tr bgcolor="#7e7e7e">
				<th style="color:#fff;font-weight:normal;padding:5px;width:85%;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Comment'); ?>
				</th>
				<th style="color:#fff;font-weight:normal;padding:5px;width:15%;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Total to pay'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="padding:5px;border:1px solid #000;">
					<?php echo $rule['rule']['description']; ?>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<?php echo Yii::$app->formatter->asPrice($rule['total_pay']); ?>
				</td>
			</tr>
		</tbody>
	</table>
<?php break; } ?>
