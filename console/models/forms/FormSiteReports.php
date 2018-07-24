<?php

namespace console\models\forms;

use common\models\Log;
use Yii;
use yii\db\Query;
use yii\console\Exception;
use yii\helpers\ArrayHelper;
use common\models\Task;
use common\models\User;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\Report;
use common\components\rbac\Role;
use backend\models\forms\FormReport;

class FormSiteReports extends \yii\base\Model
{
    public function send() {
        $count = 0;
        $reports = [];
        $db = Yii::$app->db;
        $billing_day = date('j');
        $last_month_day = date('t');
        $query = Site::find()->where(['in', Site::tableName() . '.status', [
            Site::STATUS_ACTIVE,
        ]])->andWhere(['in', Site::tableName() . '.to_issue', [
            Site::TO_ISSUE_MANUAL,
            Site::TO_ISSUE_AUTOMATIC,
        ]])
                     ->joinWith([
                                    'relationSiteBillingSetting',
                                    'relationTenantsToIssued',
                                ], 'INNER JOIN')
                     ->andWhere(Site::tableName() . '.auto_issue_reports IS NOT NULL');
        if($billing_day >= $last_month_day) {
            $query->andWhere(['>=', SiteBillingSetting::tableName() . '.billing_day', $billing_day]);
        }
        else {
            $query->andWhere([SiteBillingSetting::tableName() . '.billing_day' => $billing_day]);
        }
        $sites = $query->groupBy([Site::tableName() . '.id'])->all();
        /**
         * Admins
         */
        // $users = $db->createCommand('
        // 	SELECT *
        // 	FROM {{user}} t
        // 	WHERE t.role = :role_admin AND t.status = :status
        // ')
        // ->bindValues([
        // 	':status' => User::STATUS_ACTIVE,
        // 	':role_admin' => Role::ROLE_ADMIN,
        // ])
        // ->queryAll();
        if($sites != null) {
            foreach($sites as $site) {
                $site_setting = $site->relationSiteBillingSetting;
                $auto_issue_reports = $site->getAutoIssueReports();
                $from_date = new \DateTime();
                $from_date->modify("first day of -1 month midnight");
                $from_date = $from_date->getTimestamp() + (($site_setting->billing_day - 1) * 86400);
                $to_date = new \DateTime();
                $to_date->modify("first day of this month midnight");
                $to_date = $to_date->getTimestamp() + (($site_setting->billing_day - 2) * 86400);
                /**
                 * Generates reports
                 */
                foreach($auto_issue_reports as $auto_issue_report) {
                    $report_exists = Report::find()
                                           ->andWhere([
                                                          'site_id' => $site->id,
                                                          'level' => Report::LEVEL_SITE,
                                                          'type' => $auto_issue_report,
                                                          'is_automatically_generated' => true,
                                                      ])
                                           ->andWhere(['from_date' => $from_date, 'to_date' => $to_date])
                                           ->exists();
                    if($report_exists == null) {
                        $form = new FormReport();
                        $form->level = Report::LEVEL_SITE;
                        $form->site_owner_id = $site->user_id;
                        $form->site_id = $site->id;
                        $form->from_date = Yii::$app->formatter->asDate($from_date);
                        $form->to_date = Yii::$app->formatter->asDate($to_date);
                        $form->type = $auto_issue_report;
                        $form->is_automatically_generated = true;
                        switch($auto_issue_report) {
                            case Report::TYPE_NIS:
                            case Report::TYPE_KWH:
                            case Report::TYPE_NIS_KWH:
                                $form->format_pdf = true;
                                $form->format_excel = true;
                                break;
                            default:
                                $form->format_pdf = true;
                                break;
                        }
                        $form->save();
                        // if (($report = $form->save()) != null) {
                        // 	$reports[] = $report;
                        // }
                    }
                }
                Log::add(Log::TYPE_UPDATE, 'Reports for Site "{site_id} have been issued by a cronjob',
                         ['site_id' => $site->id]);
            }
            /**
             * Send mail
             */
            // if ($reports != null && $users != null) {
            // 	foreach ($users as $user) {
            // 		$mailer = Yii::$app->mailer
            // 		->compose('issue-alert', [
            // 			'user' => $user,
            // 			'reports' => $reports,
            // 		])
            // 		->setFrom([Yii::$app->params['emailFrom'] => Yii::$app->name])
            // 		->setTo([$user['email']])
            // 		->setSubject(Yii::t('console.mail', 'Automatically issued reports'));
            // 		$mailer->send();
            // 	}
            // }
            /**
             * Mark sites as latest cronjob date
             */
            Site::updateAll(['cronjob_latest_issue_date_check' => time()],
                            ['in', 'id', ArrayHelper::map($sites, 'id', 'id')]);
        }
        return count($sites);
    }
}
