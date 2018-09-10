<?php
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\Holiday;
use backend\models\forms\FormHoliday;

$this->title = Yii::t('backend.view', 'Holiday - {id}', ['id' => $model->id]);
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('backend.view', 'Holidays'),
    'url' => ['/holiday/list'],
];
$this->params['breadcrumbs'][] = $model->id;
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
    <?php $form_active = ActiveForm::begin([
        'id' => 'form-rate-edit',
        'enableOneProcessSubmit' => true,
    ]); ?>
    <fieldset>
        <div class="row">
            <div class="col-lg-6">
                <?php echo $form_active->field($form, 'date')->dateInput(); ?>
                <?php echo $form_active->field($form, 'name')->textInput(); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
        </div>
    </fieldset>
    <?php ActiveForm::end(); ?>
</div>