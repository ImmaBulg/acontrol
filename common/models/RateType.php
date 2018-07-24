<?php

namespace common\models;

use common\components\behaviors\UserIdBehavior;
use common\components\db\ActiveRecord;
use common\components\i18n\LanguageSelector;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * RateType is the class for the table "rate_type".
 * @property $name
 * @property $type
 * @property $id
 */
class RateType extends ActiveRecord
{
    const TYPE_FIXED = 1;
    const TYPE_TAOZ = 2;
    const TYPE_FLAT = 3;
    const TYPE_PERCENT = 3;


    const LEVEL_LOW = 1;
    const LEVEL_HIGH = 2;
    const LEVEL_SUPREME = 3;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;


    public static function tableName() {
        return 'rate_type';
    }


    public function rules() {
        return [
            [['name_en', 'name_he'], 'filter', 'filter' => 'trim'],
            [['name_en', 'name_he'], 'string', 'max' => 255],
            ['type', 'default', 'value' => self::TYPE_FIXED],
            ['type', 'in', 'range' => array_keys(self::getListTypes()), 'skipOnEmpty' => true],
            ['level', 'in', 'range' => array_keys(self::getListLevels()), 'skipOnEmpty' => true],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
        ];
    }


    public function attributeLabels() {
        return [
            'id' => Yii::t('common.rate', 'ID'),
            'name_en' => Yii::t('common.rate', 'Name'),
            'name_he' => Yii::t('common.rate', 'Name'),
            'type' => Yii::t('common.rate', 'Type'),
            'level' => Yii::t('common.rate', 'Rate to use for Power factor range'),
            'status' => Yii::t('common.rate', 'Status'),
            'created_at' => Yii::t('common.rate', 'Created at'),
            'modified_at' => Yii::t('common.rate', 'Modified at'),
            'created_by' => Yii::t('common.rate', 'Created by'),
            'modified_by' => Yii::t('common.rate', 'Modified by'),
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


    public function getRelationRates() {
        return $this->hasMany(Rate::className(), ['rate_type_id' => 'id']);
    }


    public function getRelationSiteBillingSettings() {
        return $this->hasMany(SiteBillingSetting::className(), ['rate_type_id' => 'id']);
    }


    public function getRelationTenantBillingSettings() {
        return $this->hasMany(TenantBillingSetting::className(), ['rate_type_id' => 'id']);
    }


    public static function getListStatuses() {
        return [
            self::STATUS_INACTIVE => Yii::t('common.rate', 'Inactive'),
            self::STATUS_ACTIVE => Yii::t('common.rate', 'Active'),
        ];
    }


    public function getAliasStatus() {
        $list = self::getListStatuses();
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }


    public static function getListTypes() {
        return [
            self::TYPE_FIXED => Yii::t('common.rate', 'Fixed'),
            self::TYPE_TAOZ => Yii::t('common.rate', 'TAOZ'),
        ];
    }


    public function getAliasType() {
        $list = self::getListTypes();
        return (isset($list[$this->type])) ? $list[$this->type] : $this->type;
    }


    public static function getListLevels() {
        return [
            self::LEVEL_LOW => Yii::t('common.rate', 'Low'),
            self::LEVEL_HIGH => Yii::t('common.rate', 'High'),
            self::LEVEL_SUPREME => Yii::t('common.rate', 'Supreme'),
        ];
    }


    public function getAliasLevel() {
        $list = self::getListLevels();
        return (isset($list[$this->level])) ? $list[$this->level] : $this->level;
    }


    public function getName() {
        switch(Yii::$app->language) {
            case LanguageSelector::LANGUAGE_EN:
                return ($this->name_en) ? $this->name_en : $this->name_he;
            case LanguageSelector::LANGUAGE_HE:
            default:
                return ($this->name_he) ? $this->name_he : $this->name_en;
        }
    }


    public static function getHighRateTypeId() {
        if(($value = Yii::$app->cache->get('high_rate_type_id')) != null) {
            return $value;
        }
        else {
            return 5;
        }
    }


    public static function setHighRateTypeId($language) {
        return Yii::$app->cache->set('high_rate_type_id', $language, 0);
    }
}
