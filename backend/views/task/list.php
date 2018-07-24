<?php

use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use common\components\rbac\Role;
use common\models\User;
use common\widgets\ActiveForm;
use common\components\i18n\LanguageSelector;
use backend\models\forms\FormTasks;
use backend\models\searches\models\Task;

$this->title = Yii::t('backend.view', 'Alerts/Helpdesk');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Alerts/Helpdesk');
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('TaskManager')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php
					$assignee_items = [];

					foreach (User::getListByRole(Role::ROLE_ADMIN) as $user_id => $user_name) {
						if ($user_id == Task::getAssigneeId()) {
							$assignee_items[] = [
								'label' => $user_name,
								'url' => '#',
								'options' => ['class' => 'disabled'],
							];
						} else {
							$assignee_items[] = [
								'label' => $user_name,
								'url' => ['/task/toggle-assignee', 'value' => $user_id],
								'linkOptions' => ['data' => ['method' => 'post']],
							];
						}
					}
				?>
				<?php echo Html::a(Yii::t('backend.view', 'Set default assignee for automatic tasks: {value}', [
					'value' => Task::getAssigneeName(),
				]). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-info', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => $assignee_items,
				]); ?>
			</div>
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new task'),
							'url' => ['/task/create'],
						],
					],
				]); ?>
			</div>
		</div>
	<?php endif; ?>
	<h1><?php echo $this->title; ?></h1>
</div>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-task-filter',
		'method' => 'GET',
		'action' => ['/task/list'],
	]); ?>
		<fieldset>
			<div class="form-group">
				<div class="row">
					<div class="col-lg-3">
						<?php echo Html::label(Yii::t('backend.view', 'From date'), '', ['class' => 'control-label']); ?>
						<?php echo Html::dateInput('from_date', $from_date, [
							'class' => 'form-control',
							'placeholder' => Yii::t('backend.view', 'From date'),
						]); ?>
					</div>
					<div class="col-lg-3">
						<?php echo Html::label(Yii::t('backend.view', 'To date'), '', ['class' => 'control-label']); ?>
						<?php echo Html::dateInput('to_date', $to_date, [
							'class' => 'form-control',
							'placeholder' => Yii::t('backend.view', 'To date'),
						]); ?>
					</div>
					<div class="col-lg-3">
						<?php echo Html::submitInput(Yii::t('backend.view', 'Filter'), ['class' => 'btn btn-primary control-label-offset']); ?>
					</div>
				</div>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>
<?php $form_active = ActiveForm::begin([
	'id' => 'form-tasks',
	'enableClientValidation' => false,
	'method' => 'GET',
	'action' => ['/task/list', 'from_date' => $from_date, 'to_date' => $to_date],
]); ?>
<fieldset>
	<?php if (Yii::$app->user->can('TaskManager')): ?>
		<?php echo $form_active->errorSummary($form_tasks); ?>
		<div class="row">
			<div class="col-lg-3">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Delete selected rows'), ['class' => 'btn btn-warning']); ?>
				<?php echo $form_active->field($form_tasks, 'is_delete')->hiddenInput()->label(false); ?>
			</div>
		</div>
	<?php endif; ?>

		<?php echo GridView::widget([
			'dataProvider' => $data_provider,
			'filterModel' => $filter_model,
			'id' => 'table-task-list',
            'options' => [
                'class' => 'table table-striped table-primary',
            ],
			'rowOptions' => function ($model, $key, $index, $grid) {
				if ($model->color != null) {
					return ['class' => "color color-{$model->color}"];
				}
			},
			'columns' => [
				[
					'class' => 'common\widgets\CheckboxColumn',
					'name' => FormTasks::TASKS_FIELD_NAME,
					'visible' => Yii::$app->user->can('TaskManager'),
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
					'attribute' => 'description',
					'contentOptions' => ['style' => 'width:30%;'],
					'value' => function($model){
						return StringHelper::truncate(Html::encode($model->description), 100);
					}
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
				'ip_address',
				[
					'attribute' => 'date',
					'format' => 'dateTime',
					'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
					'contentOptions' => ['style' => 'min-width:115px;'],
				],
				[
					'format' => 'raw',
					'value' => function ($model){
						$btn[] = '<div class="btn-group">'.
									Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-primary btn-sm', 'data' => ['toggle' => 'dropdown']]).
									Dropdown::widget([
										'items' => [
											[
												'label' => Yii::t('backend.view', 'View'),
												'url' => ['/task/view', 'id' => $model->id],
											],
											[
												'label' => Yii::t('backend.view', 'Edit'),
												'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/task/edit', 'id' => $model->id]),
												'visible' => (Yii::$app->user->can('TaskManager')),
											],
											[
												'label' => Yii::t('backend.view', 'Delete'),
												'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/task/delete', 'id' => $model->id]),
												'visible' => (Yii::$app->user->can('TaskManager')),
												'linkOptions' => [
													'data' => [
														'toggle' => 'confirm',
														'confirm-post' => true,
														'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this task?'),
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
</fieldset>
<?php ActiveForm::end(); ?>