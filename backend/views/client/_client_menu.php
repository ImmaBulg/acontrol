<?php

use yii\bootstrap\Nav;
use common\helpers\Html;
use common\models\Site;
use common\models\Tenant;
use common\models\UserContact;
?>
<div class="page-header">
	<div class="btn-toolbar pull-right">
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Sites ({count})', ['count' => $model->getRelationSites()->andWhere(['status' => Site::STATUS_ACTIVE])->count()]), ['/client/sites', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Tenants ({count})', ['count' => $model->getRelationTenants()->andWhere(['status' => Tenant::STATUS_ACTIVE])->count()]), ['/client/tenants', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Contacts ({count})', ['count' => $model->getRelationUserContacts()->andWhere(['status' => UserContact::STATUS_ACTIVE])->count()]), ['/client-contact/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
	</div>
	<h1><?php echo Yii::t('backend.view', 'Client - {name}', ['name' => $model->name]); ?></h1>
</div>
<?php echo Nav::widget([
	'options' => [
		'id' => 'client-nav',
		'class' => 'nav-tabs',
	],
	'items' => [
		[
			'label' => Yii::t('backend.view', 'View'),
			'url' => ['/client/view', 'id' => $model->id],
		],
		[
			'label' => Yii::t('backend.view', 'Add'),
			'options' => ['class' => 'pull-right'],
			'items' => [
				[
					'label' => Yii::t('backend.view', 'Add new site'),
					'url' => ['/client/site-create', 'id' => $model->id],
					'visible' => (Yii::$app->user->can('SiteManagerOwner')),
				],
				[
					'label' => Yii::t('backend.view', 'Add new tenant'),
					'url' => ['/client/tenant-create', 'id' => $model->id],
					'visible' => (Yii::$app->user->can('TenantManagerOwner')),
				],
				[
					'label' => Yii::t('backend.view', 'Add new contact'),
					'url' => ['/client-contact/create', 'id' => $model->id],
					'visible' => (Yii::$app->user->can('ClientManagerOwner')),
				],
			],	
		],
	],
]); ?>
