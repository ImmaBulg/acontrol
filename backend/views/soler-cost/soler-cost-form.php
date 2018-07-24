<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

$this->title = Yii::t('backend.view', 'Create a new soler cost');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('backend.view', 'Soler costs'),
    'url' => ['/soler-cost/list'],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new soler cost');
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="soler-cost-form">
<br>
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'fromDate')->widget(\kartik\date\DatePicker::className()) ?>

    <?= $form->field($model, 'toDate')->widget(\kartik\date\DatePicker::className()) ?>

    <?= $form->field($model, 'cost')->widget(MaskedInput::className(), [
        'name' => 'masked-cost',
        'options' => [
            'class' => 'form-control text-left',
        ],
        'clientOptions' => [
            'alias' => 'currency',
            'digits' => 2,
            'digitsOptional' => false,
            'prefix' => '',
            'radixPoint' => '.',
            'groupSeparator' => ',',
            'autoGroup' => true,
            'removeMaskOnSubmit' => true,
            'autoUnmask' => true
        ],
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
