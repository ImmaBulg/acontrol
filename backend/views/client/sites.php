<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\models\Tenant;
use common\models\TenantGroup;
use common\models\MeterChannelGroup;
use common\models\Rate;
use common\models\Site;
use common\models\Meter;
use common\models\SiteContact;
use common\models\SiteIpAddress;
use common\models\SiteBillingSetting;

$this->title = Yii::t('backend.view', '{name} / Sites', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->name,
	'url' => ['/client/view', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Sites');
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('SiteManagerOwner')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new site'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/client/site-create', 'id' => $model->id]),
						],
					],
				]); ?>
			</div>
		</div>
	<?php endif; ?>
	<h1><?php echo $this->title; ?></h1>
</div>
<?php echo GridView::widget([
	'dataProvider' => $data_provider,
	'filterModel' => $filter_model,
	'id' => 'table-client-sites-list',
	'columns' => [
		'id',
		[
			'attribute' => 'site_name',
			'format' => 'raw',
			'value' => function($model){
				return Html::a($model->name, ['/site/view', 'id' => $model->id]);
			},
		],
		[
			'attribute' => 'electric_company_id',
		],
		[
			'attribute' => 'to_issue',
			'value' => 'aliasToIssue',
			'filter' => Site::getListToIssues(),
		],
		[
			'attribute' => 'rate_type_id',
			'value' => 'relationSiteBillingSetting.aliasRateType',
			'filter' => Rate::getListRateTypes(),
		],
		[
			'attribute' => 'fixed_payment',
			'value' => 'relationSiteBillingSetting.fixed_payment',
			'format' => 'round',
		],
		[
			'attribute' => 'square_meters',
			'value' => 'squareMeters',
			'format' => 'round',
		],
		[
			'format' => 'raw',
			'value' => function ($model){
				$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Tenants ({count})', ['count' => $model->getRelationTenants()->andWhere(['status' => Tenant::STATUS_ACTIVE])->count()]), ['/site/tenants', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']). '</div>';
				$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Tenant groups ({count})', ['count' => $model->getRelationTenantGroups()->andWhere(['status' => TenantGroup::STATUS_ACTIVE])->count()]), ['/site/tenant-groups', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']). '</div>';
				$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Channel groups ({count})', ['count' => $model->getRelationMeterChannelGroups()->andWhere(['status' => MeterChannelGroup::STATUS_ACTIVE])->count()]), ['/site/meter-channel-groups', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']). '</div>';
				$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Contacts ({count})', ['count' => $model->getRelationSiteContacts()->andWhere(['status' => SiteContact::STATUS_ACTIVE])->count()]), ['/site-contact/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']). '</div>';
				$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Meters ({count})', ['count' => $model->getRelationMeters()->andWhere(['status' => Meter::STATUS_ACTIVE])->count()]), ['/meter/list', 'Meter[site_name]' => $model->name], ['class' => 'btn btn-info btn-sm']). '</div>';
				$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'IP ({count})', ['count' => $model->getRelationSiteIpAddresses()->andWhere(['status' => SiteIpAddress::STATUS_ACTIVE])->count()]), ['/site-ip-address/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']). '</div>';
				$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Energy tree'), ['/site-meter/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']). '</div>';
				$btn[] = '<div class="btn-group">'.
							Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-primary btn-sm', 'data' => ['toggle' => 'dropdown']]).
							Dropdown::widget([
								'items' => [
									[
										'label' => Yii::t('backend.view', 'View'),
										'url' => ['/site/view', 'id' => $model->id],
									],
									[
										'label' => Yii::t('backend.view', 'Edit'),
										'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/site/edit', 'id' => $model->id]),
										'visible' => (Yii::$app->user->can('SiteManagerOwner')),
									],
									[
										'label' => Yii::t('backend.view', 'Delete'),
										'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/site/delete', 'id' => $model->id]),
										'visible' => (Yii::$app->user->can('SiteManagerOwner')),
										'linkOptions' => [
											'data' => [
												'toggle' => 'confirm',
												'confirm-post' => true,
												'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this site?'),
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