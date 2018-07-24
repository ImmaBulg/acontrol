<?php

namespace backend\models\forms;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\helpers\Html;
use common\models\Rate;
use common\models\RateType;
use common\models\SiteBillingSetting;
use common\components\i18n\Formatter;
use common\models\events\logs\EventLogRate;

/**
 * FormRate is the class for rate create/edit.
 */
class FormRate extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $rate_type_id;
	public $season;
	public $fixed_payment;
	public $rate;
	public $shefel;
	public $geva;
	public $pisga;
	public $start_date;
	public $end_date;
	public $identifier;
	public $shefel_identifier;
	public $geva_identifier;
	public $pisga_identifier;

	public function rules()
	{
		return [
			[['start_date', 'end_date'], 'filter', 'filter' => 'trim'],
			[['rate_type_id', 'fixed_payment', 'start_date', 'end_date'], 'required'],
			[['identifier', 'shefel_identifier', 'geva_identifier', 'pisga_identifier'], 'match', 'pattern' => Rate::IDENTIFIER_VALIDATION_PATTERN],
			[['identifier', 'shefel_identifier', 'geva_identifier', 'pisga_identifier'], 'string', 'max' => 255],
			[['fixed_payment', 'rate', 'shefel', 'geva', 'pisga'], 'number', 'min' => 0],
			['rate_type_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\RateType', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					RateType::STATUS_INACTIVE,
					RateType::STATUS_ACTIVE,
				]]);
			}],
			['season', 'in', 'range' => array_keys(Rate::getListSeasons()), 'skipOnEmpty' => true],
			['start_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['end_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['end_date', '\common\components\validators\DateTimeCompareValidator', 'compareAttribute' => 'start_date', 'format' => Formatter::PHP_DATE_FORMAT, 'operator' => '>='],
			['start_date', 'validateDatePeriod'],
			['rate_type_id', 'validateRateType'],
		];
	}

	public function validateDatePeriod($attribute, $params)
	{
		$start_date = Yii::$app->formatter->modifyTimestamp($this->start_date, 'midnight');
		$end_date = Yii::$app->formatter->modifyTimestamp($this->end_date, 'tomorrow') - 1;

		/**
		 * Check if date in range
		 */
		$query = Rate::find()
		->where('start_date <= :end_date AND end_date >= :start_date', [
			'start_date' => $start_date,
			'end_date' => $end_date,
		])->andWhere(['rate_type_id' => $this->rate_type_id])->andWhere(['in', 'status', [
			Rate::STATUS_INACTIVE,
			Rate::STATUS_ACTIVE,
		]]);

		if ($this->_id != null) {
			$query->andWhere('id != :id', ['id' => $this->_id]);
		}

		$model = $query->one();

		if ($model != null) {
			return $this->addError($attribute, Yii::t('backend.rate', 'This date period has already been taken by {link}', [
				'link' => Html::a(Yii::t('backend.rate', 'Rate {name}', ['name' => $model->id]), ['/rate/edit', 'id' => $model->id], ['target' => '_blank']),
			]));
		}

		/**
		 * Check if date greater than last end date
		 */
		$query = Rate::find()->andWhere(['rate_type_id' => $this->rate_type_id])->andWhere(['in', 'status', [
			Rate::STATUS_INACTIVE,
			Rate::STATUS_ACTIVE,
		]]);

		if ($this->_id != null) {
			$query->andWhere('id != :id', ['id' => $this->_id]);
		}

		$model_last = $query->orderBy(['end_date' => SORT_DESC])->one();

		if ($model_last != null && ($start_date - $model_last->end_date) > 86400) {
			return $this->addError($attribute, Yii::t('backend.rate', '{attribute} must be no greater than {date}', [
				'attribute' => $this->getAttributeLabel('start_date'),
				'date' => Yii::$app->formatter->asDate($model_last->end_date + 86400),
			]));		
		}

		/**
		 * Check if date less than last start date
		 */
		$query = Rate::find()->andWhere(['rate_type_id' => $this->rate_type_id])->andWhere(['in', 'status', [
			Rate::STATUS_INACTIVE,
			Rate::STATUS_ACTIVE,
		]]);

		if ($this->_id != null) {
			$query->andWhere('id != :id', ['id' => $this->_id]);
		}

		$model_first = $query->orderBy(['start_date' => SORT_ASC])->one();

		if ($model_first != null && ($model_first->start_date - $end_date) > 86400) {
			return $this->addError('end_date', Yii::t('backend.rate', '{attribute} must be no less than {date}', [
				'attribute' => $this->getAttributeLabel('end_date'),
				'date' => Yii::$app->formatter->asDate($model_first->start_date - 86400),
			]));
		}
	}

	public function validateRateType($attribute, $params)
	{
		$type = Rate::getAliasRateBaseTypeAssociation($this->$attribute);

		switch ($type) {
			case RateType::TYPE_TAOZ:
				if ($this->season == null) {
					$this->addError('season', Yii::t('backend.rate', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('season'),
					]));
				}
				if ($this->shefel == null) {
					$this->addError('shefel', Yii::t('backend.rate', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('shefel'),
					]));
				}
				if ($this->geva == null) {
					$this->addError('geva', Yii::t('backend.rate', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('geva'),
					]));
				}
				if ($this->pisga == null) {
					$this->addError('pisga', Yii::t('backend.rate', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('pisga'),
					]));
				}
				if ($this->shefel_identifier == null) {
					$this->addError('shefel_identifier', Yii::t('backend.rate', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('shefel_identifier'),
					]));
				}
				if ($this->geva_identifier == null) {
					$this->addError('geva_identifier', Yii::t('backend.rate', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('geva_identifier'),
					]));
				}
				if ($this->pisga_identifier == null) {
					$this->addError('pisga_identifier', Yii::t('backend.rate', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('pisga_identifier'),
					]));
				}
				break;

			case RateType::TYPE_FIXED:
			default:
				if ($this->rate == null) {
					$this->addError('rate', Yii::t('backend.rate', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('rate'),
					]));
				}
				if ($this->identifier == null) {
					$this->addError('identifier', Yii::t('backend.rate', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('identifier'),
					]));
				}
				break;
		}
	}

	public function attributeLabels()
	{
		return [
			'rate_type_id' => Yii::t('backend.rate', 'Type'),
			'season' => Yii::t('backend.rate', 'Season'),
			'fixed_payment' => Yii::t('backend.rate', 'Fixed payment for monthly billed clients'),
			'rate' => Yii::t('backend.rate', 'Basic rate in Agorot'),
			'shefel' => Yii::t('backend.rate', 'Shefel'),
			'geva' => Yii::t('backend.rate', 'Geva'),
			'pisga' => Yii::t('backend.rate', 'Pisga'),
			'start_date' => Yii::t('backend.rate', 'Start date'),
			'end_date' => Yii::t('backend.rate', 'End date'),
			'identifier' => Yii::t('backend.rate', 'Rate identifier'),
			'shefel_identifier' => Yii::t('backend.rate', 'Shefel rate identifier'),
			'geva_identifier' => Yii::t('backend.rate', 'Geva rate identifier'),
			'pisga_identifier' => Yii::t('backend.rate', 'Pisga rate identifier'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

				$this->rate_type_id = $model->rate_type_id;
				$this->season = $model->season;
				$this->fixed_payment = $model->fixed_payment;
				$this->rate = $model->rate;
				$this->shefel = $model->shefel;
				$this->geva = $model->geva;
				$this->pisga = $model->pisga;
				$this->start_date = $model->start_date;
				$this->end_date = $model->end_date;
				$this->identifier = $model->identifier;
				$this->shefel_identifier = $model->shefel_identifier;
				$this->geva_identifier = $model->geva_identifier;
				$this->pisga_identifier = $model->pisga_identifier;
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new Rate();
		$model->rate_type_id = $this->rate_type_id;
		$model->fixed_payment = $this->fixed_payment;
		$model->start_date = $this->start_date;
		$model->end_date = $this->end_date;

		$type = Rate::getAliasRateBaseTypeAssociation($model->rate_type_id);

		switch ($type) {
			case RateType::TYPE_TAOZ:
				$model->season = $this->season;
				$model->shefel = $this->shefel;
				$model->geva = $this->geva;
				$model->pisga = $this->pisga;
				$model->shefel_identifier = $this->shefel_identifier;
				$model->geva_identifier = $this->geva_identifier;
				$model->pisga_identifier = $this->pisga_identifier;
				break;

			case RateType::TYPE_FIXED:
			default:
				$model->rate = $this->rate;
				$model->identifier = $this->identifier;
				break;
		}

		$event = new EventLogRate();
		$event->model = $model;
		$model->on(EventLogRate::EVENT_AFTER_INSERT, [$event, EventLogRate::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$model = Rate::findOne($this->_id);
			$model->rate_type_id = $this->rate_type_id;
			$model->fixed_payment = $this->fixed_payment;
			$model->start_date = $this->start_date;
			$model->end_date = $this->end_date;

			$type = Rate::getAliasRateBaseTypeAssociation($model->rate_type_id);

			switch ($type) {
				case RateType::TYPE_TAOZ:
					$model->season = $this->season;
					$model->shefel = $this->shefel;
					$model->geva = $this->geva;
					$model->pisga = $this->pisga;
					$model->shefel_identifier = $this->shefel_identifier;
					$model->geva_identifier = $this->geva_identifier;
					$model->pisga_identifier = $this->pisga_identifier;
					$model->rate = NULL;
					$model->identifier = NULL;
					break;

				case RateType::TYPE_FIXED:
				default:
					$model->rate = $this->rate;
					$model->identifier = $this->identifier;
					$model->season = NULL;
					$model->shefel = NULL;
					$model->geva = NULL;
					$model->pisga = NULL;
					$model->shefel_identifier = NULL;
					$model->geva_identifier = NULL;
					$model->pisga_identifier = NULL;
					break;
			}

			$event = new EventLogRate();
			$event->model = $model;
			$model->on(EventLogRate::EVENT_BEFORE_UPDATE, [$event, EventLogRate::METHOD_UPDATE]);

			$latest = Rate::find()->andWhere(['rate_type_id' => $model->rate_type_id])->andWhere(['in', 'status', [
				Rate::STATUS_INACTIVE,
				Rate::STATUS_ACTIVE,
			]])->orderBy(['end_date' => SORT_DESC])->one();
			
			if ($latest && $latest->id == $model->id) {
				SiteBillingSetting::updateAll([
					'fixed_payment' => $this->fixed_payment
				], [
					'rate_type_id' => $model->rate_type_id,
					'fixed_payment' => ArrayHelper::getValue($model->getOldAttributes(), 'fixed_payment'),
				]);
			}

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			$transaction->commit();
			return $model;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}

	public static function getListRateTypeAttributes()
	{
		$attributes = [];
		$list = Rate::getListRateBaseTypeAssociations();

		foreach ($list as $key => $value) {
			$attributes[$key] = ['data-base-type' => $value];
		}

		return $attributes;
	}
}
