<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use common\widgets\Select2;
use common\models\Tenant;
use common\models\MeterChannel;
use common\widgets\ActiveForm;
use backend\models\forms\FormMeters;
use backend\models\searches\models\Meter;

$this->title = Yii::t('backend.view', 'Meters');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Meters');
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('MeterManager')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new meter'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/meter/create']),
						],
					],
				]); ?>
			</div>
		</div>
	<?php endif; ?>
	<h1><?php echo $this->title; ?></h1>
</div>
<?php $form_active = ActiveForm::begin([
	'id' => 'form-meters-edit',
	'enableClientValidation' => false,
	'method' => 'GET',
	'action' => ['/meter/list'],
]); ?>
<fieldset>
	<?php if (Yii::$app->user->can('MeterManager')): ?>
		<?php echo $form_active->errorSummary($form_meters); ?>
		<div class="row">
			<div class="col-lg-3">
				<?php echo $form_active->field($form_meters, 'site_id')->widget(Select2::classname(), [
					'data' => ArrayHelper::merge(['' => Yii::t('backend.view', 'Not set')], Tenant::getListSites()),
				])->error(false); ?>
			</div>
			<div class="col-lg-3">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-primary control-label-offset']); ?>
			</div>
		</div>
	<?php endif; ?>
	<?php echo GridView::widget([
		'dataProvider' => $data_provider,
		'filterModel' => $filter_model,
		'id' => 'table-meters-list',
        'options' => [
            'class' => 'table table-striped table-primary',
        ],
		'columns' => [
			[
				'class' => 'common\widgets\CheckboxColumn',
				'name' => FormMeters::METERS_FIELD_NAME,
				'visible' => Yii::$app->user->can('MeterManager'),
			],
			'id',
			[
				'attribute' => 'name',
				'format' => 'raw',
				'value' => function($model){
					return Html::a($model->name, ['/meter/view', 'id' => $model->id]);
				},
			],
			[
				'attribute' => 'type_name',
				'value' => 'aliasType',
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
				'attribute' => 'start_date',
				'format' => 'date',
				'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
			],
			[
				'attribute' => 'status',
				'value' => 'aliasStatus',
				'filter' => Meter::getListStatuses(),
			],
            [
                'attribute' => 'type',
                'value'     => 'aliasCategories',
                'filter'    => Meter::getMeterCategories(),
            ],
			[
				'format' => 'raw',
				'value' => function ($model){
					$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Channels ({count})', [
						'count' => $model->getRelationMeterChannels()->andWhere(['status' => MeterChannel::STATUS_ACTIVE])->count() * $model->relationMeterType->phases,
					]), ['/meter-channel/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']). '</div>';
					
					if (Yii::$app->user->can('MeterManager')) {
						$btn[] = '<div class="btn-group">'.
									Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-danger btn-sm', 'data' => ['toggle' => 'dropdown']]).
									Dropdown::widget([
										'items' => [
											[
												'label' => Yii::t('backend.view', 'Edit'),
												'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/meter/edit', 'id' => $model->id]),
											],
											[
												'label' => Yii::t('backend.view', 'Delete'),
												'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/meter/delete', 'id' => $model->id]),
												'linkOptions' => [
													'data' => [
														'toggle' => 'confirm',
														'confirm-post' => true,
														'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this meter?'),
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
</fieldset>
<?php ActiveForm::end(); ?>