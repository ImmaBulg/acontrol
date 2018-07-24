<?php
namespace common\components\rbac;

use Yii;
use yii\rbac\Assignment;

/**
 * PhpManager represents an authorization manager that stores authorization
 * information in terms of a PHP script file.
 *
 * The authorization data will be saved to and loaded from three files
 * specified by [[itemFile]], [[assignmentFile]] and [[ruleFile]].
 *
 * PhpManager is mainly suitable for authorization data that is not too big
 * (for example, the authorization data for a personal blog system).
 * Use [[DbManager]] for more complex authorization data.
 *
 * Note that PhpManager is not compatible with facebooks [HHVM](http://hhvm.com/) because
 * it relies on writing php files and including them afterwards which is not supported by HHVM.
 */
class PhpManager extends \yii\rbac\PhpManager
{
	public function getAssignments($userId)
	{
		if (!Yii::$app->user->isGuest) {
			$assignment = new Assignment;
			$assignment->userId = $userId;
			$assignment->roleName = Yii::$app->user->identity->role;
			$assignment->createdAt = Yii::$app->user->identity->created_at;
			return [$assignment->roleName => $assignment];
		}
		else return [];
	}
}