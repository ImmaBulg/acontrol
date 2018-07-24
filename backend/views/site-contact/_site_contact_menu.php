<?php

use yii\bootstrap\Nav;
use common\helpers\Html;
use common\widgets\Dropdown;
?>
<div class="page-header">
	<h1><?php echo Yii::t('backend.view', 'Contact - {name}', ['name' => $model->name]); ?></h1>
</div>

<?php echo Nav::widget([
	'options' => [
		'id' => 'site-contact-nav',
		'class' => 'nav-tabs',
	],
	'items' => [
		[
			'label' => Yii::t('backend.view', 'View'),
			'url' => ['/site-contact/view', 'id' => $model->id],
		],
		[
			'label' => Yii::t('backend.view', 'Edit'),
			'url' => ['/site-contact/edit', 'id' => $model->id],
			'visible' => (Yii::$app->user->can('SiteManager') || Yii::$app->user->can('SiteManagerOwner') || Yii::$app->user->can('SiteManagerSiteOwner')),
		],
	],
]); ?>
