<?php

use yii\helpers\Url;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\Meter;
use common\models\Tenant;
use common\models\Site;
use common\widgets\Select2;
use common\widgets\DepDrop;

/**
 * @var \backend\models\forms\FormMeter $form
 */
$this->title =  Yii::t('backend.view', $model->isNewRecord ? 'Create a new meter' :  'Meter - {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('backend.view', 'Meters'),
    'url' => ['/meter/list'],
];
$this->params['breadcrumbs'][] = $model->isNewRecord ?  Yii::t('backend.view', 'Create a new meter') : $model->name ;
if($model->isNewRecord) : ?>
    <h1 class="page-header"><?php echo $this->title; ?></h1>
<?else : ?>
    <?php echo $this->render('_meter_menu', ['model' => $model]); ?>
<?endif?>
<div class="well">
    <?php $form_active = ActiveForm::begin([
                                               'id' => 'form-meter-create',
                                               'enableOneProcessSubmit' => true,
                                           ]); ?>
    <fieldset>
        <div class="row">
            <div class="col-lg-6">
                <?php echo $form_active->field($form, 'name')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
                <?php echo $form_active->field($form, 'type')->widget(Select2::classname(), [
                    'data' => Meter::getMeterCategories(),
                    'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
                ]); ?>

                <?php echo $form_active->field($form, 'type_id')->widget(DepDrop::classname(), [
                    'type' => DepDrop::TYPE_SELECT2,
                    'data' => Meter::getListTypesByTypeId($form->type),
                    'pluginOptions' => [
                        'depends' => [Html::getInputId($form, 'type')],
                        'url' => Url::to(['form-dependent/meter-types']),
                        'placeholder' => false,
                        'loadingText' => Yii::t('backend.view', 'Loading'),
                    ],
                ]); ?>
                <?php echo $form_active->field($form, 'site_id')->widget(Select2::classname(), [
                    'data' => Tenant::getListSites(),
                    'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
                ]); ?>
                <?php echo $form_active->field($form, 'ip_address')->widget(DepDrop::classname(), [
                    'type' => DepDrop::TYPE_SELECT2,
                    'pluginOptions' => [
                        'depends' => [Html::getInputId($form, 'site_id')],
                        'url' => Url::to([
                                             '/form-dependent/site-ip-addresses',
                                         ]),
                        'initialize' => true,
                        'placeholder' => false,
                        'loadingText' => Yii::t('backend.view', 'Loading'),
                    ],
                ]); ?>
                <?php echo $form_active->field($form, 'breaker_name'); ?>
                <?php echo $form_active->field($form, 'status')->widget(Select2::classname(), [
                    'data' => Meter::getListStatuses(),
                ]); ?>
            </div>
            <div class="col-lg-6">
                <?php echo $form_active->field($form, 'communication_type')->widget(Select2::classname(), [
                    'data' => Meter::getListCommunicationTypes(),
                ]); ?>
                <?php echo $form_active->field($form, 'data_usage_method')->widget(Select2::classname(), [
                    'data' => Meter::getListDataUsageMethods(),
                ]); ?>
                <?php echo $form_active->field($form, 'physical_location')->textInput(); ?>
                <?php echo $form_active->field($form, 'start_date')->dateInput(); ?>
                <?php echo $form_active->field($form, 'is_main')->checkbox();?>
            </div>
        </div>
        <div class="form-group">
            <?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
        </div>
    </fieldset>
    <?php ActiveForm::end(); ?>
</div>