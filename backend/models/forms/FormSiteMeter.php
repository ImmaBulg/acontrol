<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\helpers\Html;
use common\models\Site;
use common\models\SiteMeterTree;
use common\models\Meter;
use common\models\MeterChannel;

/**
 * FormSiteMeter is the class for site meter create/edit.
 */
class FormSiteMeter extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;
	private $_site_id;

	public $meter_id;

	public function rules()
	{
		return [
			[['meter_id'], 'required'],
			[['meter_id'], 'integer'],
			['meter_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Meter', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					Meter::STATUS_INACTIVE,
					Meter::STATUS_ACTIVE,
				]]);
			}],
		];
	}

	public function attributeLabels()
	{
		return [
			'meter_id' => Yii::t('backend.site', 'Meter ID'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_CREATE:
				$this->_site_id = $model->id;
				break;

			case self::SCENARIO_EDIT:
				$this->_id = $model->id;
				$this->_site_id = $model->site_id;

				$this->meter_id = $model->id;
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = Meter::findOne($this->meter_id);
		$model->site_id = $this->_site_id;

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model_old = Meter::findOne($this->_id);
		$model_old->site_id = NULL;

		if (!$model_old->save()) {
			throw new BadRequestHttpException(implode(' ', $model_old->getFirstErrors()));
		}

		$model = Meter::findOne($this->meter_id);
		$model->site_id = $this->_site_id;

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
