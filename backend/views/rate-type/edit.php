<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\RateType;
use common\widgets\Select2;

$this->title = Yii::t('backend.view', 'Rate type - {id}', ['id' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Rate types'),
	'url' => ['/rate-type/list'],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-rate-type-edit',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'name_en')->textInput(); ?>
					<?php echo $form_active->field($form, 'name_he')->textInput(); ?>
					<?php echo $form_active->field($form, 'type')->widget(Select2::classname(), [
						'data' => RateType::getListTypes(),
					]); ?>
					<?php echo $form_active->field($form, 'level')->widget(Select2::classname(), [
						'data' => RateType::getListLevels(),
						'options' => ['prompt' => Yii::t('backend.view', 'Select an option')],
					]); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>