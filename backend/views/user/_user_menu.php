<?php
use yii\bootstrap\Nav;
use common\helpers\Html;
use common\widgets\Dropdown;
?>
<div class="page-header">
	<h1><?php echo Yii::t('backend.view', 'User - {name}', ['name' => $model->name]); ?></h1>
</div>

<?php echo Nav::widget([
	'options' => [
		'id' => 'user-nav',
		'class' => 'nav-tabs',
	],
	'items' => [
		[
			'label' => Yii::t('backend.view', 'View'),
			'url' => ['/user/view', 'id' => $model->id],
		],
		[
			'label' => Yii::t('backend.view', 'Edit'),
			'url' => ['/user/edit', 'id' => $model->id],
			'visible' => (Yii::$app->user->can('UserManager')),
		],
		[
			'label' => Yii::t('backend.view', 'Change password'),
			'url' => ['/user/password-change', 'id' => $model->id],
			'visible' => (Yii::$app->user->can('UserManager')),
		],
	],
]); ?>
