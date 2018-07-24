<?php
use yii\widgets\DetailView;
use common\helpers\Html;
use common\widgets\Dropdown;

$this->title = Yii::t('backend.view', 'User - {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Users'),
	'url' => ['/user/list'],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_user_menu', ['model' => $model]); ?>
<?php
	echo DetailView::widget([
		'model' => $model,
		'attributes' => [
			'name',
			'nickname',
			[ 
				'attribute' => 'role',
				'value' => $model->getAliasRole(),
			],
			'email:email',
			[ 
				'attribute' => 'status',
				'value' => $model->getAliasStatus(),
			],
			'relationUserProfile.address',
			'relationUserProfile.job',
			'relationUserProfile.phone',
			'relationUserProfile.fax',
			'relationUserProfile.comment',
		],
	]);
?>