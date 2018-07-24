<?php

namespace backend\models\forms;

use Yii;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\Tenant;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\RuleSingleChannel;
use common\components\i18n\Formatter;
use common\models\events\logs\EventLogRuleSingleChannel;

/**
 * FormRuleSingleChannel is the class for rule single channel create/edit.
 */
class FormRuleSingleChannel extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;
	private $_tenant_id;

	public $name;
	public $meter_id;
	public $channel_id;
	public $usage_tenant_id;
	public $use_type;
	public $use_percent;
	public $replaced;
	public $percent;
	public $from_hours;
	public $to_hours;
	public $start_date;
	public $status;
	public $total_bill_action;
	public $current_multiplier;
	public $voltage_multiplier;

	public function rules()
	{
		return [
			[['name'], 'filter', 'filter' => 'strip_tags'],
			[['name'], 'filter', 'filter' => 'trim'],
			[['name', 'total_bill_action'], 'required'],
			[['meter_id', 'channel_id', 'usage_tenant_id'], 'integer'],
			['replaced', 'boolean'],
			[['name'], 'string', 'max' => 255],
			['percent', 'number', 'min' => 0, 'max' => RuleSingleChannel::MAX_PERCENT],
			['from_hours', 'date', 'format' => Formatter::PHP_TIME_FORMAT],
			['to_hours', 'date', 'format' => Formatter::PHP_TIME_FORMAT],
			['use_type', 'default', 'value' => RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD],
			['use_type', 'in', 'range' => array_keys(RuleSingleChannel::getListUseTypes()), 'skipOnEmpty' => true],
			['use_percent', 'default', 'value' => RuleSingleChannel::USE_PERCENT_FULL],
			['use_percent', 'in', 'range' => array_keys(RuleSingleChannel::getListUsePercents()), 'skipOnEmpty' => true],
			['start_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['total_bill_action', 'in', 'range' => array_keys(RuleSingleChannel::getListTotalBillActions()), 'skipOnEmpty' => false],
			['status', 'in', 'range' => array_keys(RuleSingleChannel::getListStatuses()), 'skipOnEmpty' => true],
			['meter_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Meter', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['status' => Meter::STATUS_ACTIVE]);
			}, 'when' => function($model) {
				return $model->use_type == RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD;
			}],
			['channel_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\MeterChannel', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere([
					'meter_id' => $this->meter_id,
					'status' => MeterChannel::STATUS_ACTIVE,
				]);
			}, 'when' => function($model) {
				return $model->use_type == RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD;
			}, 'message' => Yii::t('backend.rule', "Inactive channel can't be selected.")],
			['usage_tenant_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Tenant', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['status' => Tenant::STATUS_ACTIVE]);
			}, 'when' => function($model) {
				return $model->use_type == RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD;
			}],

			// On scenario - create
			['channel_id', 'unique', 'targetClass' => '\common\models\RuleSingleChannel', 'filter' => function($model){
				return $model->andWhere([
					'total_bill_action' => $this->total_bill_action,
					'status' => RuleSingleChannel::STATUS_ACTIVE,
				])->andWhere('tenant_id = :tenant_id', ['tenant_id' => $this->_tenant_id]);
			}, 'on' => self::SCENARIO_CREATE, 'message' => Yii::t('backend.rule', '{attribute} has already been taken.'), 'when' => function($model) {
				return $model->use_type == RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD;
			}],
			['usage_tenant_id', 'unique', 'targetClass' => '\common\models\RuleSingleChannel', 'filter' => function($model){
				return $model->andWhere([
					'total_bill_action' => $this->total_bill_action,
					'status' => RuleSingleChannel::STATUS_ACTIVE,
				])->andWhere('tenant_id = :tenant_id', ['tenant_id' => $this->_tenant_id]);
			}, 'on' => self::SCENARIO_CREATE, 'message' => Yii::t('backend.rule', '{attribute} has already been taken.'), 'when' => function($model) {
				return $model->use_type == RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD;
			}],

			// On scenario - edit
			['channel_id', 'unique', 'targetClass' => '\common\models\RuleSingleChannel', 'filter' => function($model){
				return $model->andWhere([
					'total_bill_action' => $this->total_bill_action,
					'status' => RuleSingleChannel::STATUS_ACTIVE,
				])->andWhere('tenant_id = :tenant_id AND id != :id', [
					'tenant_id' => $this->_tenant_id,
					'id' => $this->_id,
				]);
			}, 'on' => self::SCENARIO_EDIT, 'message' => Yii::t('backend.rule', '{attribute} has already been taken.'), 'when' => function($model) {
				return $model->use_type == RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD;
			}],
			['usage_tenant_id', 'unique', 'targetClass' => '\common\models\RuleSingleChannel', 'filter' => function($model){
				return $model->andWhere([
					'total_bill_action' => $this->total_bill_action,
					'status' => RuleSingleChannel::STATUS_ACTIVE,
				])->andWhere('tenant_id = :tenant_id AND id != :id', [
					'tenant_id' => $this->_tenant_id,
					'id' => $this->_id,
				]);
			}, 'on' => self::SCENARIO_EDIT, 'message' => Yii::t('backend.rule', '{attribute} has already been taken.'), 'when' => function($model) {
				return $model->use_type == RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD;
			}],

			['use_percent', 'validateUsePercent'],
			['channel_id', 'validateChannelId', 'when' => function($model) {
				return $model->use_type == RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD;
			}],
		];
	}

	public function validateUsePercent($attribute, $params)
	{
		switch ($this->$attribute) {
			case RuleSingleChannel::USE_PERCENT_PARTIAL:
				if ($this->percent == null) {
					$this->addError('percent', Yii::t('backend.rule', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('percent'),
					]));
				}
				break;

			case RuleSingleChannel::USE_PERCENT_HOUR:
				if ($this->from_hours == null) {
					$this->addError('from_hours', Yii::t('backend.rule', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('from_hours'),
					]));
				}
				if ($this->to_hours == null) {
					$this->addError('to_hours', Yii::t('backend.rule', '{attribute} is required.', [
						'attribute' => $this->getAttributeLabel('to_hours'),
					]));
				}

				if ($this->from_hours >= $this->to_hours) {
					$this->addError('to_hours', Yii::t('backend.rule', '{attribute} must be greater than {value}.', [
						'attribute' => $this->getAttributeLabel('to_hours'),
						'value' => $this->from_hours,
					]));				
				}
				break;
			
			case RuleSingleChannel::USE_PERCENT_RELATIVE_TO_SQUARE_FOOTAGE:
				$model_tenant = Tenant::findOne($this->_tenant_id);
				$percent = $model_tenant->getAliasSiteFootage();

				if (!$percent) {
					$this->addError($attribute, Yii::t('backend.rule', "Relative to tenant's square footage must be greater than {min}.", [
						'attribute' => $this->getAttributeLabel($attribute),
						'min' => 0,
					]));
				}
				break;

			case RuleSingleChannel::USE_PERCENT_FULL:
			default:
				break;
		}
	}

	public function validateChannelId($attribute, $params)
	{
		$value = $this->$attribute;
		$model_channel = MeterChannel::findOne($value);
		$model_tenant = Tenant::findOne($this->_tenant_id);
		$model_site = $model_tenant->relationSite;
		$percent = $this->getPercent();
		$sum_query = $model_channel->getRelationRuleSingleChannels()
		->joinWith(['relationTenant'])
		->andWhere([
			'and',
			[RuleSingleChannel::tableName(). '.total_bill_action' => $this->total_bill_action],
			[RuleSingleChannel::tableName(). '.status' => RuleSingleChannel::STATUS_ACTIVE],
			[Tenant::tableName(). '.status' => RuleSingleChannel::STATUS_ACTIVE],
			['in', Tenant::tableName(). '.to_issue', [
				Site::TO_ISSUE_AUTOMATIC,
				Site::TO_ISSUE_MANUAL,
			]],
			[
				'or',
				Tenant::tableName(). '.exit_date IS NULL',
				['>', Tenant::tableName(). '.exit_date', strtotime('midnight')],
			],
		]);

		if ($this->_id != null) {
			$sum_query->andWhere(RuleSingleChannel::tableName(). '.id != :id', ['id' => $this->_id]);
		}

		$sum = $sum_query->sum('percent');

		if ($sum) {
			if ($sum >= RuleSingleChannel::MAX_PERCENT) {
				$this->addError($attribute, Yii::t('backend.rule', '{attribute} has already been taken.', [
					'attribute' => $this->getAttributeLabel($attribute),
				]));
			} elseif ($sum + $percent > RuleSingleChannel::MAX_PERCENT) {
				$this->addError($attribute, Yii::t('backend.rule', '{attribute} percentage must be no greater than {value}.', [
					'attribute' => $this->getAttributeLabel($attribute),
					'value' => Yii::$app->formatter->asPercentage(RuleSingleChannel::MAX_PERCENT - $sum),
				]));
			}
		}
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('backend.rule', 'Name'),
			'meter_id' => Yii::t('backend.rule', 'Meter ID'),
			'channel_id' => Yii::t('backend.rule', 'Channel'),
			'use_type' => Yii::t('backend.rule', 'Usage type'),
			'use_percent' => Yii::t('backend.rule', 'Usage percentage'),
			'usage_tenant_id' => Yii::t('backend.rule', 'Tenant'),
			'replaced' => Yii::t('backend.rule', 'Meter has been replaced'),
			'percent' => Yii::t('backend.rule', 'Usage percentage'),
			'from_hours' => Yii::t('backend.rule', 'From hour'),
			'to_hours' => Yii::t('backend.rule', 'To hour'),
			'start_date' => Yii::t('backend.rule', 'Start date'),
			'total_bill_action' => Yii::t('backend.rule', 'Action'),
			'status' => Yii::t('backend.rule', 'Status'),
			'current_multiplier' => Yii::t('backend.rule', 'Current multiplier'),
			'voltage_multiplier' => Yii::t('backend.rule', 'Voltage multiplier'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_CREATE:
				$this->_tenant_id = $model->id;

				$meters = Meter::getListMeters($model->site_id);
				
				if ($meters != null) {
					$meter_id = array_shift(array_keys($meters));
					$this->meter_id = $meter_id;
				}

				$this->total_bill_action = RuleSingleChannel::TOTAL_BILL_ACTION_PLUS;
				$this->status = RuleSingleChannel::STATUS_ACTIVE;
				break;

			case self::SCENARIO_EDIT:
				$this->_id = $model->id;
				$this->_tenant_id = $model->tenant_id;

				switch ($model->use_type) {
					case RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD:
						$this->usage_tenant_id = $model->usage_tenant_id;

						$meters = Meter::getListMeters($model->relationTenant->site_id);
						
						if ($meters != null) {
							$meter_id = array_shift(array_keys($meters));
							$this->meter_id = $meter_id;
						}
						break;
					
					case RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD:
					default:
						$this->meter_id = $model->relationMeterChannel->meter_id;
						$this->channel_id = $model->channel_id;
						break;
				}

				$this->name = $model->name;
				$this->use_type = $model->use_type;
				$this->use_percent = $model->use_percent;
				$this->replaced = $model->replaced;
				$this->percent = $model->percent;
				$this->from_hours = $model->from_hours;
				$this->to_hours = $model->to_hours;
				$this->start_date = $model->start_date;
				$this->total_bill_action = $model->total_bill_action;
				$this->status = $model->status;

				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new RuleSingleChannel();
		$model->name = $this->name;
		$model->tenant_id = $this->_tenant_id;
		$model->use_type = $this->use_type;
		$model->use_percent = $this->use_percent;
		$model->start_date = $this->start_date;
		$model->total_bill_action = $this->total_bill_action;
		$model->status = $this->status;

		switch ($model->use_type) {
			case RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD:
				$model->usage_tenant_id = $this->usage_tenant_id;
				break;

			case RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD:
			default:
				$model->channel_id = $this->channel_id;
				break;
		}

		switch ($model->use_percent) {
			case RuleSingleChannel::USE_PERCENT_HOUR:
				$model->from_hours = $this->from_hours;
				$model->to_hours = $this->to_hours;
				break;

			default:
				break;
		}

		$model->percent = $this->getPercent();

		$event = new EventLogRuleSingleChannel();
		$event->model = $model;
		$model->on(EventLogRuleSingleChannel::EVENT_AFTER_INSERT, [$event, EventLogRuleSingleChannel::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}
		
		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = RuleSingleChannel::findOne($this->_id);
		$model->name = $this->name;
		$model->use_type = $this->use_type;
		$model->use_percent = $this->use_percent;
		$model->start_date = $this->start_date;
		$model->total_bill_action = $this->total_bill_action;
		$model->status = $this->status;

		switch ($model->use_type) {
			case RuleSingleChannel::USE_TYPE_SINGLE_TENANT_LOAD:
				$model->channel_id = NULL;
				$model->usage_tenant_id = $this->usage_tenant_id;
				break;

			case RuleSingleChannel::USE_TYPE_SINGLE_METER_LOAD:
			default:
				$model->usage_tenant_id = NULL;
				$model->channel_id = $this->channel_id;
				break;
		}

		switch ($model->use_percent) {
			case RuleSingleChannel::USE_PERCENT_HOUR:
				$model->from_hours = $this->from_hours;
				$model->to_hours = $this->to_hours;
				break;

			default:
				$model->from_hours = NULL;
				$model->to_hours = NULL;
				break;
		}

		$model->percent = $this->getPercent();

		$event = new EventLogRuleSingleChannel();
		$event->model = $model;
		$model->on(EventLogRuleSingleChannel::EVENT_BEFORE_UPDATE, [$event, EventLogRuleSingleChannel::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function getPercent()
	{
		switch ($this->use_percent) {
			case RuleSingleChannel::USE_PERCENT_PARTIAL:
				$percent = $this->percent;
				break;

			case RuleSingleChannel::USE_PERCENT_RELATIVE_TO_SQUARE_FOOTAGE:
				$model_tenant = Tenant::findOne($this->_tenant_id);
				$percent = $model_tenant->getAliasSiteFootage();
				break;

			case RuleSingleChannel::USE_PERCENT_HOUR:
				$from_hours = strtotime($this->from_hours);
				$to_hours = strtotime($this->to_hours);
				$diff = $to_hours - $from_hours;
				$percent = ($diff * 100) / 86400;
				break;
			
			case RuleSingleChannel::USE_PERCENT_FULL:
			default:
				$percent = RuleSingleChannel::MAX_PERCENT;
				break;
		}

		return $percent;
	}
}
