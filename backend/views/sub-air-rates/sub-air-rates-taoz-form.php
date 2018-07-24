<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.04.2017
 * Time: 18:25
 * @var $form PartialActiveForm
 * @var $model \dezmont765\yii2bundle\models\ASubActiveRecord
 */
use dezmont765\yii2bundle\widgets\PartialActiveForm;
use kartik\time\TimePicker;
use yii\helpers\Url;

?>
<div style="position:relative" class="mini-stat-type-2 shadow border-primary">
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, "[$key]type")->dropDownList(\common\models\SubAirRatesTaoz::types()); ?>
            <?= $form->field($model, "[$key]week_part")
                     ->dropDownList(\common\models\SubAirRatesTaoz::week_parts()); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, "[$key]hours_from")->widget(TimePicker::classname(), [
                'pluginOptions' => [
                    'showSeconds' => false,
                    'template' => 'dropdown',
                    'showMeridian' => false,
                    'minuteStep' => 60,
                    'showInputs' => false,
                    'snapToStep' => true
                ],
                'addonOptions' => [
                    'asButton' => true,
                    'buttonOptions' => ['class' => 'btn btn-info'],
                ],
            ])->label('Time from',
                      ['class' => ' control-label text-left']); ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, "[$key]hours_to")->widget(TimePicker::classname(), [
                'pluginOptions' => [
                    'showSeconds' => false,
                    'template' => 'dropdown',
                    'showMeridian' => false,
                    'minuteStep' => 60,
                    'showInputs' => false,
                    'snapToStep' => true
                ],
                'addonOptions' => [
                    'asButton' => true,
                    'buttonOptions' => ['class' => 'btn btn-info'],
                ],
            ])->label('Time to',
                      ['class' => ' control-label text-left']); ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, "[$key]rate")->textInput(); ?>
        </div>
    </div>
    <?= $form->field($model, "[$key]identifier")->textInput(); ?>

    <a id="sub-air-rate-remove-btn-<?= $key ?>" href="#" class="btn btn-danger sub-air-rate-remove-btn"
       style="position:absolute">
        <i class="fa fa-times" style="color:white"></i>
    </a>
    <?php if($model->isNewRecord) : ?>
        <script>
            $(document).on('click', '#sub-air-rate-remove-btn-<?=$key?>', function () {
                $(this).parent().fadeOut(function () {
                    $(this).remove();
                })
            });
        </script>
    <?php else : ?>
        <script>
            $(document).on('click', '#sub-air-rate-remove-btn-<?=$key?>', function () {
                $(this).parent().fadeOut(function () {
                    var self = $(this);
                    $.ajax({
                        url: "<?=Url::to(['sub-air-rates/delete', 'id' => $model->id])?>",
                        success: function () {
                            self.remove();
                        },
                        error: function () {
                            self.fadeIn();
                        }
                    })
                })
            });
        </script>
    <?php endif; ?>
</div>


