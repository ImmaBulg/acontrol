<?php
use yii\bootstrap\Nav;
use common\helpers\Html;
use common\widgets\Dropdown;
?>
<div class="page-header">
	<h1><?php echo Yii::t('backend.view', 'Soler costs'); ?></h1>
</div>

<?php echo Nav::widget([
	'options' => [
		'id' => 'user-nav',
		'class' => 'nav-tabs',
	],
	'items' => [
		[
			'label' => Yii::t('backend.view', 'Edit'),
			'url' => ['edit', 'id' => $model->id],
		],
	],
]); ?>
