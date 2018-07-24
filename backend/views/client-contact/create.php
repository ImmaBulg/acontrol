<?php

use common\helpers\Html;
use common\widgets\ActiveForm;

$this->title = Yii::t('backend.view', 'Create a new contact');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->name,
	'url' => ['/client/view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Contacts'),
	'url' => ['/client-contact/list', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = $this->title;
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-client-contact-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'name')->textInput(); ?>
					<?php echo $form_active->field($form, 'email')->textInput(); ?>
					<?php echo $form_active->field($form, 'phone')->textInput(); ?>
					<?php echo $form_active->field($form, 'cell_phone')->textInput(); ?>
				</div>
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'address')->textArea(); ?>
					<?php echo $form_active->field($form, 'job')->textInput(); ?>
					<?php echo $form_active->field($form, 'fax')->textInput(); ?>
					<?php echo $form_active->field($form, 'comment')->textArea(); ?>	
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>