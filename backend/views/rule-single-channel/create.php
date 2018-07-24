<?php
use yii\helpers\Url;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\Meter;
use common\models\RuleSingleChannel;
use common\widgets\Select2;
use common\widgets\DepDrop;
use kartik\time\TimePicker;

$this->title = Yii::t('backend.view', 'Create a new single channel association rule');
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
$this->params['breadcrumbs'][] = [
	'label' => $model->relationSite->name,
	'url' => ['/site/view', 'id' => $model->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Tenants'),
	'url' => ['/site/tenants', 'id' => $model->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->name,
	'url' => ['/tenant/view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Single channel association rules'),
	'url' => ['/rule-single-channel/list', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new single channel association rule');
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-rule-single-channel-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'name')->textInput(); ?>
					<?php echo $form_active->field($form, 'total_bill_action')->widget(Select2::classname(), [
						'data' => RuleSingleChannel::getListTotalBillActions(),
					]); ?>
					<?php echo $form_active->field($form, 'use_type')->widget(Select2::classname(), [
						'data' => RuleSingleChannel::getListUseTypes(),
					]); ?>
					<div class="row">
						<div class="col-lg-6" style="display:none;" data-use-type="<?php echo RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD; ?>">
							<?php echo $form_active->field($form, 'meter_id')->widget(Select2::classname(), [
								'data' => Meter::getListMeters($model->site_id),
							]); ?>
						</div>
						<div class="col-lg-6" style="display:none;" data-use-type="<?php echo RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD; ?>">
							<?php echo $form_active->field($form, 'channel_id')->widget(DepDrop::classname(), [
								'type' => DepDrop::TYPE_SELECT2,
								'data' => RuleSingleChannel::getListMeterChannels($form->total_bill_action, $form->meter_id),
								'pluginOptions' => [
									'depends' => [
										Html::getInputId($form, 'total_bill_action'),
										Html::getInputId($form, 'meter_id'),
									],
									'url' => Url::to(['/form-dependent/rule-single-channel-meter-channels']),
									'placeholder' => false,
									'loadingText' => Yii::t('backend.view', 'Loading'),
								],
							]); ?>
						</div>
						<div class="col-lg-6" style="display:none;" data-use-type="<?php echo RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD; ?>">
							<?php echo $form_active->field($form, 'usage_tenant_id')->widget(Select2::classname(), [
								'data' => RuleSingleChannel::getListTenants($model->site_id, $model->id),
							]); ?>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-6">
							<?php echo $form_active->field($form, 'current_multiplier')->textInput(['disabled' => 'disabled']); ?>
						</div>
						<div class="col-lg-6">
							<?php echo $form_active->field($form, 'voltage_multiplier')->textInput(['disabled' => 'disabled']); ?>	
						</div>
					</div>
					<div class="form-group">
						<?php echo Html::a(Yii::t('backend.view', 'Edit meter channel'), '#', ['id' => 'meter-channel-edit-link', 'target' => '_blank']); ?>
					</div>
					<?php echo $form_active->field($form, 'status')->widget(Select2::classname(), [
						'data' => RuleSingleChannel::getListStatuses(),
					]); ?>
				</div>
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'start_date')->dateInput(); ?>
					<?php echo $form_active->field($form, 'use_percent')->widget(Select2::classname(), [
						'data' => RuleSingleChannel::getListUsePercents(),
					]); ?>
					<div style="display:none;" data-use-percent="<?php echo RuleSingleChannel::USE_PERCENT_PARTIAL; ?>">
						<?php echo $form_active->field($form, 'percent')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
					</div>
					<div style="display:none;" class="row" data-use-percent="<?php echo RuleSingleChannel::USE_PERCENT_HOUR; ?>">
						<div class="col-lg-6">
							<?php echo $form_active->field($form, 'from_hours')->widget(TimePicker::classname(), [
								'pluginOptions' => [
									'showMeridian' => false,
									'showInputs' => false,
									'defaultTime' => false,
								],
							]); ?>
						</div>
						<div class="col-lg-6">
							<?php echo $form_active->field($form, 'to_hours')->widget(TimePicker::classname(), [
								'pluginOptions' => [
									'showMeridian' => false,
									'showInputs' => false,
									'defaultTime' => false,
								],
							]); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>
<?php
$field_use_type = Html::getInputId($form, 'use_type');
$field_use_percent = Html::getInputId($form, 'use_percent');
$field_meter_id = Html::getInputId($form, 'meter_id');
$field_channel_id = Html::getInputId($form, 'channel_id');
$field_current_multiplier = Html::getInputId($form, 'current_multiplier');
$field_voltage_multiplier = Html::getInputId($form, 'voltage_multiplier');
$info_url = Url::to(['/json-search/meter-channel-info']);

$script = <<< JS
$('#$field_use_type').on('change', function(){
	var form = $(this).parents('form');
	var field = form.find('div[data-use-type="' +this.value+ '"]');
	form.find('div[data-use-type]').hide();
	if (field.length) field.show();
});
$('#$field_use_type').each(function(){
	var form = $(this).parents('form');
	var field = form.find('div[data-use-type="' +this.value+ '"]');
	if (field.length) field.show();
});
$('#$field_use_percent').on('change', function(){
	var form = $(this).parents('form');
	var field = form.find('div[data-use-percent="' +this.value+ '"]');
	form.find('div[data-use-percent]').hide();
	if (field.length) field.show();
});
$('#$field_use_percent').each(function(){
	var form = $(this).parents('form');
	var field = form.find('div[data-use-percent="' +this.value+ '"]');
	if (field.length) field.show();
});
$('#$field_channel_id').on('change', function(){
	var element = $(this);
	$('#$field_current_multiplier').val('').parent().append('<div class=\"plugin-loading\"></div>');
	$('#$field_voltage_multiplier').val('').parent().append('<div class=\"plugin-loading\"></div>');
	$('#meter-channel-edit-link').attr('href', '#');
	$.getJSON('$info_url', {
		channel_id: $(this).val()
	}, function(data){
		if (!$.isEmptyObject(data)) {
			$('#$field_current_multiplier').parent().find('.plugin-loading').remove();
			$('#$field_voltage_multiplier').parent().find('.plugin-loading').remove();
			
			$('#$field_current_multiplier').val(data.current_multiplier);
			$('#$field_voltage_multiplier').val(data.voltage_multiplier);
			$('#meter-channel-edit-link').attr('href', data.edit_link);
		}
	});
});
$('#$field_channel_id').each(function(){
	var element = $(this);
	$('#$field_current_multiplier').val('').parent().append('<div class=\"plugin-loading\"></div>');
	$('#$field_voltage_multiplier').val('').parent().append('<div class=\"plugin-loading\"></div>');
	$('#meter-channel-edit-link').attr('href', '#');
	$.getJSON('$info_url', {
		channel_id: $(this).val()
	}, function(data){
		if (!$.isEmptyObject(data)) {
			$('#$field_current_multiplier').parent().find('.plugin-loading').remove();
			$('#$field_voltage_multiplier').parent().find('.plugin-loading').remove();

			$('#$field_current_multiplier').val(data.current_multiplier);
			$('#$field_voltage_multiplier').val(data.voltage_multiplier);
			$('#meter-channel-edit-link').attr('href', data.edit_link);
		}
	});
});
JS;
$this->registerJs($script);