<?php

namespace backend\components\rbac\rules;

use Yii;
use common\components\rbac\Role;
use yii\helpers\ArrayHelper;

/**
 * ClientsListOwnRule
 */
class ClientOwnRule extends \yii\rbac\Rule
{
	public $name = 'isClientListOwnRule';

	/**
	 * @inheritdoc
	 */
	public function execute($user, $item, $params)
	{
        if (!empty($params['model'])) {
			/* @var $model \common\models\User*/
			$model = $params['model'];
			$user_ids = array();
			switch(Yii::$app->user->identity->role) {
				case Role::ROLE_CLIENT;
                    $users_model = Yii::$app->user->identity->relationUserOwners; // Get all sub users
					$user_ids = ArrayHelper::getColumn($users_model, 'user_id'); // Add sub users
					array_unshift($user_ids, Yii::$app->user->id); // Add owner user
                    $user = $model->id;
					break;
				default:
					return false;
			}
			return in_array($user, $user_ids);
		} else {
			return false;
		}
	}
}
