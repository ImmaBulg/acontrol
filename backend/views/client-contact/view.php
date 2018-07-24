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
	'label' => $model->relationUser->name,
	'url' => ['/client/view', 'id' => $model->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Contacts'),
	'url' => ['/client-contact/list', 'id' => $model->user_id],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_client_contact_menu', ['model' => $model]); ?>
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
