<?php

use yii\helpers\StringHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\DataColumn;
use common\models\Task;
?>
<div class="panel panel-default">
	<div class="panel-heading"><?php echo Yii::t('backend.view', 'Tasks'); ?></div>
	<div class="panel-body">
		<?php echo GridView::widget([
			'dataProvider' => $data_provider,
			'filterModel' => (Yii::$app->user->can('DashboardWidget.TaskWidgetFilter')) ? $filter_model : null,
			'id' => 'table-task-list',
			'layout' => "{items}{pager}",
			'rowOptions' => function ($model, $key, $index, $grid) {
				if ($model->color != null) {
					return ['class' => "color color-{$model->color}"];
				}
			},
			'columns' => [
				[
					'attribute' => 'user_role',
					'filter' => Task::getListRoles(),
					'value' => function($model){
						if ($model->relationUser != null) {
							return $model->relationUser->getAliasRole();
						}
					},
				],
				[
					'attribute' => 'user_name',
					'format' => 'raw',
					'filter' => Task::getListAssignees(),
					'value' => function($model){
						if ($model->relationUser != null) {
							return Html::a($model->relationUser->name, ['/user/view', 'id' => $model->relationUser->id]);
						}
					},
				],
				[
					'attribute' => 'description',
					'format' => 'raw',
					'value' => function($model){
						return Html::a(StringHelper::truncate(Html::encode($model->description), 100), ['/task/view', 'id' => $model->id]);
					}
				],
				[
					'attribute' => 'site_name',
					'format' => 'raw',
					'value' => function($model){
						if ($model->relationSite != null) {
							return Html::a($model->relationSite->name, ['/site/view', 'id' => $model->relationSite->id]);
						}
					},
				],
				[
					'attribute' => 'meter_name',
					'format' => 'raw',
					'value' => function($model){
						if ($model->relationMeter != null) {
							return Html::a($model->relationMeter->name, ['/meter/view', 'id' => $model->relationMeter->id]);
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
					'attribute' => 'type',
					'value' => 'aliasType',
					'filter' => Task::getListTypes(),
				],
				[
					'attribute' => 'urgency',
					'value' => 'aliasUrgency',
					'filter' => Task::getListUrgencies(),
				],
				[
					'attribute' => 'date',
					'format' => 'dateTime',
					'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
					'contentOptions' => ['style' => 'min-width:115px;'],
				],
				[
					'format' => 'raw',
					'value' => function ($model){
						$btn = [];

						if (Yii::$app->user->can('TaskManager')) {
							$btn[] = '<div class="btn-group">'.
										Html::a(Yii::t('backend.view', 'Edit'), ['/task/edit', 'id' => $model->id], [
											'class' => 'btn btn-info btn-sm',
											'target' => '_blank',
											//'data' => ['toggle' => 'modal'],
										]).
									'</div>';
						}

						return '<div class="btn-toolbar pull-right">' .implode('', $btn). '</div>';
					}
				],
			],
		]); ?>
	</div>
	<div class="panel-footer text-center">
		<?php echo Html::a(Yii::t('backend.view', 'View all'), ['/task/list'], ['class' => 'btn btn-info btn-sm']); ?>
	</div>
</div>

