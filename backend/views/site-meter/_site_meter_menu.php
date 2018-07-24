<?php

use yii\bootstrap\Nav;
use common\helpers\Html;
use common\widgets\Dropdown;
?>
<div class="page-header">
	<h1><?php echo Yii::t('backend.view', 'Meter - {name}', ['name' => $model->name]); ?></h1>
</div>

<?php echo Nav::widget([
	'options' => [
		'id' => 'site-meter-nav',
		'class' => 'nav-tabs',
	],
	'items' => [
		[
			'label' => Yii::t('backend.view', 'Edit'),
			'url' => ['/site-meter/edit', 'id' => $model->id],
			'visible' => (Yii::$app->user->can('SiteManager') || Yii::$app->user->can('SiteManagerOwner') || Yii::$app->user->can('SiteManagerSiteOwner')),
		],
	],
]); ?>
