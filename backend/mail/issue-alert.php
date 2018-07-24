<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\models\ReportFile;
?>
<p>
	<strong>
		<?php echo Yii::t('backend.mail', 'Hello, {name}', [
		'name' => $user['name'],
	]); ?>
	</strong>
</p>

<table style="width: 100%;color: #000; font:13px Arial; vertical-align: top;">
	<thead>
		<tr>
			<th style="border:1px solid #000;"><?php echo Yii::t('backend.mail', 'Site'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('backend.mail', 'Type'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('backend.mail', 'From date'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('backend.mail', 'To date'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('backend.mail', 'Link'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($reports as $report): ?>
			<tr>
				<td style="border:1px solid #000;">
					<?php echo $report->relationSite->name; ?>
				</td>
				<td style="border:1px solid #000;">
					<?php echo Yii::t('backend.mail', $report->getAliasType()); ?>
				</td>
				<td style="border:1px solid #000;">
					<?php echo Yii::$app->formatter->asDate($report->from_date); ?>
				</td>
				<td style="border:1px solid #000;">
					<?php echo Yii::$app->formatter->asDate($report->to_date); ?>
				</td>
				<td style="border:1px solid #000;">
					<?php if ($pdf_link = $report->getFilePath(ReportFile::FILE_TYPE_PDF)): ?>
						<?php echo Html::a(Yii::t('backend.mail', 'PDF'), $pdf_link); ?>
					<?php endif; ?>

					<?php if ($excel_link = $report->getFilePath(ReportFile::FILE_TYPE_EXCEL)): ?>
						<?php echo Html::a(Yii::t('backend.mail', 'Excel'), $excel_link); ?>
					<?php endif; ?>

					<?php if ($dat_link = $report->getFilePath(ReportFile::FILE_TYPE_DAT)): ?>
						<?php echo Html::a(Yii::t('backend.mail', 'DAT'), $dat_link); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>