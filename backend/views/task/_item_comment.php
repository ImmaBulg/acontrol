<?php
use common\helpers\Html;
?>
<div class="row">
	<div class="col-lg-4">
		<div>
			<strong><?php echo $model->relationUserCreator->name; ?></strong>
		</div>
		<div class="text-muted">
			<?php echo Yii::$app->formatter->asHumanDateTime($model->created_at); ?>
		</div>
	</div>
	<div class="col-lg-8">
			<?php if(Yii::$app->user->can('TaskCommentManager') || Yii::$app->user->can('TaskCommentController.actionDeleteOwn', ['model' => $model])): ?>
				<?php echo Html::a(Yii::t('backend.view', 'Delete'), ['/task-comment/delete', 'id' => $model->id], [
					'class' => 'pull-right',
					'data' => [
						'toggle' => 'confirm',
						'confirm-post' => true,
						'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete this comment?'),
						'confirm-button' => Yii::t('backend.view', 'Delete'),
					],
				]); ?>
			<?php endif; ?>
		<?php echo $model->description; ?>
	</div>
</div>