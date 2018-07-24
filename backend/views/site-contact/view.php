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
	'label' => $model->relationSite->relationUser->name,
	'url' => ['/client/view', 'id' => $model->relationSite->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Sites'),
	'url' => ['/client/sites', 'id' => $model->relationSite->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationSite->name,
	'url' => ['/site/view', 'id' => $model->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Contacts'),
	'url' => ['/site-contact/list', 'id' => $model->site_id],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_site_contact_menu', ['model' => $model]); ?>
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
