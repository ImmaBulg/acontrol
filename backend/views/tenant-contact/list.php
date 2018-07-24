<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;

$this->title = Yii::t('backend.view', '{name} / Contacts', ['name' => $model->name]);
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
	'label' => $model->relationSite->name,
	'url' => ['/site/view', 'id' => $model->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Tenants'),
	'url' => ['/site/tenants', 'id' => $model->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->name,
	'url' => ['/tenant/view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Contacts');
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner') || Yii::$app->user->can('TenantManagerSiteOwner') || Yii::$app->user->can('TenantManagerTenantOwner')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new contact'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/tenant-contact/create', 'id' => $model->id]),
						],
					],
				]); ?>
			</div>
		</div>
	<?php endif; ?>
	<h1><?php echo $this->title; ?></h1>
</div>
<?php echo GridView::widget([
	'dataProvider' => $data_provider,
	'filterModel' => $filter_model,
	'id' => 'table-tenant-contacts-list',
    'options' => [
        'class' => 'table table-striped table-primary',
    ],
	'columns' => [
		'id',
		[
			'attribute' => 'name',
			'format' => 'raw',
			'value' => function($model){
				return Html::a($model->name, ['/tenant-contact/view', 'id' => $model->id]);
			},
		],
		[
			'attribute' => 'user_name',
			'format' => 'raw',
			'value' => function($model){
				if (($model_user = $model->relationUser) != null) {
					return Html::a($model_user->name, ['/user/view', 'id' => $model_user->id]);
				}
			},
		],
		'email:email',
		'job',
		'phone',
		'cell_phone',
		'fax',
		[
			'format' => 'raw',
			'value' => function($model){
				$btn[] = '<div class="btn-group">'.
							Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-primary btn-sm', 'data' => ['toggle' => 'dropdown']]).
							Dropdown::widget([
								'items' => [
									[
										'label' => Yii::t('backend.view', 'View'),
										'url' => ['/tenant-contact/view', 'id' => $model->id],
									],
									[
										'label' => Yii::t('backend.view', 'Edit'),
										'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/tenant-contact/edit', 'id' => $model->id]),
										'visible' => (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner') || Yii::$app->user->can('TenantManagerSiteOwner') || Yii::$app->user->can('TenantManagerTenantOwner')),
									],
									[
										'label' => Yii::t('backend.view', 'Delete'),
										'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/tenant-contact/delete', 'id' => $model->id]),
										'visible' => (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner') || Yii::$app->user->can('TenantManagerSiteOwner') || Yii::$app->user->can('TenantManagerTenantOwner')),
										'linkOptions' => [
											'data' => [
												'toggle' => 'confirm',
												'confirm-post' => true,
												'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this contact?'),
												'confirm-button' => Yii::t('backend.view', 'Delete'),
											],
										],
									],
								],
							]).
						'</div>';
				return '<div class="btn-toolbar pull-right">' .implode('', $btn). '</div>';
			}
		],
	],
]); ?>