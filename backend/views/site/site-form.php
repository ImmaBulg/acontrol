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
use common\models\RateName;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use dezmont765\yii2bundle\widgets\AutoRegistrableScriptBlock;
use kartik\widgets\TimePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

$this->registerCssFile('@web/css/smarttime.css');
$this->registerJsFile('//ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js', [
    'position' => View::POS_END,
]);

$this->registerJsFile('@web/js/plugins/smarttime.js', [
    'position' => View::POS_END,
]);

$this->registerJsFile('@web/js/site_irregular_hours.js', [
    'position' => View::POS_END,
]);

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
        <ul class="nav nav-pills">
            <li class="active"><a data-toggle="pill" href="#edit-form">Main data</a></li>
            <li><a data-toggle="pill" href="#irregular-data">Irregular hours</a></li>
        </ul>
        <div class="tab-content">
            <div id="edit-form" class="tab-pane fade in active">
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
                                'data' => RateName::getListName(),
                            ]); ?>
                            <?php echo $form_active->field($form, 'report_calculation_type')->dropDownList(Report::getTenantBillReportTypes()); ?>
                            <?php echo $form_active->field($form, 'billing_day')->widget(Select2::classname(), [
                                'data' => SiteBillingSetting::getListBillingDays(),
                            ]); ?>
                            <?php echo $form_active->field($form, 'include_vat')->checkbox(); ?>
                            <?php echo $form_active->field($form, 'comment')->textArea(); ?>
                            <div style="display:none;" class="manual-cop" data-type="<?php echo Json::encode([Report::TENANT_BILL_REPORT_BY_MANUAL_COP]); ?>">
                                <div style="display:none;" class="manual" data-type="<?php echo Json::encode([RateName::HOME, RateName::GENERAL]); ?>">
                                    <?php echo $form_active->field($form, 'manual_cop')->textInput(); ?>
                                </div>
                                <div stlye="display: none;" class="taoz" data-type="<?php echo Json::encode([RateName::LOW, RateName::HIGH]); ?>">
                                    <?php echo $form_active->field($form, 'manual_cop_shefel')->textInput(); ?>
                                    <?php echo $form_active->field($form, 'manual_cop_geva')->textInput(); ?>
                                    <?php echo $form_active->field($form, 'manual_cop_pisga')->textInput(); ?>
                                </div>
                            </div>
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
                          <!--  <?php /*echo $form_active->field($form, 'irregular_hours_from')->widget(TimePicker::classname(), [
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
                                ]]); */?>
                            <?php /*echo $form_active->field($form, 'irregular_hours_to')->widget(TimePicker::classname(), [
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
                                ]]); */?>
                            --><?php /*echo $form_active->field($form, 'irregular_additional_percent')->textInput() */?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo Html::submitInput(Yii::t('backend.view', $is_create ? 'Create' : 'Update'), ['class' => 'btn btn-success', 'id' => 'save-button']); ?>
                    </div>
                </fieldset>
                <?php ActiveForm::end(); ?>
            </div>
            <div id="irregular-data" class="tab-pane fade">
                <div class="row">
                    <div class="col-lg-6">
                        <div ng-app="irregularHours" ng-cloak>
                            <div class="col-lg-12">
                                <div class="loader-wrap">
                                    <div ng-controller="irregularCalendar"
                                         data-init='<?php echo json_encode($irregular_data); ?>'>
                                        <hr style="border-color: grey">
                                        <div>
                                            <label class="control-label">{{ texts.percent_text }}</label>
                                            <div class="input-group" style="width:100%">
                                                <input type="text" ng-model="irregular_additional_percent" name="percent" class="form-control" placeholder="0">
                                            </div>
                                        </div>
                                        <hr style="border-color: grey">
                                        <div>
                                            <label for="usage_type">{{ texts.usage_type_text }}</label>
                                            <select name="usage_type" id="usage_type" ng-model="usage_type" class="form-control">
                                                <?php foreach($usage_types as $usage_type): ?>
                                                    <option value="<?=$usage_type['value']?>"><?=$usage_type['title']?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <hr style="border-color: grey">
                                        <div ng-repeat="(day_number,day_of_week) in days_of_week">
                                            <h3>{{day_of_week}}</h3>
                                            <div>
                                                <div ng-repeat="(index, row) in model_data[day_number]">
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <div class="input-group">
                                                                <div class="smart-time form-control time-picker" smart-time
                                                                     smt-value="row.hours_from"></div>
                                                                <span class="input-group-addon"><i
                                                                            class="glyphicon glyphicon-time"></i></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <div class="input-group">
                                                                <div class="smart-time form-control time-picker" smart-time
                                                                     smt-value="row.hours_to"></div>
                                                                <span class="input-group-addon"><i
                                                                            class="glyphicon glyphicon-time"></i></span>
                                                            </div>
                                                        </div>
                                                        <input ng-model="row.id" type="hidden">
                                                        <div class="form-group col-sm-4">
                                                            <button type="button" ng-click="deleteHours(index, day_number)"
                                                                    class="btn btn-danger">{{texts.delete_text}}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" ng-click="addHours(day_number)"
                                                    class="btn btn-success smt-button">{{texts.add_text}}
                                            </button>
                                            <hr style="border-color: grey">
                                        </div>
                                        <br>
                                        <div class="btn btn-success update-irregular smt-button" ng-click="saveIrregular()">
                                            {{texts.update_text}}
                                        </div>
                                        <div class="modal fade" id="success-modal" role="dialog">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center">
                                                        <h3>{{texts.success_text}}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="loader" ng-show="preloader"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--<div ng-app="irregularHours" ng-cloak>
                        <div class="loader-wrap">
                            <div ng-controller="irregularCalendar"
                                 data-init='<?php /*echo json_encode($irregular_data); */?>'>
                                <hr style="border-color: grey">
                                <div ng-repeat="(day_number,day_of_week) in days_of_week">
                                    <h3>{{day_of_week}}</h3>
                                    <div>
                                        <div ng-repeat="(index, row) in model_data[day_number]">
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="input-group">
                                                        <div class="smart-time form-control time-picker" smart-time
                                                             smt-value="row.hours_from"></div>
                                                        <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-time"></i></span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="input-group">
                                                        <div class="smart-time form-control time-picker" smart-time
                                                             smt-value="row.hours_to"></div>
                                                        <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-time"></i></span>
                                                    </div>
                                                </div>
                                                <input ng-model="row.id" type="hidden">
                                                <div class="form-group col-sm-3">
                                                    <button type="button" ng-click="deleteHours(index, day_number)"
                                                            class="btn btn-danger">{{texts.delete_text}}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" ng-click="addHours(day_number)"
                                            class="btn btn-success smt-button">{{texts.add_text}}
                                    </button>
                                    <hr style="border-color: grey">
                                </div>
                                <br>
                                <div class="btn btn-success update-irregular smt-button" ng-click="saveIrregular()">
                                    {{texts.update_text}}
                                </div>
                                <div class="modal fade" id="success-modal" role="dialog">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-body text-center">
                                                <h3>{{texts.success_text}}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="loader" ng-show="preloader"></div>
                            </div>
                        </div>
                    </div>
                </div>-->
                </div>
            </div>
        </div>
    </div>
<?php
$field_type = Html::getInputId($form, 'report_calculation_type');
$field_rate = Html::getInputId($form, 'rate_type_id');

$script = <<< JS
$('#$field_rate').on('change', function(){
	var value = this.value;
	var form = $(this).parents('form');
	var fields = form.find('.manual');
	var taoz = form.find('.taoz');

	fields.hide();
	taoz.hide();
	taoz.each(function() {
	    var field = jQuery(this);
	    if (jQuery.inArray(parseInt(value), field.data('type')) > -1) {
	        taoz.show();
	    }
	});
	fields.each(function(){
		var field = jQuery(this);
		if (jQuery.inArray(parseInt(value), field.data('type')) > -1) {
			field.show();
		}
	});
});
$('#$field_rate').each(function(){
	var value = this.value;
	var form = $(this).parents('form');
	var fields = form.find('.manual');
	var taoz = form.find('.taoz');

	fields.hide();
	taoz.hide();
	taoz.each(function() {
	   var field = jQuery(this);
	   if (jQuery.inArray(parseInt(value), field.data('type')) > -1) {
	       field.show();
	   }
	});
	fields.each(function(){
		var field = jQuery(this);
		if (jQuery.inArray(parseInt(value), field.data('type')) > -1) {
			field.show();
		}
	});
});

$('#$field_type').on('change', function(){
	var value = this.value;
	var form = $(this).parents('form');
	var fields = form.find('.manual-cop');
	fields.hide();
	fields.each(function(){
		var field = jQuery(this);
		if (jQuery.inArray(parseInt(value), field.data('type')) > -1) {
			field.show();
		}
	});
});
$('#$field_type').each(function(){
	var value = this.value;
	var form = $(this).parents('form');
	var fields = form.find('.manual-cop');

	fields.hide();
	fields.each(function(){
		var field = jQuery(this);
		if (jQuery.inArray(parseInt(value), field.data('type')) > -1) {
			field.show();
		}
	});
});
JS;
$this->registerJs($script);
?>

<?php /*$field_rate = Html::getInputId($form, 'rate_type_id');
$field_fixed_payment = Html::getInputId($form, 'fixed_payment');
$rate_fixed_payments_url = Url::to(['/json-search/rate-fixed-payment']);
AutoRegistrableScriptBlock::begin();
*/?><!--
    <script>
        $('#<?/*=$field_rate*/?>').on('change', function () {
            var element = $(this);
            $.getJSON('<?/*=$rate_fixed_payments_url*/?>', {
                id: element.val()
            }, function (data) {
                if (!$.isEmptyObject(data)) {
                    $('#<?/*=$field_fixed_payment*/?>').val(data['fixed_payment']);
                } else {
                    $('#<?/*=$field_fixed_payment*/?>').val('');
                }
            })
        });
        <?php /*if($is_create) : */?>
        $('#<?/*=$field_rate*/?>').each(function () {
            var element = $(this);
            $.getJSON('<?/*=$rate_fixed_payments_url*/?>', {
                id: element.val()
            }, function (data) {
                if (!$.isEmptyObject(data)) {
                    $('#<?/*=$field_fixed_payment*/?>').val(data['fixed_payment']);
                } else {
                    $('#<?/*=$field_fixed_payment*/?>').val('');
                }
            })
        });
        <?php /*endif */?>
    </script>
--><?php /*AutoRegistrableScriptBlock::end();*/







