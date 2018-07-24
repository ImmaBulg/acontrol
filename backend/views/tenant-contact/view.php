<?php
use yii\widgets\DetailView;
use common\helpers\Html;
use common\widgets\Dropdown;

$this->title = Yii::t('backend.view', 'Contact - {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationTenant->relationUser->name,
	'url' => ['/client/view', 'id' => $model->relationTenant->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Sites'),
	'url' => ['/client/sites', 'id' => $model->relationTenant->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationTenant->relationSite->name,
	'url' => ['/site/view', 'id' => $model->relationTenant->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Tenants'),
	'url' => ['/site/tenants', 'id' => $model->relationTenant->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationTenant->name,
	'url' => ['/tenant/view', 'id' => $model->tenant_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Contacts'),
	'url' => ['/tenant-contact/list', 'id' => $model->tenant_id],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_tenant_contact_menu', ['model' => $model]); ?>
<?php echo DetailView::widget([
	'model' => $model,
	'attributes' => [
		'name',
		'email:email',
		'address',
		'job',
		'phone',
		'cell_phone',
		'fax',
		'comment',
	],
]); ?>
