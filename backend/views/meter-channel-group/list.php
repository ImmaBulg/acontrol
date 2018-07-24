<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;

$this->title = Yii::t('backend.view', 'Channel groups');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Channel groups');
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('MeterChannelGroupManager')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new channel group'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/meter-channel-group/create']),
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
	'id' => 'table-meter-channel-groups-list',
    'options' => [
        'class' => 'table table-striped table-primary',
    ],
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
		[
			'attribute' => 'meter_name',
			'format' => 'raw',
			'value' => function($model){
				return Html::a($model->relationMeter->name, ['/meter/view', 'id' => $model->meter_id]);
			},
		],
		'name',
		[
			'attribute' => 'group_channels',
			'value' => function($model){
				return $model->getRelationMeterChannelGroupItems()->count();
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

				if (Yii::$app->user->can('MeterChannelGroupManager')) {
					$btn[] = '<div class="btn-group">'.
								Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-danger btn-sm', 'data' => ['toggle' => 'dropdown']]).
								Dropdown::widget([
									'items' => [
										[
											'label' => Yii::t('backend.view', 'Edit'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/meter-channel-group/edit', 'id' => $model->id]),
										],
										[
											'label' => Yii::t('backend.view', 'Delete'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/meter-channel-group/delete', 'id' => $model->id]),
											'linkOptions' => [
												'data' => [
													'toggle' => 'confirm',
													'confirm-post' => true,
													'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this channel group?'),
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