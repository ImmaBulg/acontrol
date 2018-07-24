<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\models\Task;
use common\models\Site;
use common\models\User;
use common\models\Tenant;
use common\models\Meter;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\widgets\DepDrop;

$this->title = Yii::t('backend.view', 'Create a task');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Alerts/Helpdesk'),
	'url' => ['/task/list'],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a task');
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-task-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'role')->widget(Select2::classname(), [
						'data' => Task::getListRoles(),
						'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
					]); ?>
					<?php echo $form_active->field($form, 'user_id')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'data' => User::getListByRole($form->role),
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'role')],
							'url' => Url::to([
								'/form-dependent/role-users',
							]),
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
					]); ?>	
					<?php echo $form_active->field($form, 'description')->textArea(); ?>
					<div class="row">
						<div class="col-md-6">
							<?php echo $form_active->field($form, 'date')->dateInput([
								'placeholder' => 'dd-MM-yyyy',
							]); ?>
						</div>
						<div class="col-md-6">
							<?php echo $form_active->field($form, 'time')->textInput([
								'placeholder' => 'HH:mm',
							]); ?>
						</div>
					</div>
					<?php echo $form_active->field($form, 'site_id')->widget(Select2::classname(), [
						'data' => Tenant::getListSites(),
						'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
					]); ?>
					<?php echo $form_active->field($form, 'site_contact_id')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'site_id')],
							'url' => Url::to([
								'/form-dependent/site-contacts',
							]),
							'initialize' => false,
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
					]); ?>
					<?php echo $form_active->field($form, 'meter_id')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
						'data' => ($form->site_id) ? Meter::getListMeters($form->site_id) : [],
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'site_id')],
							'url' => Url::to(['/form-dependent/site-meters']),
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
					]); ?>
					<?php echo $form_active->field($form, 'channel_id')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
						'data' => Meter::getListMeterChannels($form->meter_id),
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'meter_id')],
							'url' => Url::to(['/form-dependent/site-meter-channels']),
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
					]); ?>
					<?php echo $form_active->field($form, 'type')->widget(Select2::classname(), [
						'data' => Task::getListTypes(),
					]); ?>
					<?php echo $form_active->field($form, 'urgency')->widget(Select2::classname(), [
						'data' => Task::getListUrgencies(),
					]); ?>
					<?php echo $form_active->field($form, 'color')->widget(Select2::classname(), [
						'data' => ArrayHelper::merge(['' => Yii::t('backend.view', 'Not set')], Task::getListColors()),
					]); ?>
					<?php echo $form_active->field($form, 'status')->widget(Select2::classname(), [
						'data' => Task::getListStatuses(),
					]); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>