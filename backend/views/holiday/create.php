<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.09.2018
 * Time: 16:14
 */

use common\helpers\Html;
use common\widgets\ActiveForm;

$this->title = Yii::t('backend.view', 'Create a new holiday');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('backend.view', 'Holidays'),
    'url' => ['/holiday/list'],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new holiday');
?>

<h1 class="page-header"><?php echo $this->title; ?></h1>

<div class="well">
    <?php $form_active = ActiveForm::begin([
        'id' => 'form-holiday-create',
        'enableOneProcessSubmit' => true,
    ]); ?>
    <fieldset>
        <div class="row">
            <div class="col-lg-6">
                <?php echo $form_active->field($form, 'date')->dateInput([
                    'format' => 'Y-m-d',
                ]); ?>
                <?php echo $form_active->field($form, 'name')->textInput(); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
        </div>
    </fieldset>
    <?php ActiveForm::end(); ?>
</div>
