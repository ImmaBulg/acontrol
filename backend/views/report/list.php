<?php

use yii\db\Query;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use common\models\Report;
use common\models\ReportFile;
use common\models\TenantReport;
use common\widgets\ActiveForm;
use common\models\Task;
use common\models\Meter;
use common\components\i18n\Formatter;
use common\components\i18n\LanguageSelector;
use backend\models\forms\FormReports;

$this->title = Yii::t('backend.view', 'Reports history');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Reports history');
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('ReportManager') || Yii::$app->user->can('ReportManagerOwner')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php
					$lang_items = [];

					foreach (LanguageSelector::getSupportedLanguages() as $lang_prefix => $lang_name) {
						if ($lang_prefix == Report::getReportLanguage()) {
							$lang_items[] = [
								'label' => $lang_name,
								'url' => '#',
								'options' => ['class' => 'disabled'],
							];
						} else {
							$lang_items[] = [
								'label' => $lang_name,
								'url' => ['/report/toggle-language', 'value' => $lang_prefix],
								'linkOptions' => ['data' => ['method' => 'post']],
							];
						}
					}
				?>
				<?php echo Html::a(Yii::t('backend.view', 'Report language: {language}', [
					'language' => LanguageSelector::getAliasSupportedLanguage(Report::getReportLanguage()),
				]). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-info', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => $lang_items,
				]); ?>
			</div>
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new report'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/report/create']),
						],
					],
				]); ?>
			</div>
		</div>
	<?php endif; ?>
	<h1><?php echo $this->title; ?></h1>
</div>
<?php $form_active = ActiveForm::begin([
	'id' => 'form-reports',
	'enableClientValidation' => false,
	'method' => 'GET',
	'action' => ['/report/list', 'Report[level]' => Report::LEVEL_SITE],
]); ?>
<fieldset>
	<?php if (Yii::$app->user->can('ReportManager')): ?>
		<?php echo $form_active->errorSummary($form_reports); ?>
		<div class="row">
			<div class="col-lg-3">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Delete selected rows'), ['class' => 'btn btn-warning']); ?>
				<?php echo $form_active->field($form_reports, 'is_delete')->hiddenInput()->label(false); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php echo GridView::widget([
		'dataProvider' => $data_provider,
		'filterModel' => $filter_model,
		'id' => 'table-report-list',
        'options' => [
            'class' => 'table table-striped table-primary',
        ],
		'rowOptions' => function ($model, $key, $index, $grid) {
			if ($model->is_automatically_generated) {
				return ['class' => "color color-lightgreen"];
			}
		},
		'columns' => [
			[
				'class' => 'common\widgets\CheckboxColumn',
				'name' => FormReports::REPORTS_FIELD_NAME,
				'visible' => Yii::$app->user->can('ReportManager'),
			],
			'id',
			[
				'attribute' => 'site_owner_name',
				'format' => 'raw',
				'value' => function($model){
					return Html::a($model->relationSiteOwner->name, ['/client/view', 'id' => $model->site_owner_id]);
				},
			],
			[
				'attribute' => 'site_name',
				'format' => 'raw',
				'value' => function($model){
					return Html::a($model->relationSite->name, ['/site/view', 'id' => $model->site_id]);
				},
			],
			[
				'attribute' => 'tenant_name',
				'format' => 'raw',
				'value' => function($model){
					if ($model->level != Report::LEVEL_SITE) {
						if ($tenant = $model->getRelationTenantReports()->one()) {
							return Html::a($tenant->relationTenant->name, ['/tenant/view', 'id' => $tenant->tenant_id]);
						}
					}

					return '';
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
				'attribute' => 'is_public',
				'format' => 'raw',
				'filter' => Report::getListYesNo(),
				'value' => function($model){
					if ($model->is_public) {
						return Html::a(Yii::t('backend.view', 'Unpublish from client'), ['/report/unpublish', 'id' => $model->id], [
							'class' => 'btn btn-default btn-sm',
							'data' => [
								'toggle' => 'confirm',
								'confirm-post' => true,
								'confirm-text' => Yii::t('backend.view', 'Are you sure you want to unpublish this report from client?'),
								'confirm-button' => Yii::t('backend.view', 'Unpublish'),
							],
						]);
					} else {
						$confirm_button_class = 'btn btn-primary';

						if ($model->level == Report::LEVEL_SITE && $model->type == Report::TYPE_TENANT_BILLS && ($separate_reports = Report::find()->andWhere(['parent_id' => $model->id])->column()) == null) {
							$confirm_button_class .= ' btn-loading'; 
						}

						return Html::a(Yii::t('backend.view', 'Publish to client'), ['/report/publish', 'id' => $model->id], [
							'class' => 'btn btn-default btn-sm',
							'data' => [
								'toggle' => 'confirm',
								'confirm-post' => true,
								'confirm-text' => Yii::t('backend.view', 'Are you sure you want to publish this report to client?'),
								'confirm-button' => Yii::t('backend.view', 'Publish'),
								'confirm-button-class' => $confirm_button_class,
							],
						]);
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
				'attribute' => 'level',
				'value' => 'aliasLevel',
				'filter' => Report::getListLevels(),
			],
			[
				'attribute' => 'created_at',
				'format' => 'dateTime',
				'contentOptions' => ['style' => 'min-width:105px;'],
				'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
			],
			[
				'attribute' => 'issued_by',
				'format' => 'raw',
				'contentOptions' => ['style' => 'min-width:100px;'],
				'value' => function ($model){
					if (($relationUserCreator = $model->relationUserCreator) != null) {
						return Html::a($relationUserCreator->name, ['/user/view', 'id' => $relationUserCreator->id]);
					}
				},
			],
			[
				'format' => 'raw',
				'contentOptions' => ['style' => 'min-width:250px;'],
				'value' => function ($model){
					if ($model->level == Report::LEVEL_SITE && $model->type == Report::TYPE_TENANT_BILLS) {
						if ($separate_reports = Report::find()->andWhere(['parent_id' => $model->id])->column()) {
							$btn[] = '<div class="btn-group">'
										.Html::a(Yii::t('backend.controller', 'View separate reports '), ['/report/list', 'Report[id]' => implode(',', $separate_reports)], [
											'class' => 'btn btn-sm btn-info',
										]).
									'</div>';
						} else {
							// $btn[] = '<div class="btn-group">'
							// 			.Html::a(Yii::t('backend.view', 'Generate separate reports'), ['/report/create-separate-reports', 'id' => $model->id], [
							// 				'class' => 'btn btn-info btn-sm btn-loading',
							// 				'data' => ['method' => 'post'],
							// 			]).
							// 		'</div>';
						}
					}

					$sql_date_format = Formatter::SQL_DATE_FORMAT;

					if (!$model->is_automatically_generated && $model->relationSite->getRelationTasks()->andWhere([
						'and',
						['type' => Task::TYPE_ISSUE_ALERT],
						["DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format')" => Yii::$app->formatter->asDate($model->created_at, Formatter::PHP_DATE_FORMAT)],
					])->count()) {
						$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Issued alerts'), [
								'/task/list',
								'Task[type]' => Task::TYPE_ISSUE_ALERT,
								'Task[site_name]' => $model->relationSite->name,
								'from_date' => Yii::$app->formatter->asDate($model->created_at, Formatter::PHP_DATE_FORMAT),
								'to_date' => Yii::$app->formatter->asDate($model->created_at, Formatter::PHP_DATE_FORMAT),
						], ['class' => 'btn btn-warning btn-sm']). '</div>';
					}

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
											'visible' => (Yii::$app->user->can('ReportManager') || Yii::$app->user->can('ReportManagerOwner')),
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
</fieldset>
<?php ActiveForm::end(); ?>
<?php $this->registerJs('
jQuery(".btn-loading").on("click", function(event){
	jQuery("body").append("<div id=\"report-overlay\"></div>");
	jQuery("body").append("<div id=\"report-spinner-holder\">' .Yii::t('backend.view', 'Creating single tenant reports'). '<div id=\"report-spinner\"><div class=\"rect rect1\"></div><div class=\"rect rect2\"></div><div class=\"rect rect3\"></div><div class=\"rect rect4\"></div><div class=\"rect rect5\"></div></div></div>");
});
jQuery(window).on("shown.bs.modal", function() { 
	jQuery(".btn-loading").on("click", function(event){
		jQuery("body").append("<div id=\"report-overlay\"></div>");
		jQuery("body").append("<div id=\"report-spinner-holder\">' .Yii::t('backend.view', 'Creating single tenant reports'). '<div id=\"report-spinner\"><div class=\"rect rect1\"></div><div class=\"rect rect2\"></div><div class=\"rect rect3\"></div><div class=\"rect rect4\"></div><div class=\"rect rect5\"></div></div></div>");
	});
});'); ?>