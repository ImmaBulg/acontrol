<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;

use common\models\Tenant;
use common\models\RuleFixedLoad;
use common\models\events\logs\EventLogRuleFixedLoad;

/**
 * FormRuleFixedLoad is the class for rule fixed load create/edit.
 */
class FormRuleFixedLoad extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	const USE_TYPE_VALUE = 1;
	const USE_TYPE_CONSUMPTION = 2;

	const RATE_TYPE_TAOZ = 1;
	const RATE_TYPE_FIXED = 2;
	const RATE_TYPE_FLAT_AMOUNT = 3;
	const RATE_TYPE_PERCENT_AMOUNT = 4;

	private $_id;
	private $_tenant_id;

	public $name;
	public $use_type;
	public $use_frequency;
	public $value;
	public $shefel;
	public $geva;
	public $pisga;
	public $description;
	public $status;
	public $rate_type_flat_id;
	public $rate_type_fixed_id;

	public function rules()
	{
		return [
			[['name', 'description'], 'filter', 'filter' => 'strip_tags'],
			[['name', 'description'], 'filter', 'filter' => 'trim'],
			[['name', 'use_type', 'use_frequency', 'description'], 'required'],
			[['name'], 'string', 'max' => 255],
			[['value', 'shefel', 'geva', 'pisga'], 'number'],
			[['description'], 'string'],
			[['rate_type_flat_id', 'rate_type_fixed_id'], 'integer'],
			['use_type', 'in', 'range' => array_keys(RuleFixedLoad::getListUseTypes()), 'skipOnEmpty' => false],
			['use_frequency', 'in', 'range' => array_keys(RuleFixedLoad::getListUseFrequencies()), 'skipOnEmpty' => false],
			['status', 'in', 'range' => array_keys(RuleFixedLoad::getListStatuses()), 'skipOnEmpty' => true],
			['use_type', 'validateUseType'],
		];
	}

	public function validateUseType($attribute, $params)
	{
		switch ($this->$attribute) {
			case RuleFixedLoad::USE_TYPE_KWH_TAOZ:
				if ($this->shefel == null) {
					$this->addError('shefel', Yii::t('backend.rule', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('shefel'),
					]));
				}

				if ($this->geva == null) {
					$this->addError('geva', Yii::t('backend.rule', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('geva'),
					]));
				}

				if ($this->pisga == null) {
					$this->addError('pisga', Yii::t('backend.rule', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('pisga'),
					]));
				}

				if ($this->rate_type_flat_id == null) {
					$this->addError('rate_type_flat_id', Yii::t('backend.rule', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('rate_type_flat_id'),
					]));
				}

				break;

			case RuleFixedLoad::USE_TYPE_KWH_FIXED:
				if ($this->value == null) {
					$this->addError('value', Yii::t('backend.rule', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('value'),
					]));
				}

				if ($this->rate_type_flat_id == null) {
					$this->addError('rate_type_flat_id', Yii::t('backend.rule', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('rate_type_fixed_id'),
					]));
				}
				break;


			case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
                if ($this->value == null) {
                    $this->addError('value', Yii::t('backend.rule', '{attribute} is required.', [
                        'attribute' => $this->getAttributeLabel('value'),
                    ]));
                }

                if ($this->rate_type_fixed_id == null) {
                    $this->addError('rate_type_fixed_id', Yii::t('backend.rule', '{attribute} is required.', [
                        'attribute' => $this->getAttributeLabel('rate_type_fixed_id'),
                    ]));
                }
                break;
            case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
			case RuleFixedLoad::USE_TYPE_MONEY:
			default:
				if ($this->value == null) {
					$this->addError('value', Yii::t('backend.rule', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('value'),
					]));
				}
				break;
		}
	}

	public function attributeLabels()
	{
		return [
			'rate_type_flat_id' => Yii::t('backend.rule', 'Rate type'),
			'rate_type_fixed_id' => Yii::t('backend.rule', 'Rate type'),
			'name' => Yii::t('backend.rule', 'Name'),
			'use_type' => Yii::t('backend.rule', 'Usage type'),
			'use_frequency' => Yii::t('backend.rule', 'Usage frequency'),
			'value' => Yii::t('backend.rule', 'Value'),
			'shefel' => Yii::t('backend.rule', 'Shefel'),
			'geva' => Yii::t('backend.rule', 'Geva'),
			'pisga' => Yii::t('backend.rule', 'Pisga'),
			'description' => Yii::t('backend.rule', 'Description'),
			'status' => Yii::t('backend.rule', 'Status'),
		];
	}

	public static function getListUseTypeAttributes()
	{
		return [
			RuleFixedLoad::USE_TYPE_MONEY => [
				'data-use-type' => self::USE_TYPE_VALUE,
			],
			RuleFixedLoad::USE_TYPE_KWH_TAOZ => [
				'data-use-type' => self::USE_TYPE_CONSUMPTION,
				'data-rate-type' => self::RATE_TYPE_TAOZ,
			],
			RuleFixedLoad::USE_TYPE_KWH_FIXED => [
				'data-use-type' => self::USE_TYPE_VALUE,
				'data-rate-type' => self::RATE_TYPE_FLAT_AMOUNT,
			],
			RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT => [
				'data-use-type' => self::USE_TYPE_VALUE,
			],
			RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE => [
				'data-use-type' => self::USE_TYPE_VALUE,
                'data-rate-type' => self::RATE_TYPE_PERCENT_AMOUNT,
			],
		];
	}

	public function loadAttributes($scenario, $model = null)
	{
		switch ($scenario) {
			case self::SCENARIO_CREATE:
				$this->_tenant_id = $model->id;
				
				$this->use_frequency = RuleFixedLoad::USE_FREQUENCY_ONGOING;
				$this->status = RuleFixedLoad::STATUS_ACTIVE;
				break;

			case self::SCENARIO_EDIT:
				$this->_id = $model->id;
				$this->_tenant_id = $model->tenant_id;

				$this->rate_type_flat_id = $model->rate_type_id;
				$this->rate_type_fixed_id = $model->rate_type_id;
				$this->name = $model->name;
				$this->use_type = $model->use_type;
				$this->use_frequency = $model->use_frequency;
				$this->value = $model->value;
				$this->shefel = $model->shefel;
				$this->geva = $model->geva;
				$this->pisga = $model->pisga;
				$this->description = $model->description;
				$this->status = $model->status;
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new RuleFixedLoad();
		$model->name = $this->name;
		$model->tenant_id = $this->_tenant_id;
		$model->use_type = $this->use_type;
		$model->use_frequency = $this->use_frequency;
		$model->description = $this->description;
		$model->status = $this->status;
		switch ($model->use_type) {
			case RuleFixedLoad::USE_TYPE_KWH_TAOZ:
				$model->shefel = $this->shefel;
				$model->geva = $this->geva;
				$model->pisga = $this->pisga;
				$model->rate_type_id = $this->rate_type_flat_id;
				break;

			case RuleFixedLoad::USE_TYPE_KWH_FIXED:
                $model->value = $this->value;
                $model->rate_type_id = $this->rate_type_flat_id;
                $model->shefel = NULL;
                $model->geva = NULL;
                $model->pisga = NULL;
                break;

            case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
			case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
                $model->value = $this->value;
                $model->rate_type_id = $this->rate_type_fixed_id;
                $model->shefel = NULL;
                $model->geva = NULL;
                $model->pisga = NULL;
                break;
			case RuleFixedLoad::USE_TYPE_MONEY:
			default:
				$model->value = $this->value;
				break;
		}

		$event = new EventLogRuleFixedLoad();
		$event->model = $model;
		$model->on(EventLogRuleFixedLoad::EVENT_AFTER_INSERT, [$event, EventLogRuleFixedLoad::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = RuleFixedLoad::findOne($this->_id);
		$model->name = $this->name;
		$model->use_type = $this->use_type;
		$model->use_frequency = $this->use_frequency;
		$model->description = $this->description;
		$model->status = $this->status;
		switch ($model->use_type) {
			case RuleFixedLoad::USE_TYPE_KWH_TAOZ:
				$model->shefel = $this->shefel;
				$model->geva = $this->geva;
				$model->pisga = $this->pisga;
				$model->rate_type_id = $this->rate_type_flat_id;
				$model->value = NULL;
				break;

			case RuleFixedLoad::USE_TYPE_KWH_FIXED:
				$model->value = $this->value;
				$model->rate_type_id = $this->rate_type_flat_id;
				$model->shefel = NULL;
				$model->geva = NULL;
				$model->pisga = NULL;
				break;

            case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_USAGE:
			case RuleFixedLoad::USE_TYPE_FLAT_ADDITION_TOTAL_BILL_AMOUNT:
                $model->value = $this->value;
                $model->rate_type_id = $this->rate_type_fixed_id;
                $model->shefel = NULL;
                $model->geva = NULL;
                $model->pisga = NULL;
                break;
			case RuleFixedLoad::USE_TYPE_MONEY:
			default:
				$model->value = $this->value;
				$model->shefel = NULL;
				$model->geva = NULL;
				$model->pisga = NULL;
				$model->rate_type_id = NULL;
				break;
		}

		$event = new EventLogRuleFixedLoad();
		$event->model = $model;
		$model->on(EventLogRuleFixedLoad::EVENT_BEFORE_UPDATE, [$event, EventLogRuleFixedLoad::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
