<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\Meter;
use common\models\events\logs\EventLogMeter;

/**
 * FormMeters is the class for meters mass edit.
 */
class FormMeters extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	const METERS_FIELD_NAME = 'meters';

	public $site_id;

	public function rules()
	{
		return [
			['site_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Site', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					Site::STATUS_INACTIVE,
					Site::STATUS_ACTIVE,
				]]);
			}],
		];
	}

	public function attributeLabels()
	{
		return [
			'site_id' => Yii::t('backend.meter', 'Site'),
		];
	}

	public function save()
	{
		if (!$this->validate()) return false;
		$meters = Yii::$app->request->getQueryParam(self::METERS_FIELD_NAME);
		if ($meters == null) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$models = Meter::find()->where(['in', 'id', $meters])->all();

			if ($models != null) {
				foreach ($models as $model) {
					$model->site_id = $this->site_id;

					if ($model->getUpdatedAttributes() != null) {
						$event = new EventLogMeter();
						$event->model = $model;
						$model->on(EventLogMeter::EVENT_INIT, [$event, EventLogMeter::METHOD_UPDATE]);
						$model->init();
					}

					if (!$model->save()) {
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
