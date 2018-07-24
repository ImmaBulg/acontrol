<?php

use yii\helpers\Url;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\widgets\DepDrop;
use common\models\Meter;
use common\models\Rate;
use common\models\RateType;
use common\models\RuleFixedLoad;
use backend\models\forms\FormRuleFixedLoad;

$this->title = Yii::t('backend.view', 'Create a new fixed load rule');
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
	'label' => Yii::t('backend.view', 'Fixed load association rules'),
	'url' => ['/rule-fixed-load/list', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new fixed load rule');
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-fixed-load-rule-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'name')->textInput(); ?>
					<?php echo $form_active->field($form, 'use_type')->widget(Select2::classname(), [
						'data' => RuleFixedLoad::getListUseTypes(),
						'options' => [
							'options' => FormRuleFixedLoad::getListUseTypeAttributes(),
						],
					]); ?>
					<?php echo $form_active->field($form, 'description')->textArea(); ?>
					<?php echo $form_active->field($form, 'use_frequency')->widget(Select2::classname(), [
						'data' => RuleFixedLoad::getListUseFrequencies(),
					]); ?>
					<?php echo $form_active->field($form, 'status')->widget(Select2::classname(), [
						'data' => RuleFixedLoad::getListStatuses(),
					]); ?>
				</div>
				<div class="col-lg-6">
					<div style="display:none;" data-use-type="<?php echo FormRuleFixedLoad::USE_TYPE_VALUE; ?>">
						<?php echo $form_active->field($form, 'value')->textInput(); ?>
					</div>
					<div style="display:none;" data-use-type="<?php echo FormRuleFixedLoad::USE_TYPE_CONSUMPTION; ?>">
						<?php echo $form_active->field($form, 'pisga')->textInput(); ?>
						<?php echo $form_active->field($form, 'geva')->textInput(); ?>
						<?php echo $form_active->field($form, 'shefel')->textInput(); ?>
					</div>
					<div style="display:none;" data-rate-type="<?php echo FormRuleFixedLoad::RATE_TYPE_FLAT_AMOUNT; ?>">
						<?php echo $form_active->field($form, 'rate_type_flat_id')->widget(Select2::classname(), [
							'data' => Rate::getListRateTypes(['type' => RateType::TYPE_FLAT]),
							'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
						]); ?>
					</div>
					<div style="display:none;" data-rate-type="<?php echo FormRuleFixedLoad::RATE_TYPE_PERCENT_AMOUNT; ?>">
						<?php echo $form_active->field($form, 'rate_type_fixed_id')->widget(Select2::classname(), [
							'data' => Rate::getListRateTypes(['type' => RateType::TYPE_PERCENT]),
							'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
						]); ?>
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

$script = <<< JS
$('#$field_use_type').on('change', function(){
	var form = $(this).parents('form');

	var use_type = this.options[this.selectedIndex].getAttribute('data-use-type');
	var field = form.find('div[data-use-type="' +use_type+ '"]');
	form.find('div[data-use-type]').hide();
	if (field.length) field.show();

	var rate_type = this.options[this.selectedIndex].getAttribute('data-rate-type');
	var field = form.find('div[data-rate-type="' +rate_type+ '"]');
	form.find('div[data-rate-type]').hide();
	if (field.length) field.show();
});
$('#$field_use_type').each(function(){
	var form = $(this).parents('form');

	var use_type = this.options[this.selectedIndex].getAttribute('data-use-type');
	var field = form.find('div[data-use-type="' +use_type+ '"]');
	if (field.length) field.show();

	var rate_type = this.options[this.selectedIndex].getAttribute('data-rate-type');
	var field = form.find('div[data-rate-type="' +rate_type+ '"]');
	if (field.length) field.show();
});
JS;
$this->registerJs($script);