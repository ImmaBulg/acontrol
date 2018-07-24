<?php

namespace backend\models\forms;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\helpers\Html;
use common\models\RateType;
use common\models\events\logs\EventLogRateType;

/**
 * FormRateType is the class for rate type create/edit.
 */
class FormRateType extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $name_en;
	public $name_he;
	public $type = RateType::TYPE_FIXED;
	public $level;

	public function rules()
	{
		return [
			[['name_en', 'name_he'], 'filter', 'filter' => 'trim'],
			[['name_en', 'name_he'], 'string', 'max' => 255],
			[['name_en', 'name_he', 'type', 'level'], 'required'],
			['type', 'in', 'range' => array_keys(RateType::getListTypes()), 'skipOnEmpty' => false],
			['level', 'in', 'range' => array_keys(RateType::getListLevels()), 'skipOnEmpty' => false],

			// On scenario create
			[['name_en', 'name_he'], 'unique', 'targetClass' => '\common\models\RateType', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					RateType::STATUS_INACTIVE,
					RateType::STATUS_ACTIVE,
				]]);
			}, 'on' => self::SCENARIO_CREATE],

			// On scenario edit
			[['name_en', 'name_he'], 'unique', 'targetClass' => '\common\models\RateType', 'filter' => function($model){
				return $model->andWhere('id != :id', ['id' => $this->_id])
				->andWhere(['in', 'status', [
					RateType::STATUS_INACTIVE,
					RateType::STATUS_ACTIVE,
				]]);
			}, 'on' => self::SCENARIO_EDIT],
		];
	}

	public function attributeLabels()
	{
		return [
			'name_en' => Yii::t('backend.rate', 'Name (English)'),
			'name_he' => Yii::t('backend.rate', 'Name (Hebrew)'),
			'type' => Yii::t('backend.rate', 'Type'),
			'level' => Yii::t('backend.rate', 'Rate to use for Power factor range'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

				$this->name_en = $model->name_en;
				$this->name_he = $model->name_he;
				$this->type = $model->type;
				$this->level = $model->level;
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new RateType();
		$model->name_en = $this->name_en;
		$model->name_he = $this->name_he;
		$model->type = $this->type;
		$model->level = $this->level;

		$event = new EventLogRateType();
		$event->model = $model;
		$model->on(EventLogRateType::EVENT_AFTER_INSERT, [$event, EventLogRateType::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = RateType::findOne($this->_id);
		$model->name_en = $this->name_en;
		$model->name_he = $this->name_he;
		$model->type = $this->type;
		$model->level = $this->level;

		$event = new EventLogRateType();
		$event->model = $model;
		$model->on(EventLogRateType::EVENT_BEFORE_UPDATE, [$event, EventLogRateType::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
