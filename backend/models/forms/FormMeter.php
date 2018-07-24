<?php namespace backend\models\forms; use Exception; use Yii; use yii\base\Model; use yii\helpers\ArrayHelper; use yii\web\BadRequestHttpException; use common\models\Meter; 
use common\models\MeterType; use common\models\MeterChannel; use common\models\MeterSubchannel; use common\models\MeterChannelMultiplier; use 
common\models\ElectricityMeterRawData; use common\models\Tenant; use common\models\SiteMeterTree; use common\components\i18n\Formatter; use 
common\models\events\logs\EventLogMeter; /**
 * FormMeter is the class for meter create/edit.
 */ class FormMeter extends Model {
    const SCENARIO_CREATE = 'create';
    const SCENARIO_EDIT = 'edit';
    private $_id;
    public $name;
    public $breaker_name;
    public $site_id;
    public $type_id;
    public $type;
    public $ip_address;
    public $communication_type;
    public $data_usage_method = Meter::DATA_USAGE_METHOD_IMPORT_PLUS_EXPORT;
    public $physical_location;
    public $start_date;
    public $status;
    public $is_main = 0;
    public function rules() {
        return [
            [['breaker_name', 'physical_location'], 'filter', 'filter' => 'strip_tags'],
            [['breaker_name', 'physical_location', 'start_date'], 'filter', 'filter' => 'trim'],
            [['name', 'type_id', 'site_id'], 'required'],
            ['name', 'match', 'pattern' => Meter::NAME_VALIDATION_PATTERN],
            [['type_id', 'site_id'], 'integer'],
            ['type_id', 'in', 'range' => array_keys(Meter::getListMeterTypes()), 'skipOnEmpty' => false],
            [['ip_address'], 'ip'],
            [['name', 'breaker_name', 'ip_address', 'type'], 'string', 'max' => 255],
            [['physical_location'], 'string'],
            ['is_main','safe'],
            ['communication_type', 'default', 'value' => Meter::COMMUNICATION_TYPE_PLC],
            ['communication_type', 'in', 'range' => array_keys(Meter::getListCommunicationTypes()),
             'skipOnEmpty' => true],
            ['data_usage_method', 'in', 'range' => array_keys(Meter::getListDataUsageMethods()),
             'skipOnEmpty' => false],
            ['start_date', 'date', 'format' => Formatter::PHP_DATE_FORMAT],
            ['site_id', 'in', 'range' => array_keys(Tenant::getListSites()), 'skipOnEmpty' => false],
            ['status', 'in', 'range' => array_keys(Meter::getListStatuses())],
            // On scenario create
            ['name', 'unique', 'targetClass' => '\common\models\Meter', 'filter' => function ($model) {
                return $model->where('name = :name COLLATE utf8_bin', ['name' => $this->name])
                             ->andWhere(['in', 'status', [
                                 Meter::STATUS_INACTIVE,
                                 Meter::STATUS_ACTIVE,
                             ]]);
            }, 'on' => self::SCENARIO_CREATE],
            // On scenario edit
            ['name', 'unique', 'targetClass' => '\common\models\Meter', 'filter' => function ($model) {
                return $model->where('name = :name COLLATE utf8_bin', ['name' => $this->name])
                             ->andWhere('id != :id', ['id' => $this->_id])
                             ->andWhere(['in', 'status', [
                                 Meter::STATUS_INACTIVE,
                                 Meter::STATUS_ACTIVE,
                             ]]);
            }, 'on' => self::SCENARIO_EDIT],
        ];
    }
    public function attributeLabels() {
        return [
            'name' => Yii::t('backend.meter', 'Meter ID'),
            'breaker_name' => Yii::t('backend.meter', 'Breaker name'),
            'type_id' => Yii::t('backend.meter', 'Type'),
            'type' => 'Type Category',
            'communication_type' => Yii::t('backend.meter', 'Communication type'),
            'data_usage_method' => Yii::t('backend.meter', 'Data usage method'),
            'physical_location' => Yii::t('backend.meter', 'Phisical location on site'),
            'start_date' => Yii::t('backend.meter', 'Start date'),
            'status' => Yii::t('backend.meter', 'Status'),
            'site_id' => Yii::t('backend.meter', 'Site'),
            'ip_address' => Yii::t('backend.meter', 'IP'),
        ];
    }
    public function loadAttributes($scenario, Meter $model) {
        switch($scenario) {
            case self::SCENARIO_EDIT:
                $this->_id = $model->id;
                $this->name = $model->name;
                $this->breaker_name = $model->breaker_name;
                $this->type_id = $model->type_id;
                $this->communication_type = $model->communication_type;
                $this->data_usage_method = $model->data_usage_method;
                $this->physical_location = $model->physical_location;
                $this->start_date = $model->start_date;
                $this->status = $model->status;
                $this->site_id = $model->site_id;
                $this->ip_address = $model->ip_address;
                $this->type = $model->type;
                $this->is_main = $model->is_main;
                break;
            default:
                break;
        }
    }
    public function save() {
        if(!$this->validate()) return false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new Meter();
            $model->name = $this->name;
            $model->breaker_name = $this->breaker_name;
            $model->type_id = $this->type_id;
            $model->communication_type = $this->communication_type;
            $model->data_usage_method = $this->data_usage_method;
            $model->physical_location = $this->physical_location;
            $model->start_date = $this->start_date;
            $model->status = $this->status;
            $model->site_id = $this->site_id;
            $model->ip_address = $this->ip_address;
            $model->is_main = $this->is_main;
            $model->type = $this->type;
            $event = new EventLogMeter();
            $event->model = $model;
            $model->on(EventLogMeter::EVENT_AFTER_INSERT, [$event, EventLogMeter::METHOD_CREATE]);
            if(!$model->save()) {
                throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
            }
            $model_type = MeterType::findOne($this->type_id);
            $channels = $model_type->channels;
            $phases = $model_type->phases;
            $subchannel = 1;
            for($i = 1; $i <= $channels; $i++) {
                $model_channel = new MeterChannel();
                $model_channel->meter_id = $model->id;
                $model_channel->channel = $i;
                $model_channel->current_multiplier = MeterChannelMultiplier::DEFAULT_CURRENT_MULTIPLIER;
                $model_channel->voltage_multiplier = MeterChannelMultiplier::DEFAULT_VOLTAGE_MULTIPLIER;
                if(!$model_channel->save()) {
                    throw new BadRequestHttpException(implode(' ', $model_channel->getFirstErrors()));
                }
                for($j = 0; $j < $phases; $j++) {
                    $model_subchannel = new MeterSubchannel();
                    $model_subchannel->meter_id = $model_channel->meter_id;
                    $model_subchannel->channel_id = $model_channel->id;
                    $model_subchannel->channel = $subchannel;
                    if(!$model_subchannel->save()) {
                        throw new BadRequestHttpException(implode(' ', $model_subchannel->getFirstErrors()));
                    }
                    $subchannel++;
                }
            }
            $transaction->commit();
            return $model;
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new BadRequestHttpException($e->getMessage());
        }
    }
    public function edit() {
        if(!$this->validate()) return false;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = Meter::findOne($this->_id);
            $updated_type = ($model->type_id != $this->type_id);
            $updated_site_id = ($model->site_id != $this->site_id);
            $model->name = $this->name;
            $model->breaker_name = $this->breaker_name;
            $model->type_id = $this->type_id;
            $model->communication_type = $this->communication_type;
            $model->data_usage_method = $this->data_usage_method;
            $model->physical_location = $this->physical_location;
            $model->start_date = $this->start_date;
            $model->status = $this->status;
            $model->site_id = $this->site_id;
            $model->ip_address = $this->ip_address;
            $model->type = $this->type;
            $model->is_main = $this->is_main;
            $event = new EventLogMeter();
            $event->model = $model;
            $model->on(EventLogMeter::EVENT_BEFORE_UPDATE, [$event, EventLogMeter::METHOD_UPDATE]);
            if(!$model->save()) {
                throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
            }
            if($updated_site_id) {
                SiteMeterTree::deleteAll([
                                             'or',
                                             ['meter_id' => $model->id],
                                             ['parent_meter_id' => $model->id],
                                         ]);
            }
            if($updated_type) {
                MeterChannel::deleteAll('meter_id = :meter_id', ['meter_id' => $model->id]);
                MeterSubchannel::deleteAll('meter_id = :meter_id', ['meter_id' => $model->id]);
                ElectricityMeterRawData::deleteAll('meter_id = :name', ['name' => $model->name]);
                ElectricityMeterRawData::deleteCacheValue(["meter_raw_data:{$model->name}"]);
                $model_type = MeterType::findOne($this->type_id);
                $channels = $model_type->channels;
                $phases = $model_type->phases;
                $subchannel = 1;
                for($i = 1; $i <= $channels; $i++) {
                    $model_channel = new MeterChannel();
                    $model_channel->meter_id = $model->id;
                    $model_channel->channel = $i;
                    $model_channel->current_multiplier = MeterChannelMultiplier::DEFAULT_CURRENT_MULTIPLIER;
                    $model_channel->voltage_multiplier = MeterChannelMultiplier::DEFAULT_VOLTAGE_MULTIPLIER;
                    if(!$model_channel->save()) {
                        throw new BadRequestHttpException(implode(' ', $model_channel->getFirstErrors()));
                    }
                    for($j = 0; $j < $phases; $j++) {
                        $model_subchannel = new MeterSubchannel();
                        $model_subchannel->meter_id = $model_channel->meter_id;
                        $model_subchannel->channel_id = $model_channel->id;
                        $model_subchannel->channel = $subchannel;
                        if(!$model_subchannel->save()) {
                            throw new BadRequestHttpException(implode(' ', $model_subchannel->getFirstErrors()));
                        }
                        $subchannel++;
                    }
                }
            }
            $transaction->commit();
            return $model;
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
