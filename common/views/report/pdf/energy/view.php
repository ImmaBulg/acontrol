<?php

use yii\helpers\ArrayHelper;

use common\models\Tenant;
use common\helpers\Html;
use common\components\i18n\LanguageSelector;
use common\models\helpers\reports\ReportGeneratorKwhPerSite;

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
				<?php echo Yii::t('common.view', 'Summary of Kwh usage for site'); ?>:
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
<?php if(!empty($data['rows'])): ?>
	<?php foreach($data['rows'] as $index => $rows): ?>
		<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
			<thead>
				<tr bgcolor="#7e7e7e">
					<th style="color:#fff;padding:5px;width:15%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Grandparent Meter ID'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Grandparent Total Kwh'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:15%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Parent Meter ID'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Parent Total Kwh'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:20%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Tenant name'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:20%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Meter ID'); ?>
					</th>
					<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
						<?php echo Yii::t('common.view', 'Total Kwh'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($rows as $row): ?>
					<tr>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo ArrayHelper::getValue($row, 'grandparent_name'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo ArrayHelper::getValue($row, 'grandparent_consumption.total'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo ArrayHelper::getValue($row, 'parent_name'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo ArrayHelper::getValue($row, 'parent_consumption.total'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center">
							<?php echo ArrayHelper::getValue($row, 'tenants'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo ArrayHelper::getValue($row, 'children_name'); ?>
						</td>
						<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
							<?php echo ArrayHelper::getValue($row, 'children_consumption.total'); ?>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" colspan="3">
						<?php echo Yii::t('common.view', 'Total Kwh of all tenants'); ?>: <span dir="ltr"><?php echo ArrayHelper::getValue($data, "totals.$index.children_consumption.total", 0); ?></span>
					</td>
				</tr>
				<tr>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" align="center"></td>
					<td style="padding:5px;border:1px solid #000;" colspan="3">
						<?php echo Yii::t('common.view', 'Difference between parent and total of all tenants'); ?>: <span dir="ltr"><?php echo ArrayHelper::getValue($data, "totals.$index.parent_consumption.total", 0) - ArrayHelper::getValue($data, "totals.$index.children_consumption.total", 0); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
	<?php endforeach; ?>
	<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:70%;font-size:11px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
		<thead>
			<tr bgcolor="#7e7e7e">
				<th style="color:#fff;padding:5px;width:15%;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Meter ID'); ?>
				</th>
				<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Pisga'); ?>
				</th>
				<th style="color:#fff;padding:5px;width:15%;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Geva'); ?>
				</th>
				<th style="color:#fff;padding:5px;width:10%;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Shefel'); ?>
				</th>
				<th style="color:#fff;padding:5px;width:20%;font-weight:normal;border:1px solid #000;" align="center">
					<?php echo Yii::t('common.view', 'Total Kwh'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($data['totals'] as $index => $totals): ?>
				<tr>
					<td style="padding:5px;border:1px solid #000;" align="center" align="center" dir="ltr">
						<?php echo ArrayHelper::getValue($totals, 'parent_name'); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo ArrayHelper::getValue($totals, 'parent_consumption.pisga', 0); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo ArrayHelper::getValue($totals, 'parent_consumption.geva', 0); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo ArrayHelper::getValue($totals, 'parent_consumption.shefel', 0); ?>
					</td>
					<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
						<?php echo ArrayHelper::getValue($totals, 'parent_consumption.total', 0); ?>
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