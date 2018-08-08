<?php
use backend\models\searches\models\Rate;
use common\widgets\DataColumn;
use common\widgets\GridView;
use yii\bootstrap\Dropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\AirRatesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->title = 'Air rates';
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Air Rates');
?>

<div class="page-header">
    <?php if (Yii::$app->user->can('RateManager')): ?>
        <div class="btn-toolbar pull-right">
            <div class="btn-group">
                <?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
                           Dropdown::widget([
                                                'items' => [
                                                    [
                                                        'label' => Yii::t('backend.view', 'Add new Air rate'),
                                                        'url' => ArrayHelper::merge(Yii::$app->request->get(), ['create']),
                                                    ],
                                                ],
                                            ]); ?>
            </div>
        </div>
    <?php endif; ?>
    <h1><?php echo $this->title; ?></h1>
</div>
<div class="air-rates-index">
<br>


    <?= GridView::widget([
        'id' => 'air-rates-list',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => [
            'class' => 'table table-striped table-primary',
        ],
        'columns' => [

            'id',
            [
                'attribute' => 'rate_name',
                'value' => 'rateTypeName',
            ],
            [
                'attribute' => 'start_date',
                'format' => 'date',
                'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
            ],
            [
                'attribute' => 'endDate',
                'format' => 'date',
                'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
            ],
            'fixed_payment',
            [
                'attribute' => 'season',
                'value' => 'seasonName',
                'filter' => Rate::getListSeasons(),
            ],
            // 'status',
            // 'create_at',
            // 'modified_at',
            // 'created_by',
            // 'modified_by',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}{delete}'
            ],
        ],
    ]); ?>

</div>
