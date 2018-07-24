<?php

namespace console\models\forms;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\console\Exception;

use common\models\Task;
use common\models\Site;
use common\models\SiteContact;
use common\models\User;
use common\models\UserAlertNotification;
use common\components\rbac\Role;

class FormAlertsPush extends \yii\base\Model
{
	public function send()
	{
		$count = 0;
		$limit = 100;
		$db = Yii::$app->db;

		/**
		 * Users
		 */
		$users = (new Query())->select(['user.*'])
		->from(User::tableName(). ' user')
		->andWhere([
			'and',
			['user.status' => User::STATUS_ACTIVE],
			['user.role' => Role::ROLE_ADMIN],
		])
		->groupBy(['user.id'])
		->createCommand($db)
		->queryAll();

		if ($users != null) {
			$alerts = (new Query())->select([
				'alert.id',
				'alert.user_id',
				'alert.site_id',
				'alert.date',
				'alert.description',
				'alert.urgency',
				'site.name as site_name',
				'site_contact.name as site_contact_name',
				'site_contact.email as site_contact_email',
				'site_contact.phone as site_contact_phone',
			])
			->from(Task::tableName(). ' alert')
			->leftJoin(Site::tableName(). ' site', 'site.id = alert.site_id')
			->leftJoin(SiteContact::tableName(). ' site_contact', 'site_contact.id = alert.site_contact_id')
			->andWhere([
				'and',
				['in', 'alert.type', [Task::TYPE_ALERT, Task::TYPE_ISSUE_ALERT]],
				['alert.is_sent' => false],
				['alert.status' => Task::STATUS_ACTIVE],
			])
			->groupBy(['alert.id'])
			->orderBy(['alert.urgency' => SORT_DESC])
			->createCommand($db)
			->queryAll();

			/**
			 * Send mail
			 */
			if ($alerts != null) {
				foreach ($users as $user) {
					$mailer = Yii::$app->mailer
					->compose('daily-alert', [
						'user' => $user,
						'alerts' => $alerts,
					])
					->setFrom([Yii::$app->params['emailFrom'] => Yii::$app->name])
					->setTo([$user['email']])
					->setSubject(Yii::t('console.mail', 'Daily alerts summary'));
					$mailer->send();
				}
			}

			/**
			 * Mark alerts as sent
			 */
			$count = $db->createCommand()->update(Task::tableName(), ['is_sent' => true], ['in', 'id', ArrayHelper::map($alerts, 'id', 'id')])->execute();
		}

		return $count;
	}
}
