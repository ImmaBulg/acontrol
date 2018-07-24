<?php

use yii\helpers\Url;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\widgets\DepDrop;
use common\models\Site;
use common\models\Meter;

$this->title = Yii::t('backend.view', 'Create a new channel group');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Channel groups'),
	'url' => ['/meter-channel-group/list'],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new channel group');
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-meter-channel-group-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'name')->textInput(); ?>
					<?php echo $form_active->field($form, 'user_id')->widget(Select2::classname(), [
						'data' => Site::getListUsers(),
                    ]); ?>
					<?php echo $form_active->field($form, 'site_id')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'user_id')],
							'url' => Url::to([
								'/form-dependent/user-sites',
							]),
							'initialize' => true,
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
					]); ?>
					<?php echo $form_active->field($form, 'meter_id')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'site_id')],
							'url' => Url::to([
								'/form-dependent/site-meters',
							]),
                            'placeholder' => 'Select ...',
						],
					]); ?>
					<?php echo $form_active->field($form, 'group_channels')->multiSelect2Side([]); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>
<?php
$field_meter_id = Html::getInputId($form, 'meter_id');
$field_channels = Html::getInputId($form, 'group_channels[]');
$channels_url = Url::to(['/json-search/meter-channels']);

$script = <<< JS
$('#$field_meter_id').on('change', function(){
	var element = $(this);
	var form = $(this).parents('form');

	if (element.val()) {
		$.getJSON('$channels_url', {
			meter_id: element.val()
		}, function(data){
			if (!$.isEmptyObject(data)) {
				$('#$field_channels option').each(function(){
					if (!$(this).is(':selected')) {
						$(this).remove();
					}
				});
				for (key in data) {
					$('#$field_channels').append($('<option></option>').attr('value', key).text(data[key]));
				}
				$('#$field_channels').multiselect2side('destroy').multiselect2side();
			} else {
				$('#$field_channels option').each(function(){
					if (!$(this).is(':selected')) {
						$(this).remove();
					}
				});
			}
		});
	} else {
		$('#$field_channels option').each(function(){
			$(this).remove();
		});
		$('#$field_channels').multiselect2side('destroy').multiselect2side();
	}
});
$('#$field_meter_id').each(function(){
	var element = $(this);
	var form = $(this).parents('form');
	$.getJSON('$channels_url', {
		meter_id: element.val()
	}, function(data){
		if (!$.isEmptyObject(data)) {
			$('#$field_channels option').each(function(){
				if (!$(this).is(':selected')) {
					$(this).remove();
				}
			});
			for (key in data) {
				$('#$field_channels').append($('<option></option>').attr('value', key).text(data[key]));
			}
			$('#$field_channels').multiselect2side('destroy').multiselect2side();
		} else {
			$('#$field_channels option').each(function(){
				if (!$(this).is(':selected')) {
					$(this).remove();
				}
			});
		}
	});
});
JS;
$this->registerJs($script);