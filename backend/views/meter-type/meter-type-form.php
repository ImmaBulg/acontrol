<?php
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\MeterType;
use common\widgets\Select2;

$this->title = $this->title =
    $form->scenario == \backend\models\forms\FormMeterType::SCENARIO_CREATE ?
        Yii::t('backend.view', 'Create a new meter type') : 'Update meter type ' . $form->name;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('backend.view', 'Meter types'),
    'url' => ['/meter-type/list'],
];
$this->params['breadcrumbs'][] = $this->title;
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
    <?php $form_active = ActiveForm::begin([
                                               'id' => 'form-meter-type-create',
                                               'enableOneProcessSubmit' => true,
                                           ]); ?>
    <fieldset>
        <div class="row">
            <div class="col-lg-6">
                <?php echo $form_active->field($form, 'name')->textInput(); ?>
                <?php echo $form_active->field($form, 'channels')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
                <?php echo $form_active->field($form, 'phases')->widget(Select2::classname(), [
                    'data' => MeterType::getListPhases(),
                ]); ?>
                <?php echo $form_active->field($form, 'type')->widget(Select2::classname(), [
                    'data' => MeterType::getMeterCategories(),
                ]); ?>
                <?php echo $form_active->field($form, 'is_divide_by_1000')->checkbox(); ?>
                <?php echo $form_active->field($form, 'modbus')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo Html::submitInput($form->scenario == \backend\models\forms\FormMeterType::SCENARIO_CREATE ?
                                             Yii::t('backend.view', 'Create') :
                                             Yii::t('backend.view', 'Update')
                , ['class' => 'btn btn-success']); ?>
        </div>
    </fieldset>
    <?php ActiveForm::end(); ?>
</div>