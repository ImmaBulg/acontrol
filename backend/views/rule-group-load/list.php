<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use backend\models\searches\models\RuleGroupLoad;

$this->title = Yii::t('backend.view', 'Group load association rules');
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
	'label' => $model->relationSite->name,
	'url' => ['/site/view', 'id' => $model->relationSite->id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Tenants'),
	'url' => ['/site/tenants', 'id' => $model->relationSite->id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->name,
	'url' => ['/tenant/view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo $this->render('//tenant/_tenant_menu', ['model' => $model]); ?>
<?php echo GridView::widget([
	'dataProvider' => $data_provider,
	'filterModel' => $filter_model,
	'id' => 'table-rule-group-loads-list',
    'options' => [
        'class' => 'table table-striped table-primary',
    ],
	'columns' => [
		'id',
		'name',
		[
			'attribute' => 'total_bill_action',
			'value' => 'aliasTotalBillAction',
			'filter' => RuleGroupLoad::getListTotalBillActions(),
		],
		[
			'attribute' => 'meter_name',
			'format' => 'raw',
			'value' => function($model){
				if ($model->relationMeterChannel != null) {
					return Html::a($model->relationMeterChannel->relationMeter->name, ['/meter/view', 'id' => $model->relationMeterChannel->relationMeter->id]);
				}
			},
		],
		[
			'attribute' => 'channel_name',
			'format' => 'raw',
			'value' => function($model){
				if ($model->relationMeterChannel != null) {
					return $model->relationMeterChannel->getChannelName();
				}
			},
		],
		[
			'attribute' => 'group_name',
			'format' => 'raw',
			'value' => function($model){
				switch ($model->use_type) {
					case RuleGroupLoad::USE_TYPE_SINGLE_TENANT_GROUP_LOAD:
						return $model->relationTenantGroup->name;
						break;
					
					case RuleGroupLoad::USE_TYPE_SINGLE_METER_GROUP_LOAD:
						return $model->relationMeterChannelGroup->name;
						break;

					default:
						break;
				}
			},
		],
		[
			'attribute' => 'use_type',
			'value' => 'aliasUseType',
			'filter' => RuleGroupLoad::getListUseTypes(),
		],
		[
			'attribute' => 'use_percent',
			'value' => 'aliasUsePercent',
			'filter' => RuleGroupLoad::getListUsePercents(),
		],
		[
			'attribute' => 'status',
			'value' => 'aliasStatus',
			'filter' => RuleGroupLoad::getListStatuses(),
		],
		[
			'format' => 'raw',
			'value' => function ($model){
				$btn = [];

				if (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner')) {
					$btn[] = '<div class="btn-group">'.
								Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-primary btn-sm', 'data' => ['toggle' => 'dropdown']]).
								Dropdown::widget([
									'items' => [
										[
											'label' => Yii::t('backend.view', 'Edit'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/rule-group-load/edit', 'id' => $model->id]),
										],
										[
											'label' => Yii::t('backend.view', 'Delete'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/rule-group-load/delete', 'id' => $model->id]),
											'linkOptions' => [
												'data' => [
													'toggle' => 'confirm',
													'confirm-post' => true,
													'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this rule?'),
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