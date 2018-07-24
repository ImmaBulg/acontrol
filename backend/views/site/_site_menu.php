<?php

use yii\bootstrap\Nav;
use common\helpers\Html;
use common\widgets\Dropdown;
use common\models\Tenant;
use common\models\TenantGroup;
use common\models\MeterChannelGroup;
use common\models\Meter;
use common\models\SiteContact;
use common\models\SiteIpAddress;
?>
<div class="page-header">
	<div class="btn-toolbar pull-right">
		<?php if (Yii::$app->user->can('SiteManager') || Yii::$app->user->can('SiteManagerOwner') || Yii::$app->user->can('SiteManagerSiteOwner')): ?>
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Issue bill'), ['/site/reports', 'id' => $model->id], ['class' => 'btn btn-success btn-sm']); ?>
			</div>
		<?php endif; ?>
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Tenants ({count})', ['count' => $model->getRelationTenants()->andWhere(['status' => Tenant::STATUS_ACTIVE])->count()]), ['/site/tenants', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Tenant groups ({count})', ['count' => $model->getRelationTenantGroups()->andWhere(['status' => TenantGroup::STATUS_ACTIVE])->count()]), ['/site/tenant-groups', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Channel groups ({count})', ['count' => $model->getRelationMeterChannelGroups()->andWhere(['status' => MeterChannelGroup::STATUS_ACTIVE])->count()]), ['/site/meter-channel-groups', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Contacts ({count})', ['count' => $model->getRelationSiteContacts()->andWhere(['status' => SiteContact::STATUS_ACTIVE])->count()]), ['/site-contact/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Meters ({count})', ['count' => $model->getRelationMeters()->andWhere(['status' => Meter::STATUS_ACTIVE])->count()]), ['/meter/list', 'Meter[site_name]' => $model->name], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'IP ({count})', ['count' => $model->getRelationSiteIpAddresses()->andWhere(['status' => SiteIpAddress::STATUS_ACTIVE])->count()]), ['/site-ip-address/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Energy tree'), ['/site-meter/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
	</div>
	<h1><?php echo Yii::t('backend.view', 'Site - {name}', ['name' => $model->name]); ?></h1>
</div>

<?php if (Yii::$app->user->can('SiteManager') || Yii::$app->user->can('SiteManagerOwner') || Yii::$app->user->can('SiteManagerSiteOwner')): ?>
	<div class="pull-right">
		<?php echo Html::a(Yii::t('backend.view', 'Generate password and send email for users'), ['/site/generate-users-password', 'id' => $model->id], [
			'class' => 'btn btn-primary btn-sm',
			'data' => [
				'toggle' => 'confirm',
				'confirm-post' => true,
				'confirm-text' => Yii::t('backend.view', 'Are you sure you want to generate new password and send email for all users of this site?'),
				'confirm-button' => Yii::t('backend.view', 'Generate password and send email'),
			],
		]); ?>
	</div>
<?php endif; ?>

<?php echo Nav::widget([
	'options' => [
		'id' => 'site-nav',
		'class' => 'nav-tabs',
	],
	'items' => [
		[
			'label' => Yii::t('backend.view', 'View'),
			'url' => ['/site/view', 'id' => $model->id],
		],
		[
			'label' => Yii::t('backend.view', 'Edit'),
			'url' => ['/site/edit', 'id' => $model->id],
			'visible' => (Yii::$app->user->can('SiteManager') || Yii::$app->user->can('SiteManagerOwner') || Yii::$app->user->can('SiteManagerSiteOwner')),
		],
	],
]); ?>
