<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\Rate;
use common\models\Report;
use common\widgets\Select2;

$this->title = Yii::t('backend.view', 'Create a new site');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->name,
	'url' => ['/client/view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Sites'),
	'url' => ['/client/sites', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new site');
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
				</div>
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'fixed_payment')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
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
					<?php echo $form_active->field($form, 'fixed_addition_value')->textInput(/*['allow_only' => Html::TYPE_NUMBER]*/); ?>
					<?php echo $form_active->field($form, 'fixed_addition_comment')->textArea(); ?>
					<?php echo $form_active->field($form, 'auto_issue_reports')->checkboxList(Report::getAutoIssueListTypes()); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>
<?php
$field_rate = Html::getInputId($form, 'rate_type_id');
$field_fixed_payment = Html::getInputId($form, 'money_addition_amount');
$rate_fixed_payments_url = Url::to(['/json-search/rate-fixed-payment']);

$script = <<< JS
$('#$field_rate').on('change', function(){
	var element = $(this);
	$.getJSON('$rate_fixed_payments_url', {
		id: element.val()
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
		id: element.val()
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