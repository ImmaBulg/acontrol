<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\ActiveForm;
use backend\widgets\site\SiteMeterTreeWidget;
use backend\models\searches\models\SiteMeterChannel;

$this->title = Yii::t('backend.view', '{name} / Energy tree', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationUser->name,
	'url' => ['/client/view', 'id' => $model->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Sites'),
	'url' => ['/client/sites', 'id' => $model->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->name,
	'url' => ['/site/view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Energy tree');
?>
<div class="page-header">
	<div class="btn-toolbar pull-right">
		<?php if (Yii::$app->user->can('SiteManager') || Yii::$app->user->can('SiteManagerOwner') || Yii::$app->user->can('SiteManagerSiteOwner')): ?>
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new meter'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/site-meter/create', 'id' => $model->id]),
						],
					],
				]); ?>
			</div>
		<?php endif; ?>
	</div>
	<h1><?php echo $this->title; ?></h1>
</div>

<?php if($tree != null): ?>

<?php $form_active = ActiveForm::begin([
	'id' => 'form-site-meter-tree',
	'enableOneProcessSubmit' => true,
]); ?>
	<?php
		$field_tree = Html::getInputId($form, 'tree');
		$this->registerJs("jQuery('#form-site-meter-tree').on('beforeSubmit', function(){
			jQuery(this).find('#$field_tree').val(JSON.stringify(jQuery('#site-meter-tree').nestable('serialize')));
		});");
	?>
	<?php echo $form_active->errorSummary($form); ?>
	<div class="form-group clearfix">
		<?php echo SiteMeterTreeWidget::widget([
			'id' => 'site-meter-tree',
			'tree' => $tree,
		]); ?>
	</div>
	<div class="form-group">
		<div class="row">
			<div class="col-lg-8 col-lg-offset-2">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Save'), ['class' => 'btn btn-success btn-block']); ?>
				<?php echo $form_active->field($form, 'tree')->hiddenInput()->label(false)->error(false); ?>
			</div>
		</div>
	</div>
<?php ActiveForm::end(); ?>

<?php else: ?>
	<div class="empty"><?php echo Yii::t('backend.view', 'No results found.'); ?></div>
<?php endif; ?>

