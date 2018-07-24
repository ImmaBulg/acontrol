<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\models\Site;
use common\models\Tenant;
use common\models\UserContact;
use common\widgets\DataColumn;
use backend\models\searches\models\User;

$this->title = Yii::t('backend.view', 'Clients');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Clients');
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('UserManager')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new client'),
							'url' => ['/user/create'],
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
	'id' => 'table-clients-list',
    'options' => [
        'class' => 'table table-striped table-primary',
    ],
	'columns' => [
		'id',
		[
			'attribute' => 'name',
			'format' => 'raw',
			'value' => function($model){
				return Html::a($model->name, ['/client/view', 'id' => $model->id]);
			},
		],
		'email:email',
		[
			'attribute' => 'phone',
			'value' => 'relationUserProfile.phone',
		],
		[
			'attribute' => 'fax',
			'value' => 'relationUserProfile.fax',
		],
		[
			'attribute' => 'status',
			'value' => 'aliasStatus',
			'filter' => User::getListStatuses(),
		],
		[
			'format' => 'raw',
			'value' => function($model){
				$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Sites ({count})', ['count' => $model->getRelationSites()->andWhere(['status' => Site::STATUS_ACTIVE])->count()]), ['/client/sites', 'id' => $model->id], ['class' => 'btn  btn-sm btn-primary']). '</div>';
				$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Tenants ({count})', ['count' => $model->getRelationTenants()->andWhere(['status' => Tenant::STATUS_ACTIVE])->count()]), ['/client/tenants', 'id' => $model->id], ['class' => 'btn  btn-success btn-sm']). '</div>';
				$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Contacts ({count})', ['count' => $model->getRelationUserContacts()->andWhere(['status' => UserContact::STATUS_ACTIVE])->count()]), ['/client-contact/list', 'id' => $model->id], ['class' => 'btn btn-lilac btn-sm']). '</div>';
				$btn[] = '<div class="btn-group">'.
							Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-danger btn-sm', 'data' => ['toggle' => 'dropdown']]).
							Dropdown::widget([
								'items' => [
									[
										'label' => Yii::t('backend.view', 'View'),
										'url' => ['/client/view', 'id' => $model->id],
									],
									[
										'label' => Yii::t('backend.view', 'Edit'),
										'url' => ['/user/edit', 'id' => $model->id],
										'visible' => (Yii::$app->user->can('UserManager')),
									],
									[
										'label' => Yii::t('backend.view', 'Delete'),
										'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/user/delete', 'id' => $model->id]),
										'visible' => (Yii::$app->user->can('UserManager')),
										'linkOptions' => [
											'data' => [
												'toggle' => 'confirm',
												'confirm-post' => true,
												'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this client?'),
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