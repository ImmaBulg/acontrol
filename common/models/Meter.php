<?php

namespace common\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;
use common\components\behaviors\ToTimestampBehavior;

/**
 * Meter is the class for the table "meter".
 * @property $type
 * @property $name
 * @property $type_id
 * @property int $id [int(11)]
 * @property bool $communication_type [tinyint(1)]
 * @property string $physical_location
 * @property bool $status [tinyint(1)]
 * @property int $created_at [int(11)]
 * @property int $modified_at [int(11)]
 * @property int $created_by [int(11)]
 * @property int $modified_by [int(11)]
 * @property string $old_id [varchar(255)]
 * @property int $start_date [int(11)]
 * @property string $breaker_name [varchar(255)]
 * @property int $site_id [int(11)]
 * @property bool $data_usage_method [tinyint(1)]
 * @property string $ip_address [varchar(255)]
 * @property bool $is_main
 */
class Meter extends ActiveRecord
{
    const NAME_VALIDATION_PATTERN = '/^[0-9]+$/';

    const COMMUNICATION_NOT_AVAILABLE = 1;
    const COMMUNICATION_TYPE_PLC = 2;
    const COMMUNICATION_TYPE_485_LINE = 3;
    const COMMUNICATION_TYPE_RF = 4;

    const DATA_USAGE_METHOD_IMPORT = 1;
    const DATA_USAGE_METHOD_IMPORT_PLUS_EXPORT = 2;
    const DATA_USAGE_METHOD_IMPORT_MINUS_EXPORT = 3;
    const DATA_USAGE_METHOD_EXPORT = 4;
    const DATA_USAGE_METHOD_DEFAULT = 5;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    const TYPE_ELECTRICITY = 'electricity';
    const TYPE_AIR = 'air';


    public static function tableName() {
        return 'meter';
    }


    public function rules() {
        return [
            [['breaker_name', 'physical_location'], 'filter', 'filter' => 'strip_tags'],
            [['breaker_name', 'physical_location'], 'filter', 'filter' => 'trim'],
            [['name', 'type_id'], 'required'],
            ['name', 'match', 'pattern' => self::NAME_VALIDATION_PATTERN],
            ['name', 'unique', 'filter' => function ($model) {
                return $model->andWhere(['in', 'status', [
                    self::STATUS_INACTIVE,
                    self::STATUS_ACTIVE,
                ]]);
            }],
            [['type_id', 'site_id'], 'integer'],
            [['ip_address'], 'ip'],
            [['name', 'breaker_name', 'ip_address'], 'string', 'max' => 255],
            [['physical_location'], 'string'],
            ['communication_type', 'default', 'value' => self::COMMUNICATION_NOT_AVAILABLE],
            ['communication_type', 'in', 'range' => array_keys(self::getListCommunicationTypes()),
             'skipOnEmpty' => true],
            ['data_usage_method', 'default', 'value' => self::DATA_USAGE_METHOD_IMPORT],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
            [['start_date'], 'safe'],
            ['type', 'default', 'value' => self::TYPE_AIR],
            ['type', 'in', 'range' => array_keys(self::getMeterCategories()), 'skipOnEmpty' => true],
        ];
    }


    public function attributeLabels() {
        return [
            'id' => Yii::t('common.meter', 'ID'),
            'name' => Yii::t('common.meter', 'Meter ID'),
            'breaker_name' => Yii::t('common.meter', 'Breaker name'),
            'type_id' => Yii::t('common.meter', 'Type'),
            'site_id' => Yii::t('common.meter', 'Site'),
            'communication_type' => Yii::t('common.meter', 'Communication type'),
            'physical_location' => Yii::t('common.meter', 'Phisical location on site'),
            'start_date' => Yii::t('common.meter', 'Start date'),
            'data_usage_method' => Yii::t('common.meter', 'Data usage meter'),
            'ip_address' => Yii::t('common.meter', 'IP'),
            'old_id' => Yii::t('common.meter', 'Old ID'),
            'status' => Yii::t('common.meter', 'Status'),
            'created_at' => Yii::t('common.meter', 'Created at'),
            'modified_at' => Yii::t('common.meter', 'Modified at'),
            'created_by' => Yii::t('common.meter', 'Created by'),
            'modified_by' => Yii::t('common.meter', 'Modified by'),
            'type_name' => Yii::t('common.meter', 'Type'),
            'site_name' => Yii::t('common.meter', 'Site'),
            'tenants' => Yii::t('common.meter', 'Tenants'),
            'type' => Yii::t('common.meter', 'Type category'),
        ];
    }


    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'modified_at',
            ],
            [
                'class' => UserIdBehavior::className(),
                'createdByAttribute' => 'created_by',
                'modifiedByAttribute' => 'modified_by',
            ],
            [
                'class' => ToTimestampBehavior::className(),
                'attributes' => [
                    'start_date',
                ],
            ],
        ];
    }


    public function beforeSave($insert) {
        if(parent::beforeSave($insert)) {
            if($this->isAttributeChanged('type_id') && !empty($this->type_id)) {
                $type = MeterType::findOne($this->type_id);
                if($type instanceof MeterType) {
                    $this->type = $type->type;
                }
            }
            return true;
        }
        else return false;
    }


    public function getRelationSite() {
        return $this->hasOne(Site::className(), ['id' => 'site_id']);
    }


    public function getRelationMeterType() {
        return $this->hasOne(MeterType::className(), ['id' => 'type_id']);
    }


    public function getRelationMeterChannels() {
        return $this->hasMany(MeterChannel::className(), ['meter_id' => 'id']);
    }


    public function getRelationMeterSubchannels() {
        return $this->hasMany(MeterSubchannel::className(), ['meter_id' => 'id']);
    }


    public static function getListCommunicationTypes() {
        return [
            self::COMMUNICATION_NOT_AVAILABLE => Yii::t('common.meter', 'Not available'),
            self::COMMUNICATION_TYPE_PLC => Yii::t('common.meter', 'PLC'),
            self::COMMUNICATION_TYPE_485_LINE => Yii::t('common.meter', '485 line'),
            self::COMMUNICATION_TYPE_RF => Yii::t('common.meter', 'RF'),
        ];
    }


    public function getAliasCommunicationType() {
        $list = self::getListCommunicationTypes();
        return (isset($list[$this->communication_type])) ? $list[$this->communication_type] : $this->communication_type;
    }


    public static function getListDataUsageMethods() {
        return [
            self::DATA_USAGE_METHOD_IMPORT => Yii::t('common.meter', 'Import'),
            self::DATA_USAGE_METHOD_IMPORT_PLUS_EXPORT => Yii::t('common.meter', 'Import + Export'),
            self::DATA_USAGE_METHOD_IMPORT_MINUS_EXPORT => Yii::t('common.meter', 'Import - Export'),
            self::DATA_USAGE_METHOD_EXPORT => Yii::t('common.meter', 'Export'),
        ];
    }


    public function getAliasDataUsageMethod() {
        $list = self::getListDataUsageMethods();
        return (isset($list[$this->data_usage_method])) ? $list[$this->data_usage_method] : $this->data_usage_method;
    }


    public static function getListStatuses() {
        return [
            self::STATUS_INACTIVE => Yii::t('common.meter', 'Inactive'),
            self::STATUS_ACTIVE => Yii::t('common.meter', 'Active'),
        ];
    }


    public static function getMeterCategories() {
        return [
            self::TYPE_ELECTRICITY => Yii::t('common.meter', 'Electricity'),
            self::TYPE_AIR => Yii::t('common.meter', 'Air'),
        ];
    }

    public function getAliasCategories() {
        $list = self::getMeterCategories();
        return (isset($list[$this->type])) ? $list[$this->type] : $this->type;
    }


    public function getAliasStatus() {
        $list = self::getListStatuses();
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }


    public function getAliasType() {
        if($this->relationMeterType != null) {
            return Yii::t('common.meter',
                          '{name} - total {n, plural, =0{are no channels} =1{# channel} other{# channels}}', [
                              'name' => $this->relationMeterType->name,
                              'n' => $this->relationMeterType->channels * $this->relationMeterType->phases,
                          ]);
        }
    }

    public static function getAirListMeters($site_id = null) {
        $query = (new Query())->select('t.id, t.name, type.name as type_name, type.channels, type.phases')
            ->from(self::tableName() . ' t')
            ->innerJoin(MeterChannel::tableName() . ' channel', 'channel.meter_id = t.id')
            ->innerJoin(MeterType::tableName() . ' type', 'type.id = t.type_id')
            ->andWhere([
                't.status' => self::STATUS_ACTIVE,
                'channel.status' => MeterChannel::STATUS_ACTIVE,
            ])
            ->andWhere(['t.type' => 'air'])
            ->having('COUNT(channel.id) > 0');
        if($site_id != null) {
            $query->andWhere(['t.site_id' => $site_id]);
        }
        $rows = $query->groupBy(['t.id'])->all();
        return ArrayHelper::map($rows, 'id', function ($row) {
            return $row['name'] . ' - (' . Yii::t('common.meter',
                    '{name} - total {n, plural, =0{are no channels} =1{# channel} other{# channels}}',
                    [
                        'name' => $row['type_name'],
                        'n' => $row['channels'] * $row['phases'],
                    ]) . ')';
        });
    }


    public static function getListMeters($site_id = null) {
        $query = (new Query())->select('t.id, t.name, type.name as type_name, type.channels, type.phases')
                              ->from(self::tableName() . ' t')
                              ->innerJoin(MeterChannel::tableName() . ' channel', 'channel.meter_id = t.id')
                              ->innerJoin(MeterType::tableName() . ' type', 'type.id = t.type_id')
                              ->andWhere([
                                             't.status' => self::STATUS_ACTIVE,
                                             'channel.status' => MeterChannel::STATUS_ACTIVE,
                                         ])->having('COUNT(channel.id) > 0');
        if($site_id != null) {
            $query->andWhere(['t.site_id' => $site_id]);
        }
        $rows = $query->groupBy(['t.id'])->all();
        return ArrayHelper::map($rows, 'id', function ($row) {
            return $row['name'] . ' - (' . Yii::t('common.meter',
                                                  '{name} - total {n, plural, =0{are no channels} =1{# channel} other{# channels}}',
                                                  [
                                                      'name' => $row['type_name'],
                                                      'n' => $row['channels'] * $row['phases'],
                                                  ]) . ')';
        });
    }


    public static function getListMeterTypes() {
        $list = [];
        $rows = (new Query())->from(MeterType::tableName() . ' t')->andWhere(['t.status' => MeterType::STATUS_ACTIVE])
                             ->all();
        if($rows != null) {
            foreach($rows as $value) {
                $list[$value['id']] = Yii::t('common.meter',
                                             '{name} - total {n, plural, =0{are no channels} =1{# channel} other{# channels}}',
                                             [
                                                 'name' => $value['name'],
                                                 'n' => $value['channels'] * $value['phases'],
                                             ]);
            }
        }
        return $list;
    }


    public static function getListMeterChannels($meter_id) {
        $list = [];
        $model = self::findOne($meter_id);
        if($model != null) {
            $model_channels = $model->relationMeterChannels;
            if($model_channels != null) {
                foreach($model_channels as $model_channel) {
                    $list[$model_channel->id] = $model_channel->getChannelName() . ' - ' . $model->name;
                }
            }
        }
        return $list;
    }


    public function getIpAddress() {
        if(($ip_address = $this->ip_address) == null) {
            $ip_address = (new Query())->select(['ip_address'])->from(SiteIpAddress::tableName())->where([
                 'site_id' => $this->site_id,
                 'status' => SiteIpAddress::STATUS_ACTIVE,
            ])->scalar();
        }
        return $ip_address;
    }


    public static function getListTypesByTypeId($type) {
        $list = [];
        $rows = (new Query())->from(MeterType::tableName())->where(['type' => $type])->all();
        if($rows != null) {
            foreach($rows as $value) {
                $list[$value['id']] = Yii::t('common.meter',
                                             '{name} - total {n, plural, =0{are no channels} =1{# channel} other{# channels}}',
                                             [
                                                 'name' => $value['name'],
                                                 'n' => $value['channels'] * $value['phases'],
                                             ]);
            }
        }
        return $list;
    }


    public function getMainChannels() {
        $meter_channels = $this->getRelationMeterChannels()
                               ->with('relationMeterSubchannels')
                               ->all();
        return $meter_channels;
    }
}
