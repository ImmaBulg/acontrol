<?php
use yii\bootstrap\Nav;
use common\helpers\Html;
use common\widgets\Dropdown;
use common\models\TenantContact;
?>
<div class="page-header">
	<div class="btn-toolbar pull-right">
		<?php if (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner') || Yii::$app->user->can('TenantManagerSiteOwner') || Yii::$app->user->can('TenantManagerTenantOwner')): ?>
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Issue bill'), ['/tenant/reports', 'id' => $model->id], ['class' => 'btn btn-success btn-sm']); ?>
			</div>
		<?php endif; ?>
		<div class="btn-group">
			<?php echo Html::a(Yii::t('backend.view', 'Contacts ({count})', ['count' => $model->getRelationTenantContacts()->andWhere(['status' => TenantContact::STATUS_ACTIVE])->count()]), ['/tenant-contact/list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']); ?>
		</div>
	</div>
	<h1><?php echo Yii::t('backend.view', 'Tenant - {name}', ['name' => $model->name]); ?></h1>
</div>

<?php switch (Yii::$app->controller->id) {
	case 'rule-single-channel':
		echo Html::a(Yii::t('backend.view', 'Add rule'), ['/rule-single-channel/create', 'id' => $model->id], [
			'class' => 'btn btn-success btn-sm pull-right',
			'visible' => (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner'))
		]);
		break;
	
	case 'rule-group-load':
		echo Html::a(Yii::t('backend.view', 'Add rule'), ['/rule-group-load/create', 'id' => $model->id], [
			'class' => 'btn btn-success btn-sm pull-right',
			'visible' => (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner'))
		]);
		break;

	case 'rule-fixed-load':
		echo Html::a(Yii::t('backend.view', 'Add rule'), ['/rule-fixed-load/create', 'id' => $model->id], [
			'class' => 'btn btn-success btn-sm pull-right',
			'visible' => (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner'))
		]);
		break;

	default:
		# code...
		break;
} ?>
<?php echo Nav::widget([
	'options' => [
		'id' => 'tenant-nav',
		'class' => 'nav-tabs',
	],
	'items' => [
		[
			'label' => Yii::t('backend.view', 'View'),
			'url' => ['/tenant/view', 'id' => $model->id],
		],
		[
			'label' => Yii::t('backend.view', 'Edit'),
			'url' => ['/tenant/edit', 'id' => $model->id],
			'visible' => (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner') || Yii::$app->user->can('TenantManagerSiteOwner') || Yii::$app->user->can('TenantManagerTenantOwner')),
		],
		[
			'label' => Yii::t('backend.view', 'Single channel association rules'),
			'url' => ['/rule-single-channel/list', 'id' => $model->id],
		],
		[
			'label' => Yii::t('backend.view', 'Group load association rules'),
			'url' => ['/rule-group-load/list', 'id' => $model->id],
		],
		[
			'label' => Yii::t('backend.view', 'Fixed load association rules'),
			'url' => ['/rule-fixed-load/list', 'id' => $model->id],
		],
		// [
		// 	'label' => Yii::t('backend.view', 'Add'),
		// 	'options' => ['class' => 'btn btn-success btn-sm pull-right'],
		// 	'items' => [
		// 		[
		// 			'label' => Yii::t('backend.view', 'Add new single channel association rule'),
		// 			'url' => ['/rule-single-channel/create', 'id' => $model->id],
		// 		],
		// 		[
		// 			'label' => Yii::t('backend.view', 'Add new group load association rule'),
		// 			'url' => ['/rule-group-load/create', 'id' => $model->id],
		// 		],
		// 		[
		// 			'label' => Yii::t('backend.view', 'Add new fixed load association rule'),
		// 			'url' => ['/rule-fixed-load/create', 'id' => $model->id],
		// 		],
		// 	],
		// 	'visible' => (Yii::$app->user->can('TenantManager') || Yii::$app->user->can('TenantManagerOwner')),
		// ],
	],
]); ?>
