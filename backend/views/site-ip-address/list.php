<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\ActiveForm;
use common\models\SiteIpAddress;

$this->title = Yii::t('backend.view', '{name} / IPs', ['name' => $model->name]);
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
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'IPs');
?>
<div class="page-header">
	<div class="btn-toolbar pull-right">
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
			Dropdown::widget([
				'items' => [
					[
						'label' => Yii::t('backend.view', 'Add new IP'),
						'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/site-ip-address/create', 'id' => $model->id]),
					],
				],
			]); ?>
		</div>
	</div>
	<h1><?php echo $this->title; ?></h1>
</div>
<?php echo GridView::widget([
	'dataProvider' => $data_provider,
	'filterModel' => $filter_model,
	'id' => 'table-site-ip-address-list',
    'options' => [
        'class' => 'table table-striped table-primary',
    ],
	'columns' => [
		'id',
		[
			'attribute' => 'ip_address',
			'format' => 'raw',
			'value' => function($model){
				return Html::a($model->ip_address, ['/site-ip-address/view', 'id' => $model->id]);
			},
		],
		[
			'attribute' => 'is_main',
			'filter' => SiteIpAddress::getListYesNo(),
			'value' => function($model){
				return ArrayHelper::getValue(SiteIpAddress::getListYesNo(), $model->is_main);
			},
		],
		[
			'attribute' => 'status',
			'value' => 'aliasStatus',
			'filter' => SiteIpAddress::getListStatuses(),
		],
		[
			'format' => 'raw',
			'value' => function($model){
				$btn[] = '<div class="btn-group">'.
							Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-primary btn-sm', 'data' => ['toggle' => 'dropdown']]).
							Dropdown::widget([
								'items' => [
									[
										'label' => Yii::t('backend.view', 'Edit'),
										'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/site-ip-address/edit', 'id' => $model->id]),
									],
									[
										'label' => Yii::t('backend.view', 'Delete'),
										'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/site-ip-address/delete', 'id' => $model->id]),
										'linkOptions' => [
											'data' => [
												'toggle' => 'confirm',
												'confirm-post' => true,
												'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this IP?'),
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