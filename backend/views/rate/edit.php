<?php
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\Rate;
use common\models\RateType;
use common\widgets\Select2;
use backend\models\forms\FormRate;

$this->title = Yii::t('backend.view', 'Rate - {id}', ['id' => $model->id]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Rates'),
	'url' => ['/rate/list'],
];
$this->params['breadcrumbs'][] = $model->id;
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-rate-edit',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'rate_type_id')->widget(Select2::classname(), [
						'data' => Rate::getListRateTypes(),
						'options' => [
							'options' => FormRate::getListRateTypeAttributes(),
						],
					]); ?>
					<?php echo $form_active->field($form, 'start_date')->dateInput(); ?>
					<?php echo $form_active->field($form, 'end_date')->dateInput(); ?>
					<?php echo $form_active->field($form, 'fixed_payment')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
				</div>
				<div class="col-lg-6">
					<div style="display:none;" data-base-type="<?php echo RateType::TYPE_TAOZ; ?>">
						<?php echo $form_active->field($form, 'season')->widget(Select2::classname(), [
							'data' => Rate::getListSeasons(),
						]); ?>
						<?php echo $form_active->field($form, 'pisga')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
						<?php echo $form_active->field($form, 'geva')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
						<?php echo $form_active->field($form, 'shefel')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
						<?php echo $form_active->field($form, 'pisga_identifier')->textInput(); ?>
						<?php echo $form_active->field($form, 'geva_identifier')->textInput(); ?>
						<?php echo $form_active->field($form, 'shefel_identifier')->textInput(); ?>
					</div>
					<div style="display:none;" data-base-type="<?php echo RateType::TYPE_FIXED; ?>">
						<?php echo $form_active->field($form, 'rate')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
						<?php echo $form_active->field($form, 'identifier')->textInput(); ?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>
<?php
$field_rate_type_id = Html::getInputId($form, 'rate_type_id');

$script = <<< JS
$('#$field_rate_type_id').on('change', function(){
	var form = $(this).parents('form');
	var type = this.options[this.selectedIndex].getAttribute('data-base-type');
	var field = form.find('div[data-base-type="' +type+ '"]');
	form.find('div[data-base-type]').hide();
	if (field.length) field.show();
});
$('#$field_rate_type_id').each(function(){
	var form = $(this).parents('form');
	var type = this.options[this.selectedIndex].getAttribute('data-base-type');
	var field = form.find('div[data-base-type="' +type+ '"]');
	if (field.length) field.show();
});
JS;
$this->registerJs($script);