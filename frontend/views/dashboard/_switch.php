<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\widgets\DepDrop;
use frontend\models\User;
?>
<?php $form_active = ActiveForm::begin([
	'action' => $action,
	'method' => 'GET',
	'enableOneProcessSubmit' => true,
	'options' => ['class' => 'well', 'data' => ['pjax' => true]],
]); ?>
	<div class="row">
		<?php if ($show_clients): ?>
			<div class="col-md-2">
				<?php echo $form_active->field($form, 'client_id')->widget(Select2::classname(), [
					'options' => ['prompt' => Yii::t('frontend.view', 'Select an option')],
					'data' => ArrayHelper::map($user->getClients(), 'id', 'name'),
				]); ?>
			</div>
		<?php endif; ?>

		<?php if ($show_sites): ?>
			<div class="col-md-2">
				<?php echo $form_active->field($form, 'site_id')->widget(DepDrop::classname(), [
					'type' => DepDrop::TYPE_SELECT2,
					'data' => ArrayHelper::map($user->getSitesByClientId($form->client_id), 'id', 'name'),
					'pluginOptions' => [
						'depends' => [Html::getInputId($form, 'client_id')],
						'url' => Url::to(['/form-dependent/sites']),
						'initialize' => false,
						'placeholder' => false,
						'loadingText' => Yii::t('frontend.view', 'Loading'),
					],
				]); ?>
			</div>
		<?php endif; ?>
		
		<?php if ($show_tenants): ?>
			<div class="col-md-2">
				<?php echo $form_active->field($form, 'tenant_id')->widget(DepDrop::classname(), [
					'type' => DepDrop::TYPE_SELECT2,
					'data' => ArrayHelper::map($user->getTenantsBySiteId($form->site_id), 'id', 'name'),
					'pluginOptions' => [
						'depends' => [Html::getInputId($form, 'site_id')],
						'url' => Url::to(['/form-dependent/tenants']),
						'initialize' => false,
						'placeholder' => false,
						'loadingText' => Yii::t('frontend.view', 'Loading'),
					],
				]); ?>
			</div>
		<?php endif; ?>

		<?php if ($show_meters): ?>
			<div class="col-md-2">
				<?php echo $form_active->field($form, 'meter_id')->widget(DepDrop::classname(), [
					'type' => DepDrop::TYPE_SELECT2,
					'data' => [-1 => 'Not set'] + ArrayHelper::map($user->getMetersByTenantId($form->tenant_id), 'id', function($item){
						return "{$item->name} - ({$item->getAliasType()})";
					}),
					'pluginOptions' => [
						'depends' => [Html::getInputId($form, 'tenant_id')],
						'url' => Url::to(['/form-dependent/meters']),
						'initialize' => false,
                        'allowClear' => true,
						'loadingText' => Yii::t('frontend.view', 'Loading'),
					],
				]); ?>
			</div>
		<?php endif; ?>

		<?php if ($show_channels): ?>
			<div class="col-md-2">
				<?php echo $form_active->field($form, 'channel_id')->widget(DepDrop::classname(), [
					'type' => DepDrop::TYPE_SELECT2,
					'data' => [-1 => 'Not set'] + ArrayHelper::map($user->getChannelsByTenantIdAndMeterId($form->tenant_id, $form->meter_id), 'id', function($item) use($form){
					    $tenant = \backend\models\searches\models\Tenant::findOne($form->tenant_id);
						$result =  Yii::t('frontend.view', '{tenant} - {name} - (M={m})', [
                            'tenant' => $tenant->name,
							'name' => $item->channel,
							'm' => $item->meter_multiplier,
						]);
						return $result;
					}),
					'pluginOptions' => [
						'depends' => [Html::getInputId($form, 'tenant_id'), Html::getInputId($form, 'meter_id')],
						'url' => Url::to(['/form-dependent/channels']),
						'initialize' => false,
                        'placeholder' => false,
                        'allowClear' => true,
						'loadingText' => Yii::t('frontend.view', 'Loading'),
					],
				]); ?>
			</div>
		<?php endif; ?>

		<div class="col-md-2 control-label-offset">
			<?php echo Html::submitButton(Yii::t('frontend.view', 'Switch'), ['class' => 'btn btn-default btn-block']); ?>
		</div>
	</div>
<?php ActiveForm::end(); ?>