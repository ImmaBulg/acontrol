<?php
namespace backend\models\forms;

use common\models\events\logs\EventLogRuleGroupLoad;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\MeterChannelGroup;
use common\models\RuleGroupLoad;
use common\models\TenantGroup;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;

/**
 * FormRuleGroupLoad is the class for rule group load create/edit.
 */
class FormRuleGroupLoad extends Model
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_EDIT = 'edit';

    private $_id;
    private $_tenant_id;

    public $name;
    public $total_bill_action;
    public $use_type;
    public $use_percent;
    public $percent;
    public $usage_tenant_group_id;
    public $meter_id;
    public $channel_id;
    public $channel_group_id;
    public $tenant_group_id;
    public $status;


    public function rules() {
        return [
            [['name', 'total_bill_action', 'use_type', 'use_percent'], 'required'],
            [['channel_id', 'channel_group_id', 'tenant_group_id', 'usage_tenant_group_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['percent'], 'number', 'min' => 0, 'max' => 100],
            ['percent', 'required', 'when' => function ($model) {
                return $model->use_percent == RuleGroupLoad::USE_PERCENT_FLAT;
            },'whenClient' => 'function() {return false;}'],
            ['use_type', 'in', 'range' => array_keys(RuleGroupLoad::getListUseTypes()), 'skipOnEmpty' => false],
            ['use_percent', 'in', 'range' => array_keys(RuleGroupLoad::getListUsePercents()), 'skipOnEmpty' => false],
            ['total_bill_action', 'in', 'range' => array_keys(RuleGroupLoad::getListTotalBillActions()),
             'skipOnEmpty' => false],
            ['status', 'in', 'range' => array_keys(RuleGroupLoad::getListStatuses()), 'skipOnEmpty' => true],
            ['meter_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Meter',
             'modelAttribute' => 'id', 'filter' => function (ActiveQuery $model) {
                return $model->andWhere(['status' => Meter::STATUS_ACTIVE]);
            }],
            ['channel_id', '\common\components\validators\ModelExistsValidator',
             'modelClass' => '\common\models\MeterChannel', 'modelAttribute' => 'id',
             'filter' => function (ActiveQuery $model) {
                 return $model->andWhere([
                                             'meter_id' => $this->meter_id,
                                             'status' => MeterChannel::STATUS_ACTIVE,
                                         ]);
             }, 'message' => Yii::t('backend.rule', "Inactive channel can't be selected.")],
            ['tenant_group_id', '\common\components\validators\ModelExistsValidator',
             'modelClass' => '\common\models\TenantGroup', 'modelAttribute' => 'id',
             'filter' => function (ActiveQuery $model) {
                 return $model->andWhere(['status' => TenantGroup::STATUS_ACTIVE]);
             }],
            ['channel_group_id', '\common\components\validators\ModelExistsValidator',
             'modelClass' => '\common\models\MeterChannelGroup', 'modelAttribute' => 'id',
             'filter' => function (ActiveQuery $model) {
                 return $model->andWhere(['status' => MeterChannelGroup::STATUS_ACTIVE]);
             }],
            ['usage_tenant_group_id', '\common\components\validators\ModelExistsValidator',
             'modelClass' => '\common\models\TenantGroup', 'modelAttribute' => 'id'],
            ['usage_tenant_group_id', 'required', 'when' => function ($model) {
                return in_array($model->use_percent, [
                    RuleGroupLoad::USE_PERCENT_FOOTAGE,
                    RuleGroupLoad::USE_PERCENT_USAGE,
                ]);
            }, 'enableClientValidation' => false],
            ['use_type', 'validateUseType'],
        ];
    }


    public function validateUseType($attribute, $params) {
        switch($this->$attribute) {
            case RuleGroupLoad::USE_TYPE_SINGLE_METER_LOAD:
                if($this->channel_id == null) {
                    $this->addError('channel_id', Yii::t('backend.rule', '{attribute} is required.', [
                        'attribute' => $this->getAttributeLabel('channel_id'),
                    ]));
                }
                break;
            case RuleGroupLoad::USE_TYPE_SINGLE_TENANT_GROUP_LOAD:
                if($this->tenant_group_id == null) {
                    $this->addError('tenant_group_id', Yii::t('backend.rule', '{attribute} is required.', [
                        'attribute' => $this->getAttributeLabel('tenant_group_id'),
                    ]));
                }
                break;
            case RuleGroupLoad::USE_TYPE_SINGLE_METER_GROUP_LOAD:
            default:
                if($this->channel_group_id == null) {
                    $this->addError('channel_group_id', Yii::t('backend.rule', '{attribute} is required.', [
                        'attribute' => $this->getAttributeLabel('channel_group_id'),
                    ]));
                }
                break;
        }
    }


    public function attributeLabels() {
        return [
            'name' => Yii::t('backend.rule', 'Name'),
            'total_bill_action' => Yii::t('backend.rule', 'Action'),
            'use_type' => Yii::t('backend.rule', 'Usage type'),
            'use_percent' => Yii::t('backend.rule', 'Usage percentage'),
            'percent' => Yii::t('backend.rule', 'Percent'),
            'usage_tenant_group_id' => Yii::t('backend.rule', 'Usage tenant group'),
            'meter_id' => Yii::t('backend.rule', 'Meter ID'),
            'channel_id' => Yii::t('backend.rule', 'Channel'),
            'channel_group_id' => Yii::t('backend.rule', 'Channel group'),
            'tenant_group_id' => Yii::t('backend.rule', 'Tenant group'),
            'status' => Yii::t('backend.rule', 'Status'),
        ];
    }


    public function loadAttributes($scenario, $model = null) {
        switch($scenario) {
            case self::SCENARIO_CREATE:
                $this->_tenant_id = $model->id;
                $this->status = RuleGroupLoad::STATUS_ACTIVE;
                $this->use_type = RuleGroupLoad::USE_TYPE_SINGLE_METER_GROUP_LOAD;
                $meters = Meter::getListMeters($model->site_id);
                if($meters != null) {
                    $meter_id = array_shift(array_keys($meters));
                    $this->meter_id = $meter_id;
                }
                break;
            case self::SCENARIO_EDIT:
                $this->_id = $model->id;
                $this->_tenant_id = $model->tenant_id;
                $this->name = $model->name;
                $this->total_bill_action = $model->total_bill_action;
                $this->use_type = $model->use_type;
                $this->use_percent = $model->use_percent;
                $this->percent = $model->percent;
                $this->usage_tenant_group_id = $model->usage_tenant_group_id;
                $this->status = $model->status;
                switch($model->use_type) {
                    case RuleGroupLoad::USE_TYPE_SINGLE_METER_LOAD:
                        $this->meter_id = $model->relationMeterChannel->meter_id;
                        $this->channel_id = $model->channel_id;
                        break;
                    case RuleGroupLoad::USE_TYPE_SINGLE_TENANT_GROUP_LOAD:
                        $this->tenant_group_id = $model->tenant_group_id;
                        break;
                    case RuleGroupLoad::USE_TYPE_SINGLE_METER_GROUP_LOAD:
                    default:
                        $this->channel_group_id = $model->channel_group_id;
                        break;
                }
                if($this->channel_id == null) {
                    $meters = Meter::getListMeters($model->relationTenant->site_id);
                    if($meters != null) {
                        $meter_id = array_shift(array_keys($meters));
                        $this->meter_id = $meter_id;
                    }
                }
                break;
            default:
                break;
        }
    }


    public function save() {
        if(!$this->validate()) return false;
        $model = new RuleGroupLoad();
        $model->tenant_id = $this->_tenant_id;
        $model->name = $this->name;
        $model->total_bill_action = $this->total_bill_action;
        $model->use_type = $this->use_type;
        $model->use_percent = $this->use_percent;
        $model->percent = $this->percent;
        $model->status = $this->status;
        switch($model->use_type) {
            case RuleGroupLoad::USE_TYPE_SINGLE_METER_LOAD:
                $model->channel_id = $this->channel_id;
                break;
            case RuleGroupLoad::USE_TYPE_SINGLE_TENANT_GROUP_LOAD:
                $model->tenant_group_id = $this->tenant_group_id;
                break;
            case RuleGroupLoad::USE_TYPE_SINGLE_METER_GROUP_LOAD:
            default:
                $model->channel_group_id = $this->channel_group_id;
                break;
        }
        switch($this->use_percent) {
            case RuleGroupLoad::USE_PERCENT_FOOTAGE:
            case RuleGroupLoad::USE_PERCENT_USAGE:
                $model->usage_tenant_group_id = $this->usage_tenant_group_id;
                break;
        }
        $event = new EventLogRuleGroupLoad();
        $event->model = $model;
        $model->on(EventLogRuleGroupLoad::EVENT_AFTER_INSERT, [$event, EventLogRuleGroupLoad::METHOD_CREATE]);
        if(!$model->save()) {
            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
        }
        return $model;
    }


    public function edit() {
        if(!$this->validate()) return false;
        $model = RuleGroupLoad::findOne($this->_id);
        $model->name = $this->name;
        $model->total_bill_action = $this->total_bill_action;
        $model->use_type = $this->use_type;
        $model->use_percent = $this->use_percent;
        $model->percent = $this->percent;
        $model->status = $this->status;
        switch($model->use_type) {
            case RuleGroupLoad::USE_TYPE_SINGLE_METER_LOAD:
                $model->channel_group_id = null;
                $model->tenant_group_id = null;
                $model->channel_id = $this->channel_id;
                break;
            case RuleGroupLoad::USE_TYPE_SINGLE_TENANT_GROUP_LOAD:
                $model->channel_id = null;
                $model->channel_group_id = null;
                $model->tenant_group_id = $this->tenant_group_id;
                break;
            case RuleGroupLoad::USE_TYPE_SINGLE_METER_GROUP_LOAD:
            default:
                $model->channel_id = null;
                $model->tenant_group_id = null;
                $model->channel_group_id = $this->channel_group_id;
                break;
        }
        switch($this->use_percent) {
            case RuleGroupLoad::USE_PERCENT_FOOTAGE:
            case RuleGroupLoad::USE_PERCENT_USAGE:
                $model->usage_tenant_group_id = $this->usage_tenant_group_id;
                break;
            case RuleGroupLoad::USE_PERCENT_FLAT:
            default:
                $model->usage_tenant_group_id = null;
                break;
        }
        $event = new EventLogRuleGroupLoad();
        $event->model = $model;
        $model->on(EventLogRuleGroupLoad::EVENT_BEFORE_UPDATE, [$event, EventLogRuleGroupLoad::METHOD_UPDATE]);
        if(!$model->save()) {
            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
        }
        return $model;
    }
}
