<?php

use common\helpers\Html;
use common\widgets\ActiveForm;

$this->title = Yii::t('backend.view', 'Contact - {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationSite->relationUser->name,
	'url' => ['/client/view', 'id' => $model->relationSite->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Sites'),
	'url' => ['/client/sites', 'id' => $model->relationSite->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationSite->name,
	'url' => ['/site/view', 'id' => $model->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Contacts'),
	'url' => ['/site-contact/list', 'id' => $model->site_id],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_site_contact_menu', ['model' => $model]); ?>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-site-contact-create',
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
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>