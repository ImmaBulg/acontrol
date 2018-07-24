<?php

use yii\helpers\ArrayHelper;
use common\models\RateType;
use common\components\i18n\LanguageSelector;
use common\models\helpers\reports\ReportGeneratorTenantBills;

$direction = LanguageSelector::getAliasLanguageDirection();
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
		<?php if(!empty($additional_parameters)): ?>
			<?php if(isset($additional_parameters['group_use_percent'])): ?>
				<tr>
					<td style="padding:5px;" colspan="5">
						<?php echo Yii::t('common.view', 'Pisga usage percentage: {value}', [
							'value' => Yii::$app->formatter->asPercentage(ArrayHelper::getValue($rule, 'percent.pisga', 100)),
						]); ?>
						<br>
						<?php echo Yii::t('common.view', 'Geva usage percentage: {value}', [
							'value' => Yii::$app->formatter->asPercentage(ArrayHelper::getValue($rule, 'percent.geva', 100)),
						]); ?>
						<br>
						<?php echo Yii::t('common.view', 'Shefel usage percentage: {value}', [
							'value' => Yii::$app->formatter->asPercentage(ArrayHelper::getValue($rule, 'percent.shefel', 100)),
						]); ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>
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
			<?php if ($rate_type == RateType::TYPE_TAOZ): ?>
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
						<?php echo $rate['identifier']; ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo Yii::$app->formatter->asNumberFormat($rate['pisga']['reading_diff'] + $rate['geva']['reading_diff'] + $rate['shefel']['reading_diff']); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo Yii::$app->formatter->asNumberFormat($rate['price']); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo Yii::$app->formatter->asPrice($rate['pisga']['total_pay'] + $rate['geva']['total_pay'] + $rate['shefel']['total_pay']); ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	</tbody>
</table>