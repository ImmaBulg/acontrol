<?php

use yii\bootstrap\Nav;
use common\helpers\Html;
use common\widgets\Dropdown;
?>
<div class="page-header">
	<h1><?php echo Yii::t('backend.view', 'IP - {name}', ['name' => $model->ip_address]); ?></h1>
</div>

<?php echo Nav::widget([
	'options' => [
		'id' => 'site-ip-address-nav',
		'class' => 'nav-tabs',
	],
	'items' => [
		[
			'label' => Yii::t('backend.view', 'Edit'),
			'url' => ['/site-ip-address/edit', 'id' => $model->id],
		],
	],
]); ?>
