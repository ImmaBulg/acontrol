<?php
use yii\widgets\DetailView;
use common\helpers\Html;
use common\widgets\Dropdown;

$this->title = Yii::t('backend.view', 'Site - {name}', ['name' => $model->name]);
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
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_site_menu', ['model' => $model]); ?>
<?php echo DetailView::widget([
	'model' => $model,
	'attributes' => [
		'name',
		'electric_company_id',
		[ 
			'attribute' => 'to_issue',
			'value' => $model->getAliasToIssue(),
		],
		[ 
			'attribute' => 'rate_type_id',
			'value' => $model->relationSiteBillingSetting->getAliasRateType(),
		],
		[ 
			'attribute' => 'billing_day',
			'value' => $model->relationSiteBillingSetting->getAliasBillingDay(),
		],
		[ 
			'attribute' => 'include_vat',
			'value' => $model->relationSiteBillingSetting->include_vat,
			'format' => 'boolean',
		],
		[ 
			'attribute' => 'comment',
			'value' => $model->relationSiteBillingSetting->comment,
		],
		[ 
			'attribute' => 'fixed_addition_type',
			'value' => $model->relationSiteBillingSetting->getAliasFixedAdditionType(),
		],
		[ 
			'attribute' => 'fixed_addition_load',
			'value' => $model->relationSiteBillingSetting->getAliasFixedAdditionLoad(),
		],
		[ 
			'attribute' => 'fixed_addition_value',
			'value' => $model->relationSiteBillingSetting->fixed_addition_value,
			'format' => 'round',
		],
		[ 
			'attribute' => 'fixed_addition_comment',
			'value' => $model->relationSiteBillingSetting->fixed_addition_comment,
		],
		[ 
			'attribute' => 'square_meters',
			'value' => $model->getSquareMeters(),
		],
		'old_id',
	],
]); ?>
