<?php
use common\helpers\Html;
use common\widgets\ActiveForm;

$this->title = Yii::t('backend.view', 'Create a VAT');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'VAT'),
	'url' => ['/vat/list'],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a VAT');
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-vat-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'vat')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
					<?php echo $form_active->field($form, 'start_date')->dateInput(); ?>
					<?php echo $form_active->field($form, 'end_date')->dateInput(); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>