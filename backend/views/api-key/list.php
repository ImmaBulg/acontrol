<?php
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use backend\models\searches\models\ApiKey;

$this->title = Yii::t('backend.view', 'API keys');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'API keys');
?>
    <div class="page-header">
        <?php if(Yii::$app->user->can('ApiKeyManager')): ?>
            <div class="btn-toolbar pull-right">
                <div class="btn-group">
                    <?php echo Html::a(Yii::t('backend.view', 'Add') . ' <span class="caret"></span>', '#',
                                       ['class' => 'dropdown-toggle btn btn-success',
                                        'data' => ['toggle' => 'dropdown']]) .
                               Dropdown::widget([
                                                    'items' => [
                                                        [
                                                            'label' => Yii::t('backend.view', 'Add new API key'),
                                                            'url' => ArrayHelper::merge(Yii::$app->request->get(),
                                                                                        ['/api-key/create']),
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
                                'id' => 'table-api-key-list',
                                'options' => [
                                    'class' => 'table table-striped table-primary',
                                ],
                                'columns' => [
                                    'id',
                                    'api_key',
                                    [
                                        'attribute' => 'status',
                                        'value' => 'aliasStatus',
                                        'filter' => ApiKey::getListStatuses(),
                                    ],
                                    [
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            $btn = [];
                                            if(Yii::$app->user->can('ApiKeyManager')) {
                                                $btn[] = '<div class="btn-group">' .
                                                         Html::a(Yii::t('backend.view', 'Actions') .
                                                                 ' <span class="caret"></span>', '#',
                                                                 ['class' => 'dropdown-toggle btn btn-danger btn-sm',
                                                                  'data' => ['toggle' => 'dropdown']]) .
                                                         Dropdown::widget([
                                                                              'items' => [
                                                                                  [
                                                                                      'label' => Yii::t('backend.view',
                                                                                                        'Edit'),
                                                                                      'url' => ArrayHelper::merge(Yii::$app->request->get(),
                                                                                                                  ['/api-key/edit',
                                                                                                                   'id' => $model->id]),
                                                                                  ],
                                                                                  [
                                                                                      'label' => Yii::t('backend.view',
                                                                                                        'Delete'),
                                                                                      'url' => ArrayHelper::merge(Yii::$app->request->get(),
                                                                                                                  ['/api-key/delete',
                                                                                                                   'id' => $model->id]),
                                                                                      'linkOptions' => [
                                                                                          'data' => [
                                                                                              'toggle' => 'confirm',
                                                                                              'confirm-post' => true,
                                                                                              'confirm-text' => Yii::t('backend.view',
                                                                                                                       'Are you sure you want to delete this API key?'),
                                                                                              'confirm-button' => Yii::t('backend.view',
                                                                                                                         'Delete'),
                                                                                          ],
                                                                                      ],
                                                                                  ],
                                                                              ],
                                                                          ]) .
                                                         '</div>';
                                            }
                                            return '<div class="btn-toolbar pull-right">' . implode('', $btn) .
                                                   '</div>';
                                        }
                                    ],
                                ],
                            ]); ?>