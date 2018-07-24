<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use backend\models\searches\models\RuleSingleChannel;

$this->title = Yii::t('backend.view', 'Single channel association rules');
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
	'id' => 'table-rule-single-channels-list',
    'options' => [
        'class' => 'table table-striped table-primary',
    ],
	'columns' => [
		'id',
		[
			'attribute' => 'total_bill_action',
			'value' => 'aliasTotalBillAction',
			'filter' => RuleSingleChannel::getListTotalBillActions(),
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
			'attribute' => 'usage_tenant_name',
			'format' => 'raw',
			'value' => function($model){
				if ($model->relationUsageTenant != null) {
					return $model->relationUsageTenant->name;
				}
			},
		],
		[
			'attribute' => 'use_type',
			'value' => 'aliasUseType',
			'filter' => RuleSingleChannel::getListUseTypes(),
		],
		[
			'attribute' => 'use_percent',
			'format' => 'raw',
			'filter' => RuleSingleChannel::getListUsePercents(),
			'value' => function($model){
				$name = $model->getAliasUsePercent();

				switch ($model->use_percent) {
					case RuleSingleChannel::USE_PERCENT_PARTIAL:
						$name .= " (" .Yii::$app->formatter->asPercentage($model->percent). ")";
						break;

					case RuleSingleChannel::USE_PERCENT_HOUR:
						$name .= " (" .Yii::$app->formatter->asTime($model->from_hours). " - " .Yii::$app->formatter->asTime($model->to_hours). ")";
						break;

					case RuleSingleChannel::USE_PERCENT_RELATIVE_TO_SQUARE_FOOTAGE:
					case RuleSingleChannel::USE_PERCENT_FULL:
					default:
						break;
				}
				
				return $name;
			},
		],
		[
			'attribute' => 'start_date',
			'format' => 'date',
			'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
		],
		[
			'attribute' => 'status',
			'value' => 'aliasStatus',
			'filter' => RuleSingleChannel::getListStatuses(),
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
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/rule-single-channel/edit', 'id' => $model->id]),
										],
										[
											'label' => Yii::t('backend.view', 'Delete'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/rule-single-channel/delete', 'id' => $model->id]),
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