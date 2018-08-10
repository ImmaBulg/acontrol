<?php
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\Tenant;
use common\models\TenantBillingSetting;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\Report;
use common\models\Rate;
use common\widgets\Select2;
use common\widgets\DepDrop;
use backend\models\forms\FormTenant;

$this->title = Yii::t('backend.view', 'Create a new tenant');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Tenants'),
	'url' => ['/tenant/list'],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new tenant');
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-tenant-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'site_id')->widget(Select2::classname(), [
						'data' => Tenant::getListSites(),
					]); ?>
					<?php echo $form_active->field($form, 'name')->textInput(); ?>
					<?php echo $form_active->field($form, 'type')->widget(Select2::classname(), [
						'data' => Tenant::getListTypes(),
					]); ?>
					<?php echo $form_active->field($form, 'to_issue')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'options' => [
							'data' => [
								'included-reports' => FormTenant::getListToIssueIncludedReports(),
							],
						],
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'site_id')],
							'url' => Url::to([
								'/form-dependent/site-to-issues',
							]),
							'initialize' => true,
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
					]); ?>
					<?php echo $form_active->field($form, 'rate_type_id')->widget(Select2::classname(), [
						'data' => ArrayHelper::merge(['' => Yii::t('backend.view', 'Not set')], Rate::getListRateTypes()),
					]); ?>
					<?php echo $form_active->field($form, 'site_rate')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'site_id')],
							'url' => Url::to([
								'/form-dependent/site-rates',
							]),
							'initialize' => true,
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
						'pluginEvents' => [
							"depdrop.change"=>"function(event, id, value, count) {
								jQuery(event.currentTarget).prop('disabled', true);
							}",
						],
					])->label(false); ?>
					<?php echo $form_active->field($form, 'square_meters')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
					<?php echo $form_active->field($form, 'entrance_date')->dateInput(); ?>
					<?php echo $form_active->field($form, 'exit_date')->dateInput(); ?>
				</div>
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'comment')->textArea(); ?>
					<?php echo $form_active->field($form, 'fixed_payment')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
					<?php echo $form_active->field($form, 'site_fixed_payment')->textInput([
						'disabled' => true,
						'allow_only' => Html::TYPE_NUMBER,
					])->label(false); ?>
					<?php echo $form_active->field($form, 'id_with_client')->textInput(); ?>
					<?php echo $form_active->field($form, 'accounting_number')->textInput(); ?>
					<?php echo $form_active->field($form, 'billing_content')->textArea(); ?>
					<?php echo $form_active->field($form, 'included_reports')->checkboxList(Report::getTenantListTypes()); ?>
					<?php echo $form_active->field($form, 'hide_drilldown')->checkbox(); ?>
					<?php echo $form_active->field($form, 'is_visible_on_dat_file')->checkbox(); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>
<?php
$field_site_id = Html::getInputId($form, 'site_id');
$field_site_fixed_payment = Html::getInputId($form, 'site_fixed_payment');
$site_fixed_payments_url = Url::to(['/json-search/site-fixed-payment']);

$field_rate = Html::getInputId($form, 'rate_type_id');
$field_fixed_payment = Html::getInputId($form, 'fixed_payment');
$rate_fixed_payments_url = Url::to(['/json-search/rate-fixed-payment']);

$field_to_issue = Html::getInputId($form, 'to_issue');
$field_included_reports = Html::getInputId($form, 'included_reports');

$script = <<< JS
$('#$field_site_id').on('change', function(){
	var element = $(this);
	$.getJSON('$site_fixed_payments_url', {
		site_id: element.val()
	}, function(data){
		if (!$.isEmptyObject(data)) {
			$('#$field_site_fixed_payment').val(data.fixed_payment);
		} else {
			$('#$field_site_fixed_payment').val('');
		}
	})
});
$('#$field_site_id').each(function(){
	var element = $(this);
	$.getJSON('$site_fixed_payments_url', {
		site_id: element.val()
	}, function(data){
		if (!$.isEmptyObject(data)) {
			$('#$field_site_fixed_payment').val(data.fixed_payment);
		} else {
			$('#$field_site_fixed_payment').val('');
		}
	})
});
$('#$field_to_issue').on('change', function(){
	var form = $(this).parents('form');
	var data = $(this).data('included-reports');
	var type = $(this).val();
	var field = form.find('#$field_included_reports');
	
	if (data[type]) {
		for (key in data[type]) {
			field.find('input[type="checkbox"][value="' +key+ '"]').prop('checked', data[type][key]);
		}
	}
});

$('#$field_rate').on('change', function(){
	var element = $(this);
	$.getJSON('$rate_fixed_payments_url', {
		type: element.val()
	}, function(data){
		if (!$.isEmptyObject(data)) {
			$('#$field_fixed_payment').val(data.fixed_payment);
		} else {
			$('#$field_fixed_payment').val('');
		}
	})
});
$('#$field_rate').each(function(){
	var element = $(this);
	$.getJSON('$rate_fixed_payments_url', {
		type: element.val()
	}, function(data){
		if (!$.isEmptyObject(data)) {
			$('#$field_fixed_payment').val(data.fixed_payment);
		} else {
			$('#$field_fixed_payment').val('');
		}
	})
});
JS;
$this->registerJs($script);