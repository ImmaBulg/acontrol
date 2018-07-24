<?php
use yii\widgets\DetailView;
use common\helpers\Html;
use common\widgets\Dropdown;

$this->title = Yii::t('backend.view', 'Client - {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_client_menu', ['model' => $model]); ?>
<?php
	echo DetailView::widget([
		'model' => $model,
		'attributes' => [
			'name',
			'email:email',
			'relationUserProfile.address',
			'relationUserProfile.job',
			'relationUserProfile.phone',
			'relationUserProfile.fax',
			'relationUserProfile.comment',
			'old_id',
		],
	]);
?>