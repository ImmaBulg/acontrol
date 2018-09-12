<?php
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\ActiveForm;

$this->title = Yii::t('backend.view', 'Multiplier - {value}', ['value' => $model->id]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Meters'),
	'url' => ['/meter/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationMeter->name,
	'url' => ['/meter/edit', 'id' => $model->relationMeter->id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Meter channels'),
	'url' => ['/meter-channel/list', 'id' => $model->relationMeter->id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationMeterChannel->channel,
	'url' => ['/meter-channel/edit', 'id' => $model->relationMeterChannel->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Multiplier - {value}', ['value' => $model->id]);
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-meter-channel-multiplier-edit',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'start_date')->dateInput(); ?>
					<?php echo $form_active->field($form, 'end_date')->dateInput(); ?>
					<?php echo $form_active->field($form, 'meter_multiplier')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>