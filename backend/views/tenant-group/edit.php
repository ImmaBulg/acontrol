<?php

use yii\helpers\Url;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\Site;
use common\models\Tenant;
use common\models\TenantGroup;
use common\widgets\Select2;
use common\widgets\DepDrop;

$this->title = Yii::t('backend.view', 'Tenant group - {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationUser->name,
	'url' => ['/client/view', 'id' => $model->relationUser->id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Sites'),
	'url' => ['/client/sites', 'id' => $model->relationUser->id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationSite->name,
	'url' => ['/site/view', 'id' => $model->relationSite->id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Tenant groups'),
	'url' => ['/site/tenant-groups', 'id' => $model->relationSite->id],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-tenant-group-edit',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'name')->textInput(); ?>
					<?php echo $form_active->field($form, 'site_id')->widget(Select2::classname(), [
						'data' => TenantGroup::getListSites($model->relationUser->id, $model->relationSite->id),
					]); ?>
					<?php echo $form_active->field($form, 'group_tenants')->multiSelect2Side(Site::getListTenants($form->site_id)); ?>
				</div>
			</div>
			<div class="form-group">
				<div id="tenant-rules-table">
					<div class="blankstate"><?php echo Yii::t('backend.view', 'Select a tenant to view his rules'); ?></div>
					<div class="content"></div>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>
<?php
$field_site_id = Html::getInputId($form, 'site_id');
$field_tenants = Html::getInputId($form, 'group_tenants[]');
$tenants_url = Url::to(['/json-search/site-tenants']);
$rules_url = Url::to(['/ajax-grid-view/tenant-rules']);

$script = <<< JS
$('#$field_site_id').on('change', function(){
	var element = $(this);
	var form = $(this).parents('form');

	$.getJSON('$tenants_url', {
		site_id: element.val()
	}, function(data){
		if (!$.isEmptyObject(data)) {
			$('#$field_tenants option').each(function(){
				$(this).remove();
			});
			for (key in data) {
				$('#$field_tenants').append($('<option></option>').attr('value', data[key].id).text(data[key].value));
			}
			$('#$field_tenants').multiselect2side('destroy').multiselect2side();
		} else {
			$('#$field_tenants option').each(function(){
				$(this).remove();
			});
		}
		multiSelect2SideOnClick();
	});
});
$('#$field_site_id').each(function(){
	multiSelect2SideOnClick();
});
$('#$field_tenants').siblings('.ms2side__div').find('.ms2side__select').on('change', function(){
	multiSelect2SideOnClick();
});
function multiSelect2SideOnClick()
{
	$('#tenant-rules-table .content').html('');
	$('#tenant-rules-table .blankstate').show();

	$('#$field_tenants').siblings('.ms2side__div').find('.ms2side__select option').on('click', function(){
		$('#tenant-rules-table .content').html('<div class="plugin-loading"></div>');

		jQuery.get('$rules_url', {
			id: this.value
		}, function(data){
			$('#tenant-rules-table .content').html(data);
			$('#tenant-rules-table .blankstate').hide();
		}).success(function(){});
	});
}
JS;
$this->registerJs($script);