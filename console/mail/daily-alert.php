<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\models\Task;
?>
<p>
	<strong>
		<?php echo Yii::t('console.mail', 'Hello, {name}', [
		'name' => $user['name'],
	]); ?>
	</strong>
</p>

<table style="width: 100%;color: #000; font:13px Arial; vertical-align: top;">
	<thead>
		<tr>
			<th style="border:1px solid #000;"><?php echo Yii::t('console.mail', 'Site'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('console.mail', 'Contact name'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('console.mail', 'Contact email'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('console.mail', 'Contact phone'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('console.mail', 'Description'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('console.mail', 'Date'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('console.mail', 'Urgency'); ?></th>
			<th style="border:1px solid #000;"><?php echo Yii::t('console.mail', 'Link'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($alerts as $alert): ?>
			<tr>
				<td style="border:1px solid #000;">
					<?php echo $alert['site_name']; ?>
				</td>
				<td style="border:1px solid #000;">
					<?php echo $alert['site_contact_name']; ?>
				</td>
				<td style="border:1px solid #000;">
					<?php echo $alert['site_contact_email']; ?>
				</td>
				<td style="border:1px solid #000;">
					<?php echo $alert['site_contact_phone']; ?>
				</td>
				<td style="border:1px solid #000;">
					<?php echo $alert['description']; ?>
				</td>
				<td style="border:1px solid #000;">
					<?php if ($alert['date'] != null): ?>
						<?php echo Yii::$app->formatter->asDate($alert['date']); ?>
					<?php endif; ?>
				</td>
				<td style="border:1px solid #000;">
					<?php if ($alert['urgency'] != null): ?>
						<?php echo ArrayHelper::getValue(Task::getListUrgencies(), $alert['urgency']); ?>
					<?php endif; ?>
				</td>
				<td style="border:1px solid #000;">
					<?php echo Html::a(Yii::t('console.mail', 'View'), Yii::$app->urlManagerBackend->createAbsoluteUrl(['/task/view', 'id' => $alert['id']])); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>