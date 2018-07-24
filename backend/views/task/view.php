<?php

use yii\widgets\DetailView;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\ListView;

$this->title = Yii::t('backend.view', 'Task - {id}', ['id' => $model->id]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Alerts/Helpdesk'),
	'url' => ['/task/list'],
];
$this->params['breadcrumbs'][] = $model->id;
?>
<?php echo $this->render('_menu', ['model' => $model]); ?>
<?php echo DetailView::widget([
	'model' => $model,
	'attributes' => [
		[ 
			'attribute' => 'user_name',
			'value' => ($model->relationUser != null) ? $model->relationUser->name : null,
		],
		[
			'attribute' => 'description',
			'format' => 'raw',
			'value' => implode("<br />", explode("\n", $model->description)),
		],
		[ 
			'attribute' => 'date',
			'value' => ($model->date != null) ? Yii::$app->formatter->asDateTime($model->date) : null,
		],
		[ 
			'attribute' => 'site_name',
			'value' => ($model->relationSite != null) ? $model->relationSite->name : null,
		],
		[ 
			'attribute' => 'site_contact_name',
			'value' => ($model->relationSiteContact != null) ? $model->relationSiteContact->name : null,
		],
		[ 
			'attribute' => 'meter_name',
			'value' => ($model->relationMeter != null) ? $model->relationMeter->name : null,
		],
		[ 
			'attribute' => 'channel_name',
			'value' => ($model->relationMeterChannel != null) ? $model->relationMeterChannel->channel : null,
		],
		[ 
			'attribute' => 'type',
			'value' => $model->getAliasType(),
		],
		[ 
			'attribute' => 'urgency',
			'value' => $model->getAliasUrgency(),
		],
		'ip_address',
		[ 
			'attribute' => 'status',
			'value' => $model->getAliasStatus(),
		],
	],
]); ?>
<?php if(Yii::$app->user->can('TaskCommentController.actionCreate')): ?>
	<div class="well">
		<?php $form_active = ActiveForm::begin([
			'id' => 'form-task-comment-create',
			'enableOneProcessSubmit' => true,
		]); ?>
			<fieldset>
				<div class="row">
					<div class="col-lg-6">
						<?php echo $form_active->field($form, 'description')->textArea()->label(false); ?>
					</div>
				</div>
				<div class="form-group">
					<?php echo Html::submitInput(Yii::t('backend.view', 'Add comment'), ['class' => 'btn btn-success']); ?>
				</div>
			</fieldset>
		<?php ActiveForm::end(); ?>
	</div>
<?php endif; ?>
<?php echo ListView::widget([
	'dataProvider' => $data_provider,
	'id' => 'task-comment-list',
	'itemView' => '_item_comment',
	'layout' => "{items}{pager}",
	'options' => ['class' => 'list-group'],
	'itemOptions' => ['class' => 'list-group-item'],
	'emptyText' => Yii::t('backend.view', 'No comments found.'),
]); ?>
