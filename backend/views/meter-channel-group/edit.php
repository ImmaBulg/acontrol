<?php

use yii\helpers\Url;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\widgets\DepDrop;
use common\models\Site;
use common\models\Tenant;
use common\models\Meter;

$this->title = Yii::t('backend.view', 'Channel group - {name}', ['name' => $model->name]);
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
	'url' => ['/site/view', 'id' => $model->relationSite->id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Channel groups'),
	'url' => ['/site/meter-channel-groups', 'id' => $model->relationSite->id],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-meter-channel-group-edit',
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
						'data' => Tenant::getListSites($form->user_id),
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'user_id')],
							'url' => Url::to([
								'/form-dependent/user-sites',
							]),
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
					]); ?>
					<?php echo $form_active->field($form, 'meter_id')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'data' => Meter::getAirListMeters($model->relationSite->id),
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'site_id')],
							'url' => Url::to([
								'/form-dependent/site-meters',
							]),
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
					]); ?>
					<?php echo $form_active->field($form, 'group_channels')->multiSelect2Side($form->getListGroupChannels()); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
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
JS;
$this->registerJs($script);