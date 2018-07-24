<?php

namespace backend\components\rbac\rules;

use Yii;
use yii\helpers\ArrayHelper;
use common\components\rbac\Role;
use common\models\UserOwnerTenant;
use common\models\UserOwnerSite;

/**
 * SiteOwnRule
 */
class TenantOwnRule extends \yii\rbac\Rule
{
	public $name = 'isTenantOwnRule';

	/**
	 * @inheritdoc
	 */
	public function execute($user, $item, $params)
	{
		if (!empty($params['model'])) {
			/* @var $model \common\models\Tenant*/
			$model = $params['model'];
			$user_ids = array();
			switch(Yii::$app->user->identity->role) {
				case Role::ROLE_TENANT:
					$owner_tenant_models = UserOwnerTenant::find()->where(['tenant_id' => $model->id])->all();
					$user_ids = ArrayHelper::getColumn($owner_tenant_models, 'user_owner_id');
					break;
				case Role::ROLE_SITE:
					$owner_site_models = UserOwnerSite::find()->where(['site_id' => $model->site_id])->all();
					$user_ids = ArrayHelper::getColumn($owner_site_models, 'user_owner_id');
					break;
                case Role::ROLE_CLIENT:
					$users_model = Yii::$app->user->identity->relationUserOwners; // Get all sub users
					$user_ids = ArrayHelper::getColumn($users_model, 'user_id'); // Add sub users
					array_unshift($user_ids, Yii::$app->user->id); // Add owner user
					return true;
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