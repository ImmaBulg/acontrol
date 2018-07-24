<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\components\rbac\Role;
use backend\models\forms\FormUsers;
use backend\models\searches\models\User;

$this->title = Yii::t('backend.view', 'Users');
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Users');
?>
<div class="page-header">
	<?php if (Yii::$app->user->can('UserManager')): ?>
		<div class="btn-toolbar pull-right">
			<div class="btn-group">
				<?php echo Html::a(Yii::t('backend.view', 'Add'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-success', 'data' => ['toggle' => 'dropdown']]).
				Dropdown::widget([
					'items' => [
						[
							'label' => Yii::t('backend.view', 'Add new user'),
							'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/user/create']),
						],
					],
				]); ?>
			</div>
		</div>
	<?php endif; ?>
	<h1><?php echo $this->title; ?></h1>
</div>
<?php $form_active = ActiveForm::begin([
	'id' => 'form-users-edit',
	'enableClientValidation' => false,
	'method' => 'GET',
	'action' => ['/user/list'],
]); ?>
<fieldset>
	<?php if (Yii::$app->user->can('UserManager')): ?>
		<?php echo $form_active->errorSummary($form_users); ?>
		<div class="row">
			<div class="col-lg-3">
				<?php echo $form_active->field($form_users, 'status')->widget(Select2::classname(), [
					'data' => User::getListStatuses(),
					'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
				])->error(false); ?>
			</div>
			<div class="col-lg-2 control-label-offset">
				<?php echo $form_active->field($form_users, 'generate_nickname')->checkbox()->error(false); ?>
			</div>
			<div class="col-lg-2 control-label-offset">
				<?php echo $form_active->field($form_users, 'generate_password')->checkbox()->error(false); ?>
			</div>
			<div class="col-lg-3">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update and send email'), ['class' => 'btn btn-primary control-label-offset']); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php echo GridView::widget([
		'dataProvider' => $data_provider,
		'filterModel' => $filter_model,
		'id' => 'table-clients-list',
        'options' => [
            'class' => 'table table-striped table-primary',
        ],
		'columns' => [
			[
				'class' => 'common\widgets\CheckboxColumn',
				'name' => FormUsers::USERS_FIELD_NAME,
				'visible' => Yii::$app->user->can('UserManager'),
			],
			'id',
			[
				'attribute' => 'name',
				'format' => 'raw',
				'value' => function($model){
					return Html::a($model->name, ['/user/view', 'id' => $model->id]);
				},
			],
			[
				'attribute' => 'nickname',
				'format' => 'raw',
				'value' => function($model){
					return Html::a($model->nickname, ['/user/view', 'id' => $model->id]);
				},
			],
			[
				'attribute' => 'email',
			],
			[
				'attribute' => 'role',
				'value' => 'aliasRole',
				'filter' => Role::getAliasAllowedRoles(),
			],
			[
				'attribute' => 'job',
				'value' => 'relationUserProfile.job',
			],
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
					$btn[] = '<div class="btn-group">'.
								Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-danger btn-sm', 'data' => ['toggle' => 'dropdown']]).
								Dropdown::widget([
									'items' => [
										[
											'label' => Yii::t('backend.view', 'View'),
											'url' => ['/user/view', 'id' => $model->id],
										],
										[
											'label' => Yii::t('backend.view', 'Edit'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/user/edit', 'id' => $model->id]),
											'visible' => (Yii::$app->user->can('UserManager')),
										],
										[
											'label' => Yii::t('backend.view', 'Delete'),
											'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/user/delete', 'id' => $model->id]),
											'visible' => (Yii::$app->user->can('UserManager') && Yii::$app->user->id != $model->id),
											'linkOptions' => [
												'data' => [
													'toggle' => 'confirm',
													'confirm-post' => true,
													'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this user?'),
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