<?php

use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\models\Rate;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use backend\models\searches\models\RateType;

$this->title = Yii::t('backend.view', 'Rate types');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Rate types');
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
							'label' => Yii::t('backend.view', 'Add new rate type'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/rate-type/create']),
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
	'id' => 'table-rate-types-list',
    'options' => [
        'class' => 'table table-striped table-primary',
    ],
	'columns' => [
		'id',
		[
			'attribute' => 'name',
			'value' => function($model){
				return $model->getName();
			},
		],
		[
			'attribute' => 'type',
			'value' => 'aliasType',
			'filter' => RateType::getListTypes(),
		],
		[
			'attribute' => 'level',
			'value' => 'aliasLevel',
			'filter' => RateType::getListLevels(),
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
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/rate-type/edit', 'id' => $model->id]),
										],
										[
											'label' => Yii::t('backend.view', 'Delete'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/rate-type/delete', 'id' => $model->id]),
											'linkOptions' => [
												'data' => [
													'toggle' => 'confirm',
													'confirm-post' => true,
													'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this rate type?'),
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