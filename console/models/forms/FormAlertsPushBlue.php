<?php

namespace console\models\forms;

use Yii;
use yii\db\Query;
use yii\console\Exception;
use yii\helpers\ArrayHelper;

use common\models\Task;
use common\models\User;
use common\models\Site;
use common\models\Tenant;
use common\models\Meter;
use common\models\MeterSubchannel;
use common\models\ElectricityMeterRawData;
use common\components\rbac\Role;

class FormAlertsPushBlue extends \yii\base\Model
{
	public function send()
	{
		$count = 0;
		$limit = 1;
		$db = Yii::$app->db;
		$max_date = time() - (72 * 3600);
		$push_alerts = [];

		/**
		 * Sites
		 */
		$sites = $db->createCommand('
			SELECT t.id, t.name
			FROM {{site}} t 
			INNER JOIN {{meter}} meter ON meter.site_id = t.id 
			WHERE t.status = :status AND (t.cronjob_latest_meter_date_check IS NULL OR t.cronjob_latest_meter_date_check <= :cronjob_latest_meter_date_check) 
			GROUP BY t.id
			LIMIT :limit
		')
		->bindValues([
			':cronjob_latest_meter_date_check' => time() - (72 * 3600),
			':status' => Site::STATUS_ACTIVE,
			':limit' => $limit,
		])
		->queryAll();

		/**
		 * Special admin
		 */
		$admin_id = Task::getAssigneeId();

		/**
		 * Admins
		 */
		$admin_emails = $db->createCommand('
			SELECT t.email
			FROM {{user}} t 
			WHERE t.role = :role_admin AND t.status = :status
		')
		->bindValues([
			':status' => User::STATUS_ACTIVE,
			':role_admin' => Role::ROLE_ADMIN,
		])
		->queryColumn();

		if ($sites != null && $admin_id != null) {
			foreach ($sites as $site) {
				/**
				 * Tenants
				 */
				$tenants = $db->createCommand('
					SELECT COUNT(*)
					FROM {{tenant}} tenant
					WHERE tenant.site_id = :site_id AND tenant.status = :status
				')
				->bindValues([
					':site_id' => $site['id'],
					':status' => Tenant::STATUS_ACTIVE,
				])
				->queryScalar();

				if (!$tenants) continue;

				/**
				 * Meters
				 */
				$meters = $db->createCommand('
					SELECT t.id, t.name 
					FROM {{meter}} t 
					WHERE t.site_id = :site_id AND t.status = :status
				')
				->bindValues([
					':site_id' => $site['id'],
					':status' => Meter::STATUS_ACTIVE,
				])
				->queryAll();

				foreach ($meters as $meter) {
					/**
					 * Meters channels
					 */
					$channels = $db->createCommand('
						SELECT t.id, t.channel as name 
						FROM {{meter_subchannel}} t 
						WHERE t.meter_id = :meter_id AND t.status = :status
						ORDER BY t.channel ASC
					')
					->bindValues([
						':meter_id' => $meter['id'],
						':status' => MeterSubchannel::STATUS_ACTIVE,
					])
					->queryAll();

					foreach ($channels as $channel) {
						/**
						 * Meter raw data max date
						 */
						$meter_raw_data_date = $db->createCommand('
							SELECT MAX(t.date)
							FROM {{meter_raw_data}} t 
							WHERE t.meter_id = :meter_id AND t.channel_id = :channel_id AND t.status = :status
						')
						->bindValues([
							':meter_id' => $meter['name'],
							':channel_id' => $channel['name'],
							':status' => ElectricityMeterRawData::STATUS_ACTIVE,
						])
						->queryScalar();

						if ($meter_raw_data_date == null || $meter_raw_data_date < $max_date) {
							if (empty($push_alerts[$site['id']])) {
								$push_alerts[$site['id']] = [
									'site_id' => $site['id'],
									'site_name' => $site['name'],
								];
							}

							$push_alerts[$site['id']]['description'][] = Yii::t('console.mail', 'Missing channel {channel} data', [
								'channel' => $channel['name'] . '(' . $meter['name'] . ')',
							]);
						}
					}
				}
			}

			foreach ($push_alerts as $push_alert) {
				/**
				 * Create helpdesk
				 */
				$alert = new Task();
				$alert->user_id = $admin_id;
				$alert->site_id = $push_alert['site_id'];
				$alert->date = time();
				$alert->description = implode("\r\n", [
					Yii::t('console.mail', 'No new data were obtained from {name} during the latest 72 hours:', [
						'name' => $push_alert['site_id']. ' - ' .$push_alert['site_name'],
					]),
					implode(', ', $push_alert['description']),
				]);
				$alert->urgency = Task::URGENCY_NORMAL;
				$alert->color = Task::COLOR_BLUE;

				if (!$alert->save()) {
					throw new Exception(implode(' ', $alert->getFirstErrors()));
				}

				/**
				 * Send mail
				 */
				// $mailer = Yii::$app->mailer
				// ->compose('blue-alert', [
				// 	'alert' => $alert->toArray(),
				// 	'site_id' => $site_id,
				// 	'site_name' => $site_name,
				// ])
				// ->setFrom([Yii::$app->params['emailFrom'] => Yii::$app->name])
				// ->setTo($admin_emails)
				// ->setSubject(Yii::t('console.mail', 'New alert: no new data for {site_name}', [
				// 	'site_name' => $site_name,
				// ]));
				// $mailer->send();

				$count++;
			}

			/**
			 * Mark sites as latest cronjob date
			 */
			Site::updateAll(['cronjob_latest_meter_date_check' => time()], ['in', 'id', ArrayHelper::map($sites, 'id', 'id')]);
		}

		return $count;
	}
}
