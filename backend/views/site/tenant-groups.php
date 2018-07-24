<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;

$this->title = Yii::t('backend.view', '{name} / Tenant groups', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationUser->name,
	'url' => ['/client/view', 'id' => $model->relationUser->id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Sites'),
	'url' => ['/client/sites', 'id' => $model->relationUser->id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->name,
	'url' => ['/site/view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Tenant groups');
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('SiteManager') || Yii::$app->user->can('SiteManagerOwner') || Yii::$app->user->can('SiteManagerSiteOwner')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new tenant group'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/site/tenant-group-create', 'id' => $model->id]),
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
	'id' => 'table-tenant-groups-list',
	'columns' => [
		'id',
		[
			'attribute' => 'user_name',
			'format' => 'raw',
			'value' => function($model){
				return Html::a($model->relationUser->name, ['/client/view', 'id' => $model->user_id]);
			},
		],
		[
			'attribute' => 'site_name',
			'format' => 'raw',
			'value' => function($model){
				return Html::a($model->relationSite->name, ['/site/view', 'id' => $model->site_id]);
			},
		],
		'name',
		[
			'attribute' => 'group_tenants',
			'value' => function($model){
				return $model->getRelationTenantGroupItems()->count();
			},
		],
		[
			'attribute' => 'created_at',
			'format' => 'date',
			'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
		],
		[
			'format' => 'raw',
			'value' => function ($model){
				$btn = [];

				if (Yii::$app->user->can('TenantGroupManager') || Yii::$app->user->can('TenantGroupManagerOwner') || Yii::$app->user->can('TenantGroupManagerSiteOwner')) {
					$btn[] = '<div class="btn-group">'.
								Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-primary btn-sm', 'data' => ['toggle' => 'dropdown']]).
								Dropdown::widget([
									'items' => [
										[
											'label' => Yii::t('backend.view', 'Edit'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/tenant-group/edit', 'id' => $model->id]),
										],
										[
											'label' => Yii::t('backend.view', 'Delete'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/tenant-group/delete', 'id' => $model->id]),
											'linkOptions' => [
												'data' => [
													'toggle' => 'confirm',
													'confirm-post' => true,
													'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this tenant group?'),
													'confirm-button' => Yii::t('backend.view', 'Delete'),
												],
											],
										],
									],
								]).
							'</div>';					
				}

				return '<div class="btn-toolbar pull-right">' .implode('', $btn). '</div>';
			}
		],
	],
]); ?>