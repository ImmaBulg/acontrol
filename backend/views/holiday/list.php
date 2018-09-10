<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.09.2018
 * Time: 15:40
 */

use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use backend\models\searches\models\Holiday;

$this->title = Yii::t('backend.view', 'Holidays');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Holidays');
?>

<div class="page-header">
    <?php if (Yii::$app->user->can('HolidayManager')): ?>
        <div class="btn-toolbar pull-right">
            <div class="btn-group">
                <?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
                    Dropdown::widget([
                        'items' => [
                            [
                                'label' => Yii::t('backend.view', 'Add new holiday'),
                                'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/holiday/create']),
                            ],
                        ],
                    ]); ?>
            </div>
        </div>
    <?php endif; ?>
    <h1><?php echo $this->title; ?></h1>
</div>
<?php echo GridView::widget([
    'dataProvider' => $data_provider,
    'filterModel' => $filter_model,
    'id' => 'table-rates-list',
    'options' => [
        'class' => 'table table-striped table-primary',
    ],
    'columns' => [
        'id',
        [
            'attribute' => 'date',
            'format' => 'date',
            'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
        ],
        'name',
        [
            'format' => 'raw',
            'value' => function ($model){
                $btn = [];

                if (Yii::$app->user->can('HolidayManager')) {
                    $btn[] = '<div class="btn-group">'.
                        Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-danger btn-sm', 'data' => ['toggle' => 'dropdown']]).
                        Dropdown::widget([
                            'items' => [
                                [
                                    'label' => Yii::t('backend.view', 'Edit'),
                                    'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/holiday/edit', 'id' => $model->id]),
                                ],
                                [
                                    'label' => Yii::t('backend.view', 'Delete'),
                                    'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/holiday/delete', 'id' => $model->id]),
                                    'linkOptions' => [
                                        'data' => [
                                            'toggle' => 'confirm',
                                            'confirm-post' => true,
                                            'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this holiday?'),
                                            'confirm-button' => Yii::t('backend.view', 'Delete'),
                                        ],
                                    ],
                                ],
                            ],
                        ]).
                        '</div>';
                }

                return '<div class="btn-toolbar pull-right">' .implode('', $btn). '</div>';
            }
        ],
    ],
]); ?>

