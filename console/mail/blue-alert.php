<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\models\Task;
?>
<p>
	<?php echo Yii::t('console.mail', 'No new data were obtained from {name} during the latest 72 hours.', [
		'name' => "$site_id - $site_name",
	]); ?>
</p>
<p>
	<?php echo Yii::t('console.mail', 'New {link} have been created.', [
		'link' => Html::a(Yii::t('console.mail', 'helpdesk'), Yii::$app->urlManagerBackend->createAbsoluteUrl(['/task/view', 'id' => $alert['id']])),
	]); ?>
</p>