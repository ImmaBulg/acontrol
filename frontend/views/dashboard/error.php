<?php

use common\helpers\Html;

$this->title = $name;
?>
<div id="error-page">
	<h1><?php echo $code; ?></h1>
	<p>
		<?php echo nl2br(Html::encode($message)); ?> 
		<?php echo Yii::t('frontend.view', 'Go back to {link}.', ['link' => Html::a(Yii::t('frontend.view', 'homepage'), Yii::$app->homeUrl)]); ?>
	</p>
</div>