<?php
use yii\widgets\DetailView;
use common\helpers\Html;

$this->title = Yii::t('backend.view', 'Tenant - {name}', ['name' => $model->name]);
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
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_tenant_menu', ['model' => $model]); ?>
<?php echo DetailView::widget([
	'model' => $model,
	'attributes' => [
		'name',
		[ 
			'attribute' => 'type',
			'value' => $model->getAliasType(),
		],
		[ 
			'attribute' => 'to_issue',
			'value' => $model->getAliasToIssue(),
		],
		'square_meters:round',
		[ 
			'attribute' => 'site_footage',
			'format' => 'percentage',
			'value' => $model->getAliasSiteFootage(),
		],
		'entrance_date:date',
		'exit_date:date',
		[ 
			'attribute' => 'rate_type_id',
			'format' => 'raw',
			'value' => $model->getAliasRateType(),
		],
		'relationTenantBillingSetting.comment',
		'relationTenantBillingSetting.fixed_payment:round',
		'relationTenantBillingSetting.id_with_client',
		'relationTenantBillingSetting.accounting_number',
		'relationTenantBillingSetting.billing_content',
	],
]); ?>
