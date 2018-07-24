<?php
use common\helpers\Html;
use common\models\ElectricityMeterRawData;

?>
<div class="<?php echo $model['class']; ?>">
    <div class="row">
        <div class="col-lg-2 list-view-column">
            <div class="vertical-align-table">
                <div class="vertical-align-cell">
                    <?php echo Yii::$app->formatter->asDatetime($model['date']); ?>
                    <?php echo $form_active->field($form, 'readings[' .$model['date']. '][datetime]')->hiddenInput(['value'=> $model['datetime']])->label(false); ?>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row">
                <div class="col-lg-2">
                    <?php echo $form_active->field($form, 'readings[' . $model['date'] . '][kilowatt_hour]', [
                        'options' => ['class' => 'list-view-column'],
                    ])->textInput(['allow_only' => Html::TYPE_NUMBER,
                                   'class' => 'form-control ' . $model['input_class']])
                                           ->label(false)->error(false); ?>
                </div>
                <div class="col-lg-2">
                    <?php echo $form_active->field($form, 'readings[' . $model['date'] . '][cubic_meter]', [
                        'options' => ['class' => 'list-view-column'],
                    ])->textInput(['allow_only' => Html::TYPE_NUMBER])
                                           ->label(false)->error(false); ?>
                </div>
                <div class="col-lg-2">
                    <?php echo $form_active->field($form, 'readings[' . $model['date'] . '][kilowatt]', [
                        'options' => ['class' => 'list-view-column'],
                    ])->textInput(['allow_only' => Html::TYPE_NUMBER,
                                   'class' => 'form-control ' . $model['input_class']])
                                           ->label(false)->error(false); ?>
                </div>
                <div class="col-lg-2">
                    <?php echo $form_active->field($form, 'readings[' . $model['date'] . '][cubic_meter_hour]', [
                        'options' => ['class' => 'list-view-column'],
                    ])->textInput(['allow_only' => Html::TYPE_NUMBER,
                                   'class' => 'form-control ' . $model['input_class']])
                                           ->label(false)->error(false); ?>
                </div>
                <div class="col-lg-2">
                    <?php echo $form_active->field($form, 'readings[' . $model['date'] . '][incoming_temp]', [
                        'options' => ['class' => 'list-view-column'],
                    ])->textInput(['allow_only' => Html::TYPE_NUMBER,
                                   'class' => 'form-control ' . $model['input_class']])
                                           ->label(false)->error(false); ?>
                </div>
                <div class="col-lg-2">
                    <?php echo $form_active->field($form, 'readings[' . $model['date'] . '][outgoing_temp]', [
                        'options' => ['class' => 'list-view-column'],
                    ])->textInput(['allow_only' => Html::TYPE_NUMBER,
                                   'class' => 'form-control ' . $model['input_class']])
                                           ->label(false)->error(false); ?>
                </div>

            </div>
        </div>
        <?php if(isset($model['id'])): ?>
            <div class="col-lg-1 list-view-column">
                <div class="vertical-align-table">
                    <div class="vertical-align-cell">
                        <?php echo Html::a(Yii::t('backend.view', 'Delete'),
                                           ['/meter-raw-data/delete-row', 'id' => $model['id'], 'type' => \common\models\Meter::TYPE_AIR], [
                                               'class' => 'btn btn-default btn-sm',
                                               'data' => [
                                                   'toggle' => 'confirm',
                                                   'confirm-post' => true,
                                                   'confirm-text' => Yii::t('backend.view',
                                                                            'Are you sure you want to delete these data?'),
                                                   'confirm-button' => Yii::t('backend.view', 'Delete'),
                                               ],
                                           ]); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>


</div>
