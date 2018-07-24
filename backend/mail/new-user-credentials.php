<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\models\Task;
?>
<p>
	<strong>
		<?php echo Yii::t('backend.mail', 'Hello, {name}', [
		'name' => $user->name,
	]); ?>
	</strong>
</p>
<p>
	<?php echo Yii::t('backend.mail', 'New credentials have been created for your account'); ?>
</p>
<p>
	<strong><?php echo Yii::t('backend.mail', 'Username'); ?>: <?php echo $user->nickname; ?></strong>
</p>
<?php if (!empty($password)): ?>
	<p>
		<strong><?php echo Yii::t('backend.mail', 'Password'); ?>: <?php echo $password; ?></strong>
	</p>
<?php endif; ?>