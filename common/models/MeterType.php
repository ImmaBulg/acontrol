<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;

/**
 * MeterType is the class for the table "meter_type".
 * @property $is_divide_by_1000
 * @property $type
 * @property $status
 */
class MeterType extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    const TYPE_ELECTRICITY = 'electricity';
    const TYPE_AIR = 'air';

	public static function tableName()
	{
		return 'meter_type';
	}


    public function rules() {
        return [
            [['name'], 'filter', 'filter' => 'strip_tags'],
            [['name'], 'filter', 'filter' => 'trim'],
            [['name', 'channels', 'phases'], 'required'],
            [['name'], 'string', 'max' => 255],
            ['channels', 'default', 'value' => 1],
            ['phases', 'default', 'value' => 1],
            [['channels', 'serie_number'], 'integer', 'min' => 1],
            [['modbus'], 'number'],
            [['is_divide_by_1000'], 'safe'],
            ['name', 'unique', 'filter' => function ($model) {
                return $model->where('name = :name COLLATE utf8_bin', ['name' => $this->name])
                             ->andWhere([
                                            'channels' => $this->channels,
                                            'phases' => $this->phases,
                                        ])->andWhere(['in', 'status', [
                        self::STATUS_INACTIVE,
                        self::STATUS_ACTIVE,
                    ]]);
            }, 'message' => Yii::t('common.meter', 'Meter type has already been taken.')],
            ['phases', 'in', 'range' => array_keys(self::getListPhases()), 'skipOnEmpty' => false],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
            [['type'], 'string'],
        ];
    }


    public function attributeLabels() {
        return [
            'id' => Yii::t('common.meter', 'ID'),
            'name' => Yii::t('common.meter', 'Name'),
            'channels' => Yii::t('common.meter', 'Channels'),
            'phases' => Yii::t('common.meter', 'Phases'),
            'serie_number' => Yii::t('common.meter', 'Serie Number'),
            'modbus' => Yii::t('common.meter', 'MODBUS'),
            'old_id' => Yii::t('common.meter', 'Old ID'),
            'status' => Yii::t('common.meter', 'Status'),
            'created_at' => Yii::t('common.meter', 'Created at'),
            'modified_at' => Yii::t('common.meter', 'Modified at'),
            'created_by' => Yii::t('common.meter', 'Created by'),
            'modified_by' => Yii::t('common.meter', 'Modified by'),
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
        ];
    }


    public static function getListPhases() {
        return [
            1 => 1,
            3 => 3,
        ];
    }


    public static function getListStatuses() {
        return [
            self::STATUS_INACTIVE => Yii::t('common.meter', 'Inactive'),
            self::STATUS_ACTIVE => Yii::t('common.meter', 'Active'),
        ];
    }

    public static function getMeterCategories() {
        return [
            self::TYPE_ELECTRICITY => 'Electricity',
            self::TYPE_AIR => 'Air'
        ];
    }

    public function getType() {
	    if(isset(self::getMeterCategories()[$this->type])) {
	        return self::getMeterCategories()[$this->type];
        }
        return false;
    }


    public function getAliasStatus() {
        $list = self::getListStatuses();
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }
}
