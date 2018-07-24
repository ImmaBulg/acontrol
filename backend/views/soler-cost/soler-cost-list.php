<?php
use yii\bootstrap\Dropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SolerCostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Soler Costs';
?>

<div class="page-header">
    <?php if (Yii::$app->user->can('RateManager')): ?>
        <div class="btn-toolbar pull-right">
            <div class="btn-group">
                <?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
                           Dropdown::widget([
                                                'items' => [
                                                    [
                                                        'label' => Yii::t('backend.view', 'Add new soler cost'),
                                                        'url' => ArrayHelper::merge(Yii::$app->request->get(), ['create']),
                                                    ],
                                                ],
                                            ]); ?>
            </div>
        </div>
    <?php endif; ?>
    <h1><?php echo $this->title; ?></h1>
</div>
<div class="soler-cost-index">
<br>


    <?= GridView::widget([
        'id' => 'soler-cost-list',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
        ['class' => 'yii\grid\CheckboxColumn'],

            'id',
            'from_date',
            'to_date',
            'cost',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}{delete}'
            ],
        ],
    ]); ?>

</div>
