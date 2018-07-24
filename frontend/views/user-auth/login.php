<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\ActiveForm;

$this->title = Yii::t('frontend.view', 'Sign In');
?>
<?php $form_active = ActiveForm::begin([
	'enableOneProcessSubmit' => true,
]); ?>
	<div class="form-group text-center">
		<?php echo Html::img('@web/theme/images/logo.png', ['class' => 'logo', 'width' => 423, 'height' => 69]); ?>
	</div>
	<?php echo $form_active->field($form, 'nickname')->textInput(['placeholder' => $form->getAttributeLabel('nickname')])->label(false); ?>
	<?php echo $form_active->field($form, 'password')->passwordInput(['placeholder' => $form->getAttributeLabel('password')])->label(false); ?>
	<?php echo $form_active->field($form, 'rememberMe')->checkbox(); ?>
	<div class="form-group">
		<?php echo Html::submitButton(Yii::t('frontend.view', 'Sign In'), ['class' => 'btn btn-primary btn-block']); ?>
	</div>
<?php ActiveForm::end(); ?>