<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\TaskComment;
use common\widgets\Alert;
use common\models\events\logs\EventLogTaskComment;

/**
 * TaskCommentController
 */
class TaskCommentController extends \backend\components\Controller
{
	public $enableCsrfValidation = false;
	
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'accessDelete' => [
				'class' => AccessControl::className(),
				'only' => ['delete'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['TaskCommentController.actionDelete'],
					],
				],
				'rules' => [
					[
						'allow' => true,
						'matchCallback' => function ($rule, $action) {
							$id = Yii::$app->getRequest()->getQueryParam('id');
							return (Yii::$app->user->can('TaskCommentController.actionDelete') || Yii::$app->user->can('TaskCommentController.actionDeleteOwn', ['model' => $this->loadTaskComment($id)]));
						}
					],
				],
			],
		]);
	}

	public function actionDelete($id)
	{
		$model = $this->loadTaskComment($id);

		$event = new EventLogTaskComment();
		$event->model = $model;
		$model->on(EventLogTaskComment::EVENT_BEFORE_DELETE, [$event, EventLogTaskComment::METHOD_DELETE]);

		if ($model->delete()) {
			Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Comment have been deleted.'));
			return $this->goBackReferrer();
		} else {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
	}

	private function loadTaskComment($id)
	{
		$model = TaskComment::find()->andWhere([
			TaskComment::tableName(). '.id' => $id,
		])->andWhere(['in', TaskComment::tableName(). '.status', [
			TaskComment::STATUS_INACTIVE,
			TaskComment::STATUS_ACTIVE,
		]])->one();

		if ($model == null) {
			throw new NotFoundHttpException(Yii::t('backend.controller', 'Comment not found'));
		}

		return $model;	
	}
}
