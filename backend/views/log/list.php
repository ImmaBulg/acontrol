<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use backend\models\searches\models\Log;

$this->title = Yii::t('backend.view', 'System log');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'System log');
?>
<div class="page-header">
	<div class="btn-toolbar pull-right">
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Flush cache'), ['/dashboard/flush-cache'], ['class' => 'btn btn-default', 'data' => ['method' => 'post']]); ?>
		</div>
	</div>
	<h1><?php echo $this->title; ?></h1>
</div>
<?php echo GridView::widget([
	'dataProvider' => $data_provider,
	'filterModel' => $filter_model,
	'id' => 'table-log-list',
    'options' => [
        'class' => 'table table-striped table-primary',
    ],
	'columns' => [
		'id',
		[
			'attribute' => 'type',
			'value' => 'aliasType',
			'filter' => Log::getListTypes(),
		],
		[
			'attribute' => 'action',
			'value' => 'aliasAction',
		],
		[
			'attribute' => 'user_name',
			'value' => 'relationUserCreator.name',
		],
		'ip_address',
		[
			'attribute' => 'created_at',
			'format' => 'dateTime',
			'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
		],
	],
]); ?>