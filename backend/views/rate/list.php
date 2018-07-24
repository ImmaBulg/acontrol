<?php

use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use backend\models\searches\models\Rate;

$this->title = Yii::t('backend.view', 'Rates');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Rates');
?>
<?php
	echo Nav::widget([
		'options' => [
			'class' => 'nav-tabs',
		],
		'items' => [
			[
				'label' => Yii::t('backend.view', 'Rates'),
				'url' => ['/rate/list'],
			],
			[
				'label' => Yii::t('backend.view', 'Rate types'),
				'url' => ['/rate-type/list'],
			],
		],
	]);
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('RateManager')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new rate'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/rate/create']),
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
	'id' => 'table-rates-list',
    'options' => [
        'class' => 'table table-striped table-primary',
    ],
	'columns' => [
		'id',
		[
			'attribute' => 'rate_type_id',
			'value' => 'aliasRateType',
			'filter' => Rate::getListRateTypes(),
		],
		[
			'attribute' => 'start_date',
			'format' => 'date',
			'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
		],
		[
			'attribute' => 'end_date',
			'format' => 'date',
			'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
		],
		'fixed_payment:round',
		[
			'attribute' => 'season',
			'value' => 'aliasSeason',
			'filter' => Rate::getListSeasons(),
		],
		[
			'format' => 'raw',
			'value' => function ($model){
				$btn = [];

				if (Yii::$app->user->can('RateManager')) {
					$btn[] = '<div class="btn-group">'.
								Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-danger btn-sm', 'data' => ['toggle' => 'dropdown']]).
								Dropdown::widget([
									'items' => [
										[
											'label' => Yii::t('backend.view', 'Edit'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/rate/edit', 'id' => $model->id]),
										],
										[
											'label' => Yii::t('backend.view', 'Delete'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/rate/delete', 'id' => $model->id]),
											'linkOptions' => [
												'data' => [
													'toggle' => 'confirm',
													'confirm-post' => true,
													'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this rate?'),
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