<?php
use common\models\Site;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use common\widgets\Select2;
use common\models\ReportFile;
use common\models\Report;
use common\models\Meter;
use common\models\helpers\reports\ReportGenerator;

$this->title = Yii::t('backend.view', 'Reports');
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
	'label' => Yii::t('backend.view', 'Tenants'),
	'url' => ['/site/tenants', 'id' => $model->relationSite->id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->name,
	'url' => ['/tenant/view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Reports');
?>
<div class="page-header">
	<h1><?php echo $model->name. " / " .Yii::t('backend.view', 'Reports'); ?></h1>
</div>
<?php if(Yii::$app->user->can('ReportManager') || Yii::$app->user->can('ReportManagerOwner')  || Yii::$app->user->can('ReportManagerSiteOwner') || Yii::$app->user->can('ReportManagerTenantOwner')): ?>
	<div class="well">
		<?php $form_active = ActiveForm::begin([
			'id' => 'form-report-create',
			'method' => 'GET',
			'action' => ['/tenant/reports', 'id' => $model->id],
		]); ?>
			<fieldset>
				<?php echo $form_active->errorSummary($form); ?>
				<div class="row">
					<div class="col-lg-3">
						<?php echo $form_active->field($form, 'from_date')->dateInput([
							'placeholder' => $form->getAttributeLabel('from_date'),
						])->label(false)->error(false); ?>
					</div>
					<div class="col-lg-3">
						<?php echo $form_active->field($form, 'to_date')->dateInput([
							'placeholder' => $form->getAttributeLabel('to_date'),
						])->label(false)->error(false); ?>
					</div>
					<div class="col-lg-3">
						<?php echo $form_active->field($form, 'type')->widget(Select2::classname(), [
							'data' => Report::getTenantListTypes(),
							'options' => [
								'placeholder' => $form->getAttributeLabel('type'),
							],
						])->label(false)->error(false); ?>
					</div>
					<div class="col-lg-3">
						<?php echo Html::submitInput(Yii::t('backend.view', 'Create'), ['class' => 'btn btn-success']); ?>
					</div>
				</div>
				<div class="row" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS_KWH]); ?>">
					<div class="col-lg-3">
						<?php echo $form_active->field($form, 'electric_company_pisga')->textInput([
							'placeholder' => $form->getAttributeLabel('electric_company_pisga'),
						])->label(false)->error(false); ?>
					</div>
					<div class="col-lg-3">
						<?php echo $form_active->field($form, 'electric_company_geva')->textInput([
							'placeholder' => $form->getAttributeLabel('electric_company_geva'),
						])->label(false)->error(false); ?>
					</div>
					<div class="col-lg-3">
						<?php echo $form_active->field($form, 'electric_company_shefel')->textInput([
							'placeholder' => $form->getAttributeLabel('electric_company_shefel'),
						])->label(false)->error(false); ?>
					</div>
				</div>
                <div class="row" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS]); ?>">
                    <div class="col-lg-12">
                        <div class="row" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS]); ?>">
                            <div class="col-lg-3">
                                <?php echo $form_active->field($form, 'column_fixed_payment')->checkbox()->error(false); ?>
                               <!-- --><?php /*echo $form_active->field($form, 'is_vat_included')->checkbox()->error(false); */?>
                                <div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS]); ?>">
                                    <?php echo $form_active->field($form, 'column_total_pay_single_channel_rules')->checkbox()->error(false); ?>
                                </div>
                            </div>
                            <div class="col-lg-3" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS]); ?>">
                                <?php echo $form_active->field($form, 'column_total_pay_group_load_rules')->checkbox()->error(false); ?>
                                <?php echo $form_active->field($form, 'column_total_pay_fixed_load_rules')->checkbox()->error(false); ?>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="row" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS_KWH]); ?>">
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-3">
								<?php echo $form_active->field($form, 'electric_company_price')->textInput([
									'placeholder' => $form->getAttributeLabel('electric_company_price'),
								])->label(false)->error(false); ?>
							</div>
						</div>
						<div class="row" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS_KWH]); ?>">
							<div class="col-lg-3">
								<?php echo $form_active->field($form, 'column_fixed_payment')->checkbox()->error(false); ?>
								<?php /*echo $form_active->field($form, 'is_vat_included')->checkbox()->error(false); */?>
								<div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS]); ?>">
									<?php echo $form_active->field($form, 'column_total_pay_single_channel_rules')->checkbox()->error(false); ?>
								</div>
							</div>
							<div class="col-lg-3" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_NIS]); ?>">
								<?php echo $form_active->field($form, 'column_total_pay_group_load_rules')->checkbox()->error(false); ?>
								<?php echo $form_active->field($form, 'column_total_pay_fixed_load_rules')->checkbox()->error(false); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="row" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_RATES_COMPRASION]); ?>">
					<div class="col-lg-3">
						<?php echo $form_active->field($form, 'electric_company_rate_low')->textInput([
							'placeholder' => $form->getAttributeLabel('electric_company_rate_low'),
						])->label(false)->error(false); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-3">
						<?php echo $form_active->field($form, 'format_pdf')->checkbox()->error(false); ?>
						<?php echo $form_active->field($form, 'format_excel')->checkbox()->error(false); ?>
						<div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_TENANT_BILLS]); ?>">
							<?php echo $form_active->field($form, 'format_dat')->checkbox()->error(false); ?>
						</div>
					</div>
					<div class="col-lg-3" style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_METERS, Report::TYPE_RATES_COMPRASION]); ?>">
						<?php echo $form_active->field($form, 'order_by')->widget(Select2::classname(), [
							'data' => ReportGenerator::getListOrderBy(),
							'options' => [
								'placeholder' => $form->getAttributeLabel('order_by'),
							],
						])->label(false)->error(false); ?>
					</div>

					<div class="col-lg-3">
						<div style="display:none;" data-type="<?php echo Json::encode([Report::TYPE_TENANT_BILLS, Report::TYPE_NIS, Report::TYPE_KWH, Report::TYPE_NIS_KWH]); ?>">
							<?php echo $form_active->field($form, 'is_import_export_separatly')->checkbox()->error(false); ?>
						</div>
					</div>
                    <!--<div class="col-lg-3">
                        <div style="display:none;" data-type="<?php /*echo Json::encode([Report::TYPE_TENANT_BILLS]); */?>">
                            <?php /*echo $form_active->field($form, 'report_calculation_type')->dropDownList(Report::getTenantBillReportTypes())->label(false); */?>
                        </div>
                    </div>-->
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
<?php endif; ?>
<?php echo GridView::widget([
	'dataProvider' => $data_provider,
	'filterModel' => $filter_model,
	'id' => 'table-report-list',
	'rowOptions' => function ($model, $key, $index, $grid) {
		if ($model->is_automatically_generated) {
			return ['class' => "color color-lightgreen"];
		}
	},
	'columns' => [
		'id',
		[
			'attribute' => 'site_name',
			'format' => 'raw',
			'value' => function($model){
				return Html::a($model->relationSite->name, ['/site/view', 'id' => $model->site_id]);
			},
		],
		[
			'attribute' => 'type',
			'filter' => Report::getListTypes(),
			'value' => function($model) {
				$type = ArrayHelper::getValue(Report::getListTypes(), $model->type, $model->type);

				/*switch ($model->data_usage_method) {
					case Meter::DATA_USAGE_METHOD_IMPORT:
					case Meter::DATA_USAGE_METHOD_IMPORT_PLUS_EXPORT:
					case Meter::DATA_USAGE_METHOD_IMPORT_MINUS_EXPORT:
					case Meter::DATA_USAGE_METHOD_EXPORT:
						return implode(' ', [
							$type,
							'(' .ArrayHelper::getValue(Meter::getListDataUsageMethods(), $model->data_usage_method). ')',
						]);
					
					default:
						return $type;
				}*/
				return $type;
			},
		],
		[
			'attribute' => 'from_date',
			'format' => 'date',
			'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
		],
		[
			'attribute' => 'to_date',
			'format' => 'date',
			'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
		],
		[
			'attribute' => 'created_at',
			'format' => 'dateTime',
			'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
		],
		[
			'attribute' => 'issued_by',
			'format' => 'raw',
			'value' => function ($model){
				if (($relationUserCreator = $model->relationUserCreator) != null) {
					return Html::a($relationUserCreator->name, ['/user/view', 'id' => $relationUserCreator->id]);
				}
			},
		],
		// [
		// 	'attribute' => 'is_automatically_generated',
		// 	'filter' => Report::getListYesNo(),
		// 	'value' => function($model){
		// 		return ArrayHelper::getValue(Report::getListYesNo(), $model->is_automatically_generated);
		// 	},
		// ],
		[
			'format' => 'raw',
			'value' => function ($model){
				$btn[] = '<div class="btn-group">'.
							Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-primary btn-sm', 'data' => ['toggle' => 'dropdown']]).
							Dropdown::widget([
								'items' => [
									[
										'label' => Yii::t('backend.view', 'PDF'),
										'url' => $model->getFilePath(ReportFile::FILE_TYPE_PDF),
										'linkOptions' => [
											'target' => '_blank',
										],
									],
									[
										'label' => Yii::t('backend.view', 'Excel'),
										'url' => $model->getFilePath(ReportFile::FILE_TYPE_EXCEL),
										'linkOptions' => [
											'target' => '_blank',
										],
									],
									[
										'label' => Yii::t('backend.view', 'DAT'),
										'url' => $model->getFilePath(ReportFile::FILE_TYPE_DAT),
										'linkOptions' => [
											'target' => '_blank',
										],
									],
									[
										'label' => Yii::t('backend.view', 'TXT'),
										'url' => $model->getFilePath(ReportFile::FILE_TYPE_TXT),
										'linkOptions' => [
											'target' => '_blank',
										],
									],
									'<li class="divider"></li>',
									[
										'label' => Yii::t('backend.view', 'Delete'),
										'url' => ['/report/delete', 'id' => $model->id],
										'visible' => (Yii::$app->user->can('ReportManager') || Yii::$app->user->can('ReportManagerOwner')  || Yii::$app->user->can('ReportManagerSiteOwner') || Yii::$app->user->can('ReportManagerTenantOwner')),
										'linkOptions' => [
											'data' => [
												'toggle' => 'confirm',
												'confirm-post' => true,
												'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this report?'),
												'confirm-button' => Yii::t('backend.view', 'Delete'),
											],
										],
									],
								],
							]).
						'</div>';
				return '<div class="btn-toolbar pull-right">' .implode('', $btn). '</div>';
			}
		],
	],
]); ?>