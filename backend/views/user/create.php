<?php
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\components\rbac\Role;
use common\widgets\Select2;
use common\models\Tenant;
use common\models\User;
use common\models\UserOwner;
use common\models\UserOwnerTenant;
use common\models\UserOwnerSite;

$this->title = Yii::t('backend.view', 'Create a new user');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Users'),
	'url' => ['/user/list'],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new user');
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-user-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'name')->textInput(); ?>
					<?php echo $form_active->field($form, 'nickname')->textInput(); ?>
					<?php echo $form_active->field($form, 'email')->textInput(); ?>
					<?php echo $form_active->field($form, 'role')->widget(Select2::classname(), [
						'data' => Role::getAliasAllowedRoles(Yii::$app->user->role),
					]); ?>
					<?php echo $form_active->field($form, 'password')->passwordInput(); ?>
					<?php echo $form_active->field($form, 'password_repeat')->passwordInput(); ?>
					<?php echo $form_active->field($form, 'status')->widget(Select2::classname(), [
						'data' => User::getListStatuses(),
					]); ?>
					<?php echo $form_active->field($form, 'alert_notification_email', [
						'options' => [
							'data' => ['role' => Role::ROLE_TECHNICIAN],
						],
					])->checkbox(); ?>
					<?php echo $form_active->field($form, 'alert_notifications', [
						'options' => [
							'data' => ['role' => Role::ROLE_TECHNICIAN],
						],
					])->multiSelect2Side(Tenant::getListSites()); ?>
					<?php echo $form_active->field($form, 'users', [
						'options' => [
							'data' => ['role' => Role::ROLE_CLIENT],
						],
					])->multiSelect2Side(UserOwner::getListUsers()); ?>
					<?php echo $form_active->field($form, 'sites', [
						'options' => [
							'data' => ['role' => Role::ROLE_SITE],
						],
					])->multiSelect2Side(UserOwnerSite::getListSites()); ?>
					<?php echo $form_active->field($form, 'tenants', [
						'options' => [
							'data' => ['role' => Role::ROLE_TENANT],
						],
					])->multiSelect2Side(UserOwnerTenant::getListTenants()); ?>
				</div>
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'address')->textArea(); ?>
					<?php echo $form_active->field($form, 'job')->textInput(); ?>
					<?php echo $form_active->field($form, 'phone')->textInput(); ?>
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

<?php $this->registerJs("
	jQuery('#" .Html::getInputId($form, 'role'). "').on('change', function(){
		var form = jQuery(this).parents('form');
		form.find('div[data-role]').hide();
		console.log(form.find('div[data-role]'));
		form.find('div[data-role=\"' +this.value+ '\"]').show();
	});
	jQuery('#" .Html::getInputId($form, 'role'). "').each(function(){
		var form = jQuery(this).parents('form');
		form.find('div[data-role]').hide();
		form.find('div[data-role=\"' +this.value+ '\"]').show();
	});
"); ?>