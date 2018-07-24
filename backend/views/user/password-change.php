<?php
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\User;

$this->title = Yii::t('backend.view', 'User - {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Users'),
	'url' => ['/user/list'],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_user_menu', ['model' => $model]); ?>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-user-password-change',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'password')->passwordInput(); ?>
					<?php echo $form_active->field($form, 'password_repeat')->passwordInput(); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>