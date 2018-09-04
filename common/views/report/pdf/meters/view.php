<?php

use common\models\Tenant;
use common\helpers\Html;
use common\components\i18n\LanguageSelector;
use common\models\helpers\reports\ReportGeneratorTenantBills;

$direction = LanguageSelector::getAliasLanguageDirection();
?>
<table dir="<?php echo $direction; ?>" style="width:100%;font-size:12px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td style="padding:5px;" align="center">
				<strong><?php echo $site->name; ?></strong>
			</td>
		</tr>
		<tr>
			<td style="padding:5px;">
				<?php echo Yii::t('common.view', 'List of meters on site'); ?>
			</td>
		</tr>
		<tr>
			<td style="padding:5px;">
				<?php echo Yii::t('common.view', 'Date of report: {date}', [
					'date' => Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'),
				]); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php if ($data != null): ?>
	<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0" cellspacing="0">
		<thead>
			<tr bgcolor="#7e7e7e">
				<th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Row number'); ?>
				</th>
				<th style="width:20%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Meter ID'); ?>
				</th>
				<th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Current multiplier'); ?>
				</th>
				<th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Voltage multiplier'); ?>
				</th>
				<th style="width:20%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Tenant name'); ?>
				</th>
				<th style="width:20%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Breaker name'); ?>
				</th>
				<th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'To issue'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php $index = 1; ?>
			<?php foreach($data as $row): ?>
				<tr>
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php echo $index;?>
						<?php $index++; ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo $row['rule_name']; ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo $row['meter_multiplier'];?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php echo $row['tenant']->name;?>
						<p>
							<?php if ($entrance_date = $row['tenant']->getEntranceDateReport($report->from_date, $report->to_date)): ?>
								<?php echo Yii::t('common.view', 'Entry date: {date}', [
									'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
								]); ?>
							<?php endif; ?>

							<?php if ($exit_date = $row['tenant']->getExitDateReport($report->from_date, $report->to_date)): ?>
								<?php echo Yii::t('common.view', 'Exit date: {date}', [
									'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
								]); ?>
							<?php endif; ?>
						</p>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php echo $row['meter']->breaker_name;?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center">
						<?php echo ($row['to_issue']) ? Yii::t('common.view', 'Yes, to issue') : Yii::t('common.view', 'Not to issue'); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
<htmlpagefooter name="HTMLFooter" style="display:none">
	<div style="font-size: 10px; color: #000;">
		<?php echo Yii::t('common.view', 'Page - {PAGENO}'); ?>
	</div>
</htmlpagefooter>
