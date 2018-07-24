<?php

use yii\helpers\Url;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\widgets\DepDrop;
use common\models\Meter;
use common\models\RuleGroupLoad;
use common\models\TenantGroup;

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationTenant->relationUser->name,
	'url' => ['/client/view', 'id' => $model->relationTenant->relationUser->id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Sites'),
	'url' => ['/client/sites', 'id' => $model->relationTenant->relationUser->id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationTenant->relationSite->name,
	'url' => ['/site/view', 'id' => $model->relationTenant->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Tenants'),
	'url' => ['/site/tenants', 'id' => $model->relationTenant->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationTenant->name,
	'url' => ['/tenant/view', 'id' => $model->tenant_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Group load association rules'),
	'url' => ['/rule-group-load/list', 'id' => $model->tenant_id],
];

$this->title = $model->name;
$this->params['breadcrumbs'][] = $model->name;
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-group-load-rule-edit',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'name')->textInput(); ?>
					<?php echo $form_active->field($form, 'total_bill_action')->widget(Select2::classname(), [
						'data' => RuleGroupLoad::getListTotalBillActions(),
					]); ?>
					<?php echo $form_active->field($form, 'status')->widget(Select2::classname(), [
						'data' => RuleGroupLoad::getListStatuses(),
					]); ?>
					<?php echo $form_active->field($form, 'use_type')->widget(Select2::classname(), [
						'data' => RuleGroupLoad::getListUseTypes(),
					]); ?>
				</div>
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'use_percent')->widget(Select2::classname(), [
						'data' => RuleGroupLoad::getListUsePercents(),
                        'options'=> ['prompt' =>  Yii::t('backend.view','Choose calculation type')]
					]); ?>
					<?php echo $form_active->field($form, 'usage_tenant_group_id', [
						'options' => [
							'style' => "display:none;",
							'data' => ['use-percent' => [RuleGroupLoad::USE_PERCENT_FOOTAGE, RuleGroupLoad::USE_PERCENT_USAGE]],
						],
					])->widget(Select2::classname(), [
						'data' => RuleGroupLoad::getListTenantGroups($model->relationTenant->site_id),
					])->label($form->getAttributeLabel('usage_tenant_group_id'). ' ' .Html::a(Yii::t('backend.view', '(Add tenant group)'), ['/site/tenant-group-create', 'id' => $model->relationTenant->relationSite->id], ['target' => '_blank'])); ?>
                    <?php echo $form_active->field($form, 'percent', [
                        'options' => [
                            'style' => "display:none;",
                            'data' => ['use-percent' => [RuleGroupLoad::USE_PERCENT_FLAT]],
                        ],
                    ])->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-3" style="display:none;" data-use-type="<?php echo RuleGroupLoad::USE_TYPE_SINGLE_METER_LOAD; ?>">
					<?php echo $form_active->field($form, 'meter_id')->widget(Select2::classname(), [
						'data' => Meter::getAirListMeters($model->relationTenant->site_id),
					]); ?>
				</div>
				<div class="col-lg-3" style="display:none;" data-use-type="<?php echo RuleGroupLoad::USE_TYPE_SINGLE_METER_LOAD; ?>">
					<?php echo $form_active->field($form, 'channel_id')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'data' => RuleGroupLoad::getListMeterChannels($form->meter_id),
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'meter_id')],
							'url' => Url::to(['/form-dependent/rule-group-load-meter-channels']),
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
					]); ?>
				</div>
				<div class="col-lg-3" style="display:none;" data-use-type="<?php echo RuleGroupLoad::USE_TYPE_SINGLE_METER_GROUP_LOAD; ?>">
					<?php echo $form_active->field($form, 'channel_group_id')->widget(Select2::classname(), [
						'data' => RuleGroupLoad::getListChannelGroups($model->relationTenant->site_id),
					])->label($form->getAttributeLabel('channel_group_id'). ' ' .Html::a(Yii::t('backend.view', '(Add channel group)'), ['/site/meter-channel-group-create', 'id' => $model->relationTenant->relationSite->id], ['target' => '_blank'])); ?>
				</div>
				<div class="col-lg-3" style="display:none;" data-use-type="<?php echo RuleGroupLoad::USE_TYPE_SINGLE_TENANT_GROUP_LOAD; ?>">
					<?php echo $form_active->field($form, 'tenant_group_id')->widget(Select2::classname(), [
						'data' => RuleGroupLoad::getListTenantGroups($model->relationTenant->site_id),
					])->label($form->getAttributeLabel('tenant_group_id'). ' ' .Html::a(Yii::t('backend.view', '(Add tenant group)'), ['/site/tenant-group-create', 'id' => $model->relationTenant->relationSite->id], ['target' => '_blank'])); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>
<?php
$field_use_type = Html::getInputId($form, 'use_type');
$field_use_percent = Html::getInputId($form, 'use_percent');

$script = <<< JS
$('#$field_use_type').on('change', function(){
	var form = $(this).parents('form');
	var field = form.find('div[data-use-type="' +this.value+ '"]');
	form.find('div[data-use-type]').hide();
	if (field.length) field.show();
});
$('#$field_use_type').each(function(){
	var form = $(this).parents('form');
	var field = form.find('div[data-use-type="' +this.value+ '"]');
	if (field.length) field.show();
});
$('#$field_use_percent').on('change', function(){
	var form = $(this).parents('form');
	var selected = parseInt(this.value);
	var fields = form.find('div[data-use-percent]');

	fields.each(function(){
		if (jQuery(this).data('use-percent').indexOf(selected) > -1) {
			jQuery(this).show();
		} else {
			jQuery(this).hide();
		}
	});
});
$('#$field_use_percent').each(function(){
	var form = $(this).parents('form');
	var selected = parseInt(this.value);
	var fields = form.find('div[data-use-percent]');

	fields.each(function(){
		if (jQuery(this).data('use-percent').indexOf(selected) > -1) {
			jQuery(this).show();
		} else {
			jQuery(this).hide();
		}
	});
});
JS;
$this->registerJs($script);