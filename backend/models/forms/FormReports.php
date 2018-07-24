<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\helpers\FileHelper;
use common\models\Report;
use common\models\events\logs\EventLogReport;

/**
 * FormReports is the class for reports mass edit.
 */
class FormReports extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';
	const REPORTS_FIELD_NAME = 'reports';

	public $is_delete;

	public function rules()
	{
		return [
			['is_delete', 'boolean'],
		];
	}

	public function save()
	{
		if (!$this->validate()) return false;
		$reports = Yii::$app->request->getQueryParam(self::REPORTS_FIELD_NAME);
		if ($reports == null) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$models = Report::find()->where(['in', 'id', $reports])->all();

			if ($models != null) {
				foreach ($models as $model) {
					$model_files = $model->relationReportFiles;

					if ($model_files != null) {
						foreach ($model_files as $model_file) {
							FileHelper::delete($model_file->file);
						}
					}

					$event = new EventLogReport();
					$event->model = $model;
					$model->on(EventLogReport::EVENT_BEFORE_DELETE, [$event, EventLogReport::METHOD_DELETE]);

					if (!$model->delete()) {
						throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
					}
				}
			}

			$transaction->commit();
			return true;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}
}
