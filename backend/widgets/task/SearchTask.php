<?php
namespace backend\widgets\task;

use Yii;
use yii\data\ActiveDataProvider;

use common\components\data\Search;
use common\models\User;
use common\models\Task;
use common\models\Site;
use common\models\SiteContact;
use common\components\rbac\Role;

/**
 * SearchTask is the class for search tasks.
 */
class SearchTask extends \backend\models\searches\SearchTask
{
	/**
	 * @inheritdoc
	 */
	public function getDefaultQuery()
	{
		$modelClass = $this->modelClass;
		$t = $modelClass::tableName();
		$query = $modelClass::find()->where(['in', "$t.status", [
			$modelClass::STATUS_ACTIVE,
		]])->joinWith([
			'relationUser',
			'relationSite',
			'relationSiteContact',
			'relationMeter',
			'relationMeterChannel',
		], 'LEFT JOIN');

		switch (!Yii::$app->user->isGuest && Yii::$app->user->role) {
			case Role::ROLE_ADMIN:
				$query->andWhere([
					'or',
					"$t.user_id IS NULL",
					["$t.user_id" => Yii::$app->user->id],
					["$t.user_id" => Task::getAssigneeId()],
				]);
				break;
			
			default:
				$query->andWhere(["$t.user_id" => Yii::$app->user->id]);
				break;
		}

		return $query;
	}
}
