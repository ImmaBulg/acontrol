<?php

use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\models\ApiKey;

$this->title = Yii::t('backend.view', 'API key - {api_key}', ['api_key' => $model->api_key]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'API keys'),
	'url' => ['/api-key/list'],
];
$this->params['breadcrumbs'][] = $model->api_key;
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-api-key-edit',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'api_key')->textInput(); ?>
					<?php echo $form_active->field($form, 'status')->widget(Select2::classname(), [
						'data' => ApiKey::getListStatuses(),
					]); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>