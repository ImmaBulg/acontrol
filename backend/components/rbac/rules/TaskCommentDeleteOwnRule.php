<?php

namespace backend\components\rbac\rules;

use Yii;

/**
 * TaskCommentDeleteOwnRule
 */
class TaskCommentDeleteOwnRule extends \yii\rbac\Rule
{
	public $name = 'isTaskCommentDeleteOwnRule';

	/**
	 * @inheritdoc
	 */
	public function execute($user, $item, $params)
	{
		if (isset($params['model'])) {
			return ($params['model']->created_by == Yii::$app->user->id);
		} else {
			return false;
		}
	}
}
