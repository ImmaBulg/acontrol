<?php
use yii\widgets\DetailView;
use common\helpers\Html;

$this->title = Yii::t('backend.view', 'Meter - {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Meters'),
	'url' => ['/meter/list'],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_meter_menu', ['model' => $model]); ?>
<?php echo DetailView::widget([
	'model' => $model,
	'attributes' => [
		'name',
		[ 
			'attribute' => 'type',
			'value' => $model->getAliasType(),
		],
		[ 
			'attribute' => 'communication_type',
			'value' => $model->getAliasCommunicationType(),
		],
		'start_date:date',
		[ 
			'attribute' => 'status',
			'value' => $model->getAliasStatus(),
		],
	],
]); ?>
