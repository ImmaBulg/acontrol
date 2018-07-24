<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\ActiveForm;
use common\models\Site;
use common\models\Tenant;
use common\models\TenantReport;
use common\models\Report;
use common\models\Task;
use common\components\i18n\Formatter;
?>
<div class="panel panel-default">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-issue-reports',
		'action' => ['/site/issue-reports'],
	]); ?>
		<div class="panel-heading">
			<?php echo Html::submitInput(Yii::t('backend.view', 'Run automatic report issuing'), ['class' => 'btn btn-primary btn-sm pull-right']); ?>
			<?php echo Yii::t('backend.view', 'Sites to issue'); ?>
		</div>
		<div class="panel-body">
			<?php echo GridView::widget([
				'dataProvider' => $data_provider,
				'id' => 'table-site-shedule-list',
				'layout' => "{items}{pager}",
				'rowOptions' => function($model) {
					$site_setting = $model->relationSiteBillingSetting;
					$auto_issue_reports = $model->getAutoIssueReports();

					$from_date = new \DateTime();
					$from_date->modify("first day of -1 month midnight");
					$from_date = $from_date->getTimestamp() + (($site_setting->billing_day - 1) * 86400);

					$to_date = new \DateTime();
					$to_date->modify("first day of this month midnight");
					$to_date = $to_date->getTimestamp() + (($site_setting->billing_day - 2) * 86400);

					$count = Report::find()
					->andWhere([
						'and',
						['site_id' => $model->id],
						['level' => Report::LEVEL_SITE],
						['in', 'type', $auto_issue_reports],
						// ['is_automatically_generated' => true],
					])
					->andWhere(['from_date' => $from_date, 'to_date' => $to_date])
					->groupBy(['type'])
					->count();

					if ($count >= count($auto_issue_reports)) {
						return ['class' => 'color color-lightgreen'];
					}
				},
				'columns' => [
					[
						'class' => 'common\widgets\CheckboxColumn',
						'name' => 'sites',
						'visible' => Yii::$app->user->can('SiteManager'),
					],
					[
						'attribute' => 'site_name',
						'format' => 'raw',
						'value' => function($model){
							return Html::a($model->name, ['/site/view', 'id' => $model->id]);
						},
					],
					[
						'attribute' => 'billing_day',
						'value' => function($model) {
							$site_setting = $model->relationSiteBillingSetting;
							return $site_setting->billing_day;
						},
					],
					[
						'attribute' => 'issue_dates',
						'format' => 'raw',
						'value' => function($model){
							$site_setting = $model->relationSiteBillingSetting;
							$from_date = new \DateTime();
							$from_date->modify("first day of -1 month midnight");
							$from_date = $from_date->getTimestamp() + (($site_setting->billing_day - 1) * 86400);

							$to_date = new \DateTime();
							$to_date->modify("first day of this month midnight");
							$to_date = $to_date->getTimestamp() + (($site_setting->billing_day - 2) * 86400);

							return Yii::$app->formatter->asDate($from_date). ' - ' .Yii::$app->formatter->asDate($to_date);
						},
					],
					[
						'attribute' => 'last_issue_date',
						'format' => 'raw',
						'value' => function($model){
							$site_setting = $model->relationSiteBillingSetting;
							$auto_issue_reports = $model->getAutoIssueReports();

							$from_date = new \DateTime();
							$from_date->modify("first day of -1 month midnight");
							$from_date = $from_date->getTimestamp() + (($site_setting->billing_day - 1) * 86400);

							$to_date = new \DateTime();
							$to_date->modify("first day of this month midnight");
							$to_date = $to_date->getTimestamp() + (($site_setting->billing_day - 2) * 86400);

							$rows = [];
							$reports = Report::find()
							->andWhere([
								'and',
								['site_id' => $model->id],
								['level' => Report::LEVEL_SITE],
								['in', 'type', $auto_issue_reports],
								// ['is_automatically_generated' => true],
							])
							->andWhere(['from_date' => $from_date, 'to_date' => $to_date])
							->indexBy('type')
							->groupBy(['type'])
							->all();

							foreach ($auto_issue_reports as $auto_issue_report) {
								if (!empty($reports[$auto_issue_report])) {
									$report = $reports[$auto_issue_report];
									$issuer = ($report->created_by != null) ? $report->relationUserCreator->name : Yii::t('backend.view', 'System');
									$rows[] = Html::tag('div', ArrayHelper::getValue(Report::getListTypes(), $auto_issue_report) . ": " . Yii::$app->formatter->asDate($report->from_date). ' - ' .Yii::$app->formatter->asDate($report->to_date) . " ($issuer)", ['class' => 'text-success']);
								} else {
									$rows[] = Html::tag('div', ArrayHelper::getValue(Report::getListTypes(), $auto_issue_report) . ": " . Yii::t('backend.view', 'Not generated'), ['class' => 'text-danger']);
								}
							}

							return Html::tag('div', implode("\r\n", $rows), ['class' => 'small']);
						},
					],
					[
						'attribute' => 'to_issue',
						'value' => 'aliasToIssue',
					],
					[
						'attribute' => 'issue_tenants',
						'format' => 'raw',
						'value' => function($model){
							return $model->getRelationTenants()->andWhere([
								Tenant::tableName(). '.status' => Tenant::STATUS_ACTIVE,
							])
							->andWhere(['in', Tenant::tableName(). '.to_issue', [
								Site::TO_ISSUE_MANUAL,
								Site::TO_ISSUE_AUTOMATIC,
							]])->count();
						},
					],
					[
						'attribute' => 'cronjob_latest_issue_date_check',
						'format' => 'dateTime',
					],
					[
						'format' => 'raw',
						'value' => function ($model){
							$sql_date_format = Formatter::SQL_DATE_FORMAT;
							$site_setting = $model->relationSiteBillingSetting;
							$auto_issue_reports = $model->getAutoIssueReports();

							$from_date = new \DateTime();
							$from_date->modify("first day of -1 month midnight");
							$from_date = $from_date->getTimestamp() + (($site_setting->billing_day - 1) * 86400);

							$to_date = new \DateTime();
							$to_date->modify("first day of this month midnight");
							$to_date = $to_date->getTimestamp() + (($site_setting->billing_day - 2) * 86400);

							if ($model->getRelationTasks()->andWhere([
								'and',
								['type' => Task::TYPE_ISSUE_ALERT],
								["DATE_FORMAT(FROM_UNIXTIME(date), '$sql_date_format')" => Yii::$app->formatter->asDate($model->cronjob_latest_issue_date_check, Formatter::PHP_DATE_FORMAT)],
							])->count()) {
								$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Issued alerts'), [
									'/task/list',
									'Task[type]' => Task::TYPE_ISSUE_ALERT,
									'Task[site_name]' => $model->name,
									'from_date' => Yii::$app->formatter->asDate($model->cronjob_latest_issue_date_check, Formatter::PHP_DATE_FORMAT),
									'to_date' => Yii::$app->formatter->asDate($model->cronjob_latest_issue_date_check, Formatter::PHP_DATE_FORMAT),
								], ['class' => 'btn btn-warning btn-sm']). '</div>';
							}
							
							$btn[] = '<div class="btn-group">' .Html::a(Yii::t('backend.view', 'Issued reports'), [
								'/site/reports',
								'id' => $model->id,
								'Report[from_date]' => Yii::$app->formatter->asDate($from_date),
								'Report[to_date]' => Yii::$app->formatter->asDate($to_date),
								// 'Report[is_automatically_generated]' => true,
							], ['class' => 'btn btn-info btn-sm']). '</div>';
							return '<div class="btn-toolbar pull-right">' .implode('', $btn). '</div>';
						}
					],
				],
			]); ?>
		</div>
	<?php ActiveForm::end(); ?>
	<?php $this->registerJs('jQuery("#form-issue-reports").on("beforeSubmit", function(event){
		jQuery("body").append("<div id=\"report-overlay\"></div>");
		jQuery("body").append("<div id=\"report-spinner-holder\">' .Yii::t('backend.view', 'Issue reports'). '<div id=\"report-spinner\"><div class=\"rect rect1\"></div><div class=\"rect rect2\"></div><div class=\"rect rect3\"></div><div class=\"rect rect4\"></div><div class=\"rect rect5\"></div></div></div>");
	});'); ?>
</div>

