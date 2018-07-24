<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\helpers\Html;
use common\models\Vat;
use common\components\i18n\Formatter;
use common\models\events\logs\EventLogVat;

/**
 * FormVat is the class for vat create/edit.
 */
class FormVat extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $vat;
	public $start_date;
	public $end_date;

	public function rules()
	{
		return [
			[['start_date', 'end_date'], 'filter', 'filter' => 'trim'],
			[['vat', 'start_date'], 'required'],
			[['vat'], 'number'],
			['start_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['end_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['end_date', '\common\components\validators\DateTimeCompareValidator', 'compareAttribute' => 'start_date', 'format' => Formatter::PHP_DATE_FORMAT, 'operator' => '>='],
			['start_date', 'validateDatePeriod'],
		];
	}

	public function validateDatePeriod($attribute, $params)
	{
		$start_date = Yii::$app->formatter->modifyTimestamp($this->start_date, 'midnight');

		/**
		 * Check if date greater than last end date
		 */
		$query = Vat::find()->andWhere('end_date IS NOT NULL')->andWhere(['in', 'status', [
			Vat::STATUS_INACTIVE,
			Vat::STATUS_ACTIVE,
		]]);

		if ($this->_id != null) {
			$query->andWhere('id != :id', ['id' => $this->_id]);
		}

		$model_last = $query->orderBy(['end_date' => SORT_DESC])->one();

		if ($model_last != null && ($start_date - $model_last->end_date) > 86400) {
			return $this->addError($attribute, Yii::t('backend.vat', '{attribute} must be no greater than {date}', [
				'attribute' => $this->getAttributeLabel('start_date'),
				'date' => Yii::$app->formatter->asDate($model_last->end_date + 86400),
			]));		
		}

		if ($this->end_date != null) {
			/**
			 * Check if date in range
			 */
			$end_date = Yii::$app->formatter->modifyTimestamp($this->end_date, 'tomorrow') - 1;

			$query = Vat::find()->andWhere('start_date <= :end_date AND end_date >= :start_date', [
				'start_date' => $start_date,
				'end_date' => $end_date,
			])->andWhere(['in', 'status', [
				Vat::STATUS_INACTIVE,
				Vat::STATUS_ACTIVE,
			]]);

			if ($this->_id != null) {
				$query->andWhere('id != :id', ['id' => $this->_id]);
			}

			$model = $query->one();
			
			if ($model != null) {
				return $this->addError($attribute, Yii::t('backend.vat', 'This date period has already been taken by {link}', [
					'link' => Html::a(Yii::t('backend.vat', 'VAT {name}', ['name' => $model->id]), ['/vat/edit', 'id' => $model->id], ['target' => '_blank']),
				]));
			}

			/**
			 * Check if date less than last start date
			 */
			$query = Vat::find()->andWhere(['in', 'status', [
				Vat::STATUS_INACTIVE,
				Vat::STATUS_ACTIVE,
			]]);

			if ($this->_id != null) {
				$query->andWhere('id != :id', ['id' => $this->_id]);
			}

			$model_first = $query->orderBy(['start_date' => SORT_ASC])->one();
			
			if ($model_first != null && ($model_first->start_date - $end_date) > 86400) {
				return $this->addError('end_date', Yii::t('backend.vat', '{attribute} must be no less than {date}', [
					'attribute' => $this->getAttributeLabel('start_date'),
					'date' => Yii::$app->formatter->asDate($model_first->start_date - 86400),
				]));
			}

			/**
			 * Check if date greater than last start date with empty end date
			 */
			$query = Vat::find()->andWhere('end_date IS NULL')->andWhere(['in', 'status', [
				Vat::STATUS_INACTIVE,
				Vat::STATUS_ACTIVE,
			]]);

			if ($this->_id != null) {
				$query->andWhere('id != :id', ['id' => $this->_id]);
			}

			$model_empty = $query->orderBy(['start_date' => SORT_DESC])->one();

			if ($model_empty != null && ($end_date - $model_empty->start_date) > 0) {
				return $this->addError('end_date', Yii::t('backend.vat', '{attribute} must be no greater than {date}', [
					'attribute' => $this->getAttributeLabel('end_date'),
					'date' => ($model_first != null) ? Yii::$app->formatter->asDate($model_first->start_date - 86400) : Yii::$app->formatter->asDate($model_empty->start_date - 86400),
				]));
			}
		} else {
			/**
			 * Check if record with emtpy end date is exists
			 */
			$query = Vat::find()->andWhere('end_date IS NULL')->andWhere(['in', 'status', [
				Vat::STATUS_INACTIVE,
				Vat::STATUS_ACTIVE,
			]]);

			if ($this->_id != null) {
				$query->andWhere('id != :id', ['id' => $this->_id]);
			}

			$model_empty = $query->one();

			if ($model_empty != null) {
				return $this->addError('end_date', Yii::t('backend.vat', 'Set {attribute} first on last record {link}', [
					'attribute' => $this->getAttributeLabel('end_date'),
					'link' => Html::a(Yii::t('backend.vat', 'VAT {name}', ['name' => $model_empty->id]), ['/vat/edit', 'id' => $model_empty->id], ['target' => '_blank']),
				]));
			}

			/**
			 * Check if date less than last end date
			 */
			$query = Vat::find()->andWhere('end_date IS NOT NULL')->andWhere(['in', 'status', [
				Vat::STATUS_INACTIVE,
				Vat::STATUS_ACTIVE,
			]]);

			if ($this->_id != null) {
				$query->andWhere('id != :id', ['id' => $this->_id]);
			}

			$model_first = $query->orderBy(['end_date' => SORT_DESC])->one();
			
			if ($model_first != null && ($model_first->end_date - $start_date) > 86400) {
				return $this->addError($attribute, Yii::t('backend.vat', '{attribute} must be no less than {date}', [
					'attribute' => $this->getAttributeLabel('start_date'),
					'date' => Yii::$app->formatter->asDate($model_first->end_date + 86400),
				]));		
			}
		}
	}

	public function attributeLabels()
	{
		return [
			'vat' => Yii::t('backend.vat', 'Vat'),
			'start_date' => Yii::t('backend.vat', 'Start date'),
			'end_date' => Yii::t('backend.vat', 'End date'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

				$this->vat = $model->vat;
				$this->start_date = $model->start_date;
				$this->end_date = $model->end_date;
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new Vat();
		$model->vat = $this->vat;
		$model->start_date = $this->start_date;
		$model->end_date = $this->end_date;

		$event = new EventLogVat();
		$event->model = $model;
		$model->on(EventLogVat::EVENT_AFTER_INSERT, [$event, EventLogVat::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = Vat::findOne($this->_id);
		$model->vat = $this->vat;
		$model->start_date = $this->start_date;
		$model->end_date = $this->end_date;

		$event = new EventLogVat();
		$event->model = $model;
		$model->on(EventLogVat::EVENT_BEFORE_UPDATE, [$event, EventLogVat::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
