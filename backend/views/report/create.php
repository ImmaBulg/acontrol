<?php

use yii\helpers\Url;
use yii\helpers\Json;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\widgets\DepDrop;
use common\models\Site;
use common\models\Report;
use common\models\helpers\reports\ReportGenerator;

$this->title = Yii::t('backend.view', 'Create a report');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Reports history'),
	'url' => ['/report/list'],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a report');
?>
<h1 class="page-header"><?php echo $this->title; ?></h1>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-report-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<?php echo $form_active->errorSummary($form); ?>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'site_owner_id')->widget(Select2::classname(), [
						'data' => Site::getListUsers(),
					])->error(false); ?>
					<?php echo $form_active->field($form, 'site_id')->widget(DepDrop::classname(), [
						'type' => DepDrop::TYPE_SELECT2,
						'pluginOptions' => [
							'depends' => [Html::getInputId($form, 'site_owner_id')],
							'url' => Url::to([
								'/form-dependent/user-sites',
							]),
							'initialize' => true,
							'placeholder' => false,
							'loadingText' => Yii::t('backend.view', 'Loading'),
						],
					])->error(false); ?>
					<?php echo $form_active->field($form, 'from_date')->dateInput()->error(false); ?>
					<?php echo $form_active->field($form, 'to_date')->dateInput()->error(false); ?>
				</div>
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'type')->widget(Select2::classname(), [
						'data' => Report::getListTypes(),
					])->error(false); ?>
					<div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS_KWH]); ?>">
						<?php echo $form_active->field($form, 'electric_company_pisga')->textInput()->error(false); ?>
						<?php echo $form_active->field($form, 'electric_company_geva')->textInput()->error(false); ?>
						<?php echo $form_active->field($form, 'electric_company_shefel')->textInput()->error(false); ?>
					</div>
                    <div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS]); ?>">
                        <?php echo $form_active->field($form, 'column_fixed_payment')->checkbox()->error(false); ?>
                        <?php echo $form_active->field($form, 'is_vat_included')->checkbox()->error(false); ?>
                        <div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS]); ?>">
                            <?php echo $form_active->field($form, 'column_total_pay_single_channel_rules')->checkbox()->error(false); ?>
                            <?php echo $form_active->field($form, 'column_total_pay_group_load_rules')->checkbox()->error(false); ?>
                            <?php echo $form_active->field($form, 'column_total_pay_fixed_load_rules')->checkbox()->error(false); ?>
                        </div>
                    </div>
					<div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS_KWH]); ?>">
						<?php echo $form_active->field($form, 'electric_company_price')->textInput()->error(false); ?>
						<?php echo $form_active->field($form, 'column_fixed_payment')->checkbox()->error(false); ?>
						<?php echo $form_active->field($form, 'is_vat_included')->checkbox()->error(false); ?>
						<div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS]); ?>">
							<?php echo $form_active->field($form, 'column_total_pay_single_channel_rules')->checkbox()->error(false); ?>
							<?php echo $form_active->field($form, 'column_total_pay_group_load_rules')->checkbox()->error(false); ?>
							<?php echo $form_active->field($form, 'column_total_pay_fixed_load_rules')->checkbox()->error(false); ?>
						</div>
					</div>
					<div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_RATES_COMPRASION]); ?>">
						<?php echo $form_active->field($form, 'electric_company_rate_low')->textInput()->error(false); ?>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<?php echo $form_active->field($form, 'days_with_no_data')->textInput()->error(false); ?>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-4">
							<?php echo $form_active->field($form, 'format_pdf')->checkbox()->error(false); ?>
							<?php echo $form_active->field($form, 'format_excel')->checkbox()->error(false); ?>
							<div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_TENANT_BILLS]); ?>">
								<?php echo $form_active->field($form, 'format_dat')->checkbox()->error(false); ?>
							</div>
						</div>
						<div class="col-lg-4" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_METERS, Report::TYPE_RATES_COMPRASION]); ?>">
							<?php echo $form_active->field($form, 'order_by')->widget(Select2::classname(), [
								'data' => ReportGenerator::getListOrderBy(),
								'options' => [
									'placeholder' => $form->getAttributeLabel('order_by'),
								],
							])->label(false)->error(false); ?>
						</div>
						<div class="col-lg-4">
							<?php echo $form_active->field($form, 'group_use_percent')->checkbox()->error(false); ?>
							<div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_TENANT_BILLS, Report::TYPE_NIS, Report::TYPE_KWH, Report::TYPE_NIS_KWH]); ?>">
								<?php echo $form_active->field($form, 'is_import_export_separatly')->checkbox()->error(false); ?>
							</div>
						</div>
					</div>
					<div class="row" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_TENANT_BILLS]); ?>">
						<div class="col-lg-12">
							<?php echo $form_active->field($form, 'power_factor')->inline()->radioList(Site::getListPowerFactors())->error(false); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
	<?php $this->registerJs('jQuery("#form-report-create").on("beforeSubmit", function(event){
		jQuery("body").append("<div id=\"report-overlay\"></div>");
		jQuery("body").append("<div id=\"report-spinner-holder\">' .Yii::t('backend.view', 'Creating report'). '<div id=\"report-spinner\"><div class=\"rect rect1\"></div><div class=\"rect rect2\"></div><div class=\"rect rect3\"></div><div class=\"rect rect4\"></div><div class=\"rect rect5\"></div></div></div>");
	});'); ?>
</div>
<?php
$field_type = Html::getInputId($form, 'type');

$script = <<< JS
$('#$field_type').on('change', function(){
	var value = this.value;
	var form = $(this).parents('form');
	var fields = form.find('div[data-type]');

	fields.hide();
	fields.each(function(){
		var field = jQuery(this);
		if (jQuery.inArray(parseInt(value), field.data('type')) > -1) {
			field.show();
		}
	});
});
$('#$field_type').each(function(){
	var value = this.value;
	var form = $(this).parents('form');
	var fields = form.find('div[data-type]');

	fields.hide();
	fields.each(function(){
		var field = jQuery(this);
		if (jQuery.inArray(parseInt(value), field.data('type')) > -1) {
			field.show();
		}
	});
});
JS;
$this->registerJs($script);
?>