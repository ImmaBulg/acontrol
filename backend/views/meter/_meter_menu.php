<?php
use yii\bootstrap\Nav;
use common\helpers\Html;
?>
<h1 class="page-header"><?php echo Yii::t('backend.view', 'Meter - {name}', ['name' => $model->name]); ?></h1>

<?php echo Nav::widget([
	'options' => [
		'id' => 'tenant-nav',
		'class' => 'nav-tabs',
	],
	'items' => [
		[
			'label' => Yii::t('backend.view', 'View'),
			'url' => ['/meter/view', 'id' => $model->id],
		],
		[
			'label' => Yii::t('backend.view', 'Edit'),
			'url' => ['/meter/edit', 'id' => $model->id],
			'visible' => (Yii::$app->user->can('MeterManager')),
		],
		[
			'label' => Yii::t('backend.view', 'Meter channels'),
			'url' => ['/meter-channel/list', 'id' => $model->id],
		],
		[
			'label' => Yii::t('backend.view', 'Import data'),
			'url' => ['/meter/import-data', 'id' => $model->id],
			'visible' => (Yii::$app->user->can('MeterManager')),
		],
	],
]); ?>
