<?php
use yii\bootstrap\Nav;
use common\helpers\Html;
use common\widgets\Dropdown;
?>
<div class="page-header">
	<h1><?php echo Yii::t('backend.view', 'Task - {id}', ['id' => $model->id]); ?></h1>
</div>

<?php echo Nav::widget([
	'options' => [
		'id' => 'task-nav',
		'class' => 'nav-tabs',
	],
	'items' => [
		[
			'label' => Yii::t('backend.view', 'View'),
			'url' => ['/task/view', 'id' => $model->id],
		],
		[
			'label' => Yii::t('backend.view', 'Edit'),
			'url' => ['/task/edit', 'id' => $model->id],
			'visible' => (Yii::$app->user->can('TaskManager')),
		],
	],
]); ?>
