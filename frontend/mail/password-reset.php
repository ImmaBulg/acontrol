<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
?>
<p>
	<strong>
		<?php echo Yii::t('frontend.mail', 'Hello, {name}', [
		'name' => $user->name,
	]); ?>
	</strong>
</p>
<p>
	<?php echo Yii::t('frontend.mail', "We heard you need a new password. Please try to login using credentials below."); ?>
</p>
<p>
	<strong><?php echo Yii::t('frontend.mail', 'Username'); ?>: <?php echo $user->nickname; ?></strong>
</p>
<p>
	<strong><?php echo Yii::t('frontend.mail', 'Password'); ?>: <?php echo $password; ?></strong>
</p>