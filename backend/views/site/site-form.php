<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 18.07.2017
 * Time: 18:28
 * @var $model Site
 * @var $form \backend\models\forms\FormSite
 */
use common\helpers\Html;
use common\models\Rate;
use common\models\Report;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\User;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use dezmont765\yii2bundle\widgets\AutoRegistrableScriptBlock;
use kartik\widgets\TimePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

$is_create = !isset($model);
$this->title = $is_create ? Yii::t('backend.view', 'Create a new site') :
    Yii::t('backend.view', 'Site - {name}', ['name' => $model->name]);
if ($is_create) :
    $this->params['breadcrumbs'][] = [
        'label' => Yii::t('backend.view', 'Sites'),
        'url' => ['/site/list'],
    ];
    $this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new site');
else :
    $this->params['breadcrumbs'][] = [
        'label' => Yii::t('backend.view', 'Clients'),
        'url' => ['/client/list'],
    ];
    $this->params['breadcrumbs'][] = [
        'label' => $model->relationUser->name,
        'url' => ['/client/view', 'id' => $model->relationUser->id],
    ];
    $this->params['breadcrumbs'][] = [
        'label' => Yii::t('backend.view', 'Sites'),
        'url' => ['/client/sites', 'id' => $model->relationUser->id],
    ];
endif;
?>
    <h1 class="page-header"><?php echo $this->title; ?></h1>

    <div class="well">

        <?php $form_active = ActiveForm::begin([
            'id' => 'form-site-create',
            'enableOneProcessSubmit' => true,
        ]); ?>
        <fieldset>
            <div class="row">
                <div class="col-lg-6">
                    <?php echo $form_active->field($form, 'user_id')->widget(Select2::classname(), [
                        'data' => User::getListClients(),
                    ]); ?>
                    <?php echo $form_active->field($form, 'name')->textInput(); ?>
                    <?php echo $form_active->field($form, 'electric_company_id')->textInput(); ?>
                    <?php echo $form_active->field($form, 'to_issue')->widget(Select2::classname(), [
                        'data' => Site::getListToIssues(),
                    ]); ?>
                    <?php echo $form_active->field($form, 'rate_type_id')->widget(Select2::classname(), [
                        'data' => Rate::getListRateTypes(),
                    ]); ?>
                    <?php echo $form_active->field($form, 'billing_day')->widget(Select2::classname(), [
                        'data' => SiteBillingSetting::getListBillingDays(),
                    ]); ?>
                    <?php echo $form_active->field($form, 'include_vat')->checkbox(); ?>
                    <?php echo $form_active->field($form, 'comment')->textArea(); ?>
                    <?php echo $form_active->field($form, 'manual_cop')->textInput(); ?>
                    <?php echo $form_active->field($form, 'manual_cop_shefel')->textInput(); ?>
                    <?php echo $form_active->field($form, 'manual_cop_geva')->textInput(); ?>
                    <?php echo $form_active->field($form, 'manual_cop_pisga')->textInput(); ?>
                </div>
                <div class="col-lg-6">
                    <?php echo $form_active->field($form, 'fixed_payment')
                        ->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
                    <?php echo $form_active->field($form, 'fixed_addition_type')->widget(Select2::classname(), [
                        'data' => ArrayHelper::merge([
                            null => Yii::t('backend.view', 'Not set'),
                        ], SiteBillingSetting::getListFixedAdditionTypes()),
                    ]); ?>
                    <?php echo $form_active->field($form, 'fixed_addition_load')->widget(Select2::classname(), [
                        'data' => ArrayHelper::merge([
                            null => Yii::t('backend.view', 'Not set'),
                        ], SiteBillingSetting::getListFixedAdditionLoads()),
                    ]); ?>
                    <?php echo $form_active->field($form, 'fixed_addition_value')
                        ->textInput(/*['allow_only' => Html::TYPE_NUMBER]*/); ?>
                    <?php echo $form_active->field($form, 'fixed_addition_comment')->textArea(); ?>
                    <?php echo $form_active->field($form, 'auto_issue_reports')
                        ->checkboxList(Report::getAutoIssueListTypes()); ?>
                    <?php echo $form_active->field($form, 'power_factor_visibility')->inline()
                        ->radioList(Site::getListPowerFactors())->error(false); ?>
                    <?php echo $form_active->field($form, 'irregular_hours_from')->widget(TimePicker::classname(), [
                        'pluginOptions' => [
                            'showSeconds' => false,
                            'template' => 'dropdown',
                            'showMeridian' => false,
                            'minuteStep' => 15,
                            'showInputs' => false,
                            'defaultTime' => false,
                        ],
                        'addonOptions' => [
                            'asButton' => true,
                            'buttonOptions' => ['class' => 'btn btn-info'],
                        ]]); ?>
                    <?php echo $form_active->field($form, 'irregular_hours_to')->widget(TimePicker::classname(), [
                        'pluginOptions' => [
                            'showSeconds' => false,
                            'template' => 'dropdown',
                            'showMeridian' => false,
                            'minuteStep' => 15,
                            'showInputs' => false,
                            'defaultTime' => false,
                        ],
                        'addonOptions' => [
                            'asButton' => true,
                            'buttonOptions' => ['class' => 'btn btn-info'],
                        ]]); ?>
                    <?php echo $form_active->field($form, 'irregular_additional_percent')->textInput() ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo Html::submitInput(Yii::t('backend.view', $is_create ? 'Create' : 'Update'), ['class' => 'btn btn-success', 'id' => 'save-button']); ?>
            </div>
        </fieldset>
        <?php ActiveForm::end(); ?>
    </div>
<?php $field_rate = Html::getInputId($form, 'rate_type_id');
$field_fixed_payment = Html::getInputId($form, 'fixed_payment');
$rate_fixed_payments_url = Url::to(['/json-search/rate-fixed-payment']);
AutoRegistrableScriptBlock::begin();
?>
    <script>
        $('#<?=$field_rate?>').on('change', function () {
            var element = $(this);
            $.getJSON('<?=$rate_fixed_payments_url?>', {
                id: element.val()
            }, function (data) {
                if (!$.isEmptyObject(data)) {
                    $('#<?=$field_fixed_payment?>').val(data['fixed_payment']);
                } else {
                    $('#<?=$field_fixed_payment?>').val('');
                }
            })
        });
        <?php if($is_create) : ?>
        $('#<?=$field_rate?>').each(function () {
            var element = $(this);
            $.getJSON('<?=$rate_fixed_payments_url?>', {
                id: element.val()
            }, function (data) {
                if (!$.isEmptyObject(data)) {
                    $('#<?=$field_fixed_payment?>').val(data['fixed_payment']);
                } else {
                    $('#<?=$field_fixed_payment?>').val('');
                }
            })
        });
        <?php endif ?>
    </script>
<?php AutoRegistrableScriptBlock::end();





