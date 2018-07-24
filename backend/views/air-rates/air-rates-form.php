<?php
use backend\models\forms\FormRate;
use common\helpers\Html;
use common\models\Rate;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use kartik\date\DatePicker;
use yii\helpers\Url;
use yii\widgets\Block;

/* @var $this yii\web\View */
/* @var $model common\models\AirRates */
/* @var $form yii\widgets\ActiveForm */
$this->title = $model->isNewRecord ? Yii::t('backend.view', 'Create a new rate') : 'Update air rate ' . $model->id;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('backend.view', 'Air Rates'),
    'url' => ['/air-rates/list'],
];
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('/libs/dynamicFields.js', [
    'depends' => 'yii\web\JqueryAsset',
    'position' => \dezmont765\yii2bundle\views\MainView::POS_END,
]);
$model_id = $model->id ?? 'new';
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="air-rates-form">
    <br>
    <?php $form = ActiveForm::begin([
                                        'id' => 'air-rate-form',
                                        'enableAjaxValidation' => true,
                                        'enableClientValidation' => false,
                                    ]); ?>
    <fieldset>
        <div class="row">
            <div class="col-lg-6">
                <?php echo $form->field($model, 'rate_type_id', ['selectors' => ['input' => '#sib-air-rates-category']])
                                ->widget(Select2::classname(), [
                                    'data' => Rate::getListRateTypes(),
                                    'options' => [
                                        'options' => FormRate::getListRateTypeAttributes(),
                                        'id' => 'sub-air-rates-category',
                                    ],
                                ]); ?>
                <?php echo $form->field($model, 'season')->widget(Select2::classname(), [
                    'data' => Rate::getListSeasons(),
                ]); ?>
                <?= Html::hiddenInput(\common\models\SubAirRates::_formName() . '[type]',
                                      $model->rate_type_id,
                                      ['id' => 'sub-air-rates-category-hidden']); ?>
                <?= $form->field($model, 'startDate')->dateInput() ?>
                <?php echo $form->field($model, 'endDate')->dateInput() ?>
                <?php echo $form->field($model, 'fixed_payment')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
            </div>
            <div class="col-lg-6">
                <div id="additional-fields">

                </div>
            </div>
        </div>

    </fieldset>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update',
                               ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php Block::begin() ?>
<script>


    var conditionFields = new DynamicFields(
        '#sub-air-rates-category',
        "<?=Url::to(['sub-air-rates/get-fields', 'id' => $model_id])?>",
        '#additional-fields',
        "<?= $model_id?>",
        '#air-rate-form',
        '#sub-air-rates-category-hidden',
        'type'
    );
    $(document).on('click','#add-child-condition', function (e) {
        e.preventDefault();
        conditionFields.getFields("<?=Url::to(['sub-air-rates/add-fields',
                                               'id' => $model_id])?>", '#child-place-position', true);
    });

</script>
<?php Block::end() ?>