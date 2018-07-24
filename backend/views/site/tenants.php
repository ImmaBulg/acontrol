<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\models\Rate;
use common\models\Site;
use common\models\TenantContact;
use common\models\RuleSingleChannel;
use backend\models\forms\FormTenants;

$this->title = Yii::t('backend.view', '{name} / Tenants', ['name' => $model->name]);
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
	'label' => $model->name,
	'url' => ['/site/view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Tenants');
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner') || Yii::$app->user->can('TenantManagerSiteOwner')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Create / update users from all tenants'), ['/site/tenants-users', 'id' => $model->id], [
					'class' => 'btn btn-primary',
					'data' => [
						'toggle' => 'confirm',
						'confirm-post' => true,
						'confirm-text' => Yii::t('backend.view', 'Are you sure you want to create / update users from all tenants of this site?'),
						'confirm-button' => Yii::t('backend.view', 'Create / update users from all tenants'),
					],
				]); ?>
			</div>
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new tenant'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/site/tenant-create', 'id' => $model->id]),
						],
					],
				]); ?>
			</div>
		</div>
	<?php endif; ?>
	<h1><?php echo $this->title; ?></h1>
</div>
<?php $form_active = ActiveForm::begin([
	'id' => 'form-tenants-edit',
	'enableClientValidation' => false,
	'method' => 'GET',
	'action' => ['/site/tenants', 'id' => $model->id],
]); ?>
<fieldset>
	<?php if (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner') || Yii::$app->user->can('TenantManagerSiteOwner')): ?>
		<?php echo $form_active->errorSummary($form_tenants); ?>
		<div class="row">
			<div class="col-lg-3">
				<?php echo $form_active->field($form_tenants, 'rate_type_id')->widget(Select2::classname(), [
					'data' => ArrayHelper::merge(['' => Yii::t('backend.view', 'Not set')], Rate::getListRateTypes()),
				])->error(false); ?>
			</div>
			<div class="col-lg-3">
				<?php echo $form_active->field($form_tenants, 'fixed_payment')->textInput(['allow_only' => Html::TYPE_NUMBER])->error(false); ?>
			</div>
			<div class="col-lg-3">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-primary control-label-offset']); ?>
			</div>
			<div class="col-lg-3">
				<div class="pull-right">
					<?php if ($expired): ?>
						<?php echo Html::a(Yii::t('backend.view', 'Show active tenants'), Url::current(['expired' => false]), ['class' => 'btn btn-default control-label-offset']); ?>
					<?php else: ?>
						<?php echo Html::a(Yii::t('backend.view', 'Show expired tenants'), Url::current(['expired' => true]), ['class' => 'btn btn-default control-label-offset']); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php echo GridView::widget([
		'dataProvider' => $data_provider,
		'filterModel' => $filter_model,
		'id' => 'table-tenants-list',
		'columns' => [
			[
				'class' => 'common\widgets\CheckboxColumn',
				'name' => FormTenants::TENANTS_FIELD_NAME,
				'visible' => (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner') || Yii::$app->user->can('TenantManagerSiteOwner')),
			],
			'id',
			[
				'attribute' => 'tenant_name',
				'format' => 'raw',
				'value' => function($model){
					return Html::a($model->name, ['/tenant/view', 'id' => $model->id]);
				},
			],
			[
				'attribute' => 'to_issue',
				'value' => 'aliasToIssue',
				'filter' => Site::getListToIssues(),
			],
			[
				'attribute' => 'rate_type_id',
				'value' => 'aliasRateType',
				'format' => 'raw',
				'filter' => Rate::getListRateTypes(),
			],
			[
				'attribute' => 'fixed_payment',
				'format' => 'raw',
				'value' => 'aliasFixedPayment',
			],
			[
				'attribute' => 'entrance_date',
				'format' => 'date',
				'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
			],
			[
				'attribute' => 'exit_date',
				'format' => 'date',
				'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
			],
			[
				'attribute' => 'square_meters',
				'value' => function($model){
					$square_meters = Yii::$app->formatter->asRound($model->square_meters);
					$site_footage = Yii::$app->formatter->asPercentage($model->getAliasSiteFootage());
					return "$square_meters ($site_footage)";
				},
			],
			[
				'format' => 'raw',
				'value' => function ($model){
					$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Rules ({count})', ['count' => $model->getCountRules()]), ['/rule-single-channel/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']). '</div>';
					$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Contacts ({count})', ['count' => $model->getRelationTenantContacts()->andWhere(['status' => TenantContact::STATUS_ACTIVE])->count()]), ['/tenant-contact/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']). '</div>';
					$btn[] = '<div class="btn-group">'.
								Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-primary btn-sm', 'data' => ['toggle' => 'dropdown']]).
								Dropdown::widget([
									'items' => [
										[
											'label' => Yii::t('backend.view', 'View'),
											'url' => ['/tenant/view', 'id' => $model->id],
										],
										[
											'label' => Yii::t('backend.view', 'Edit'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/tenant/edit', 'id' => $model->id]),
											'visible' => (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner') || Yii::$app->user->can('TenantManagerSiteOwner')),
										],
										[
											'label' => Yii::t('backend.view', 'Delete'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/tenant/delete', 'id' => $model->id]),
											'visible' => (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner') || Yii::$app->user->can('TenantManagerSiteOwner')),
											'linkOptions' => [
												'data' => [
													'toggle' => 'confirm',
													'confirm-post' => true,
													'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this tenant?'),
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

<?php

$field_rate = Html::getInputId($form_tenants, 'rate_type_id');
$field_fixed_payment = Html::getInputId($form_tenants, 'fixed_payment');
$rate_fixed_payments_url = Url::to(['/json-search/rate-fixed-payment']);

$script = <<< JS
$('#$field_rate').on('change', function(){
	var element = $(this);
	$.getJSON('$rate_fixed_payments_url', {
		id: element.val()
	}, function(data){
		if (!$.isEmptyObject(data)) {
			$('#$field_fixed_payment').val(data.fixed_payment);
		} else {
			$('#$field_fixed_payment').val('');
		}
	});
});
$('#$field_rate').each(function(){
	var element = $(this);

	if (!$('#$field_fixed_payment').val()) {
		$.getJSON('$rate_fixed_payments_url', {
			id: element.val()
		}, function(data){
			if (!$.isEmptyObject(data)) {
				$('#$field_fixed_payment').val(data.fixed_payment);
			} else {
				$('#$field_fixed_payment').val('');
			}
		});
	}
});
JS;
$this->registerJs($script); ?>