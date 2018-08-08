<?php

namespace common\models;

use Carbon\Carbon;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use common\components\db\ActiveRecord;
use common\components\behaviors\UserIdBehavior;
use common\components\behaviors\ToTimestampBehavior;
use yii\helpers\VarDumper;

/**
 * Rate is the class for the table "rate".
 * @property $start_date
 * @property $id
 * @property $end_date
 * @property $rate_type_id
 * @property RateType $relationRateType
 */
class Rate extends ActiveRecord
{
    const IDENTIFIER_VALIDATION_PATTERN = '/^[a-zA-Z0-9]+$/';

    const SEASON_WINTER = 1;
    const SEASON_SPRING = 2;
    const SEASON_SUMMER = 3;
    const SEASON_FALL = 4;

    const CONSUMPTION_SHEFEL = 'shefel';
    const CONSUMPTION_GEVA = 'geva';
    const CONSUMPTION_PISGA = 'pisga';

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    const BYTE = 1;
    const KAVUAA = 2;
    const REHOV = 3;
    const NAMUCH = 4;
    const GAVOGA = 5;
    const ELYON = 6;
    const MEMUTZA = 7;
    const NAYAD = 8;
    const NAMUCH_KLALI = 9;
    const NAMUCH_BYTE = 10;
    const MAOR = 11;
    const INHERIT = 12;
    const HOME = 15;
    const GENERAL = 16;


    public static function tableName() {
        return 'rate';
    }


    public function rules() {
        return [
            [['identifier'], 'filter', 'filter' => 'trim'],
            [['identifier'], 'string', 'max' => 255],
            [['rate_type_id'], 'integer'],
            ['identifier', 'match', 'pattern' => self::IDENTIFIER_VALIDATION_PATTERN],
            [['fixed_payment', 'rate', 'shefel', 'geva', 'pisga'], 'number', 'min' => 0],
            ['season', 'in', 'range' => array_keys(self::getListSeasons()), 'skipOnEmpty' => true],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
        ];
    }


    public function attributeLabels() {
        return [
            'id' => Yii::t('common.rate', 'ID'),
            'identifier' => Yii::t('common.rate', 'Note'),
            'season' => Yii::t('common.rate', 'Season'),
            'fixed_payment' => Yii::t('common.rate', 'Fixed payment for monthly billed clients'),
            'rate' => Yii::t('common.rate', 'Rate in Agorot'),
            'shefel' => Yii::t('common.rate', 'Shefel'),
            'geva' => Yii::t('common.rate', 'Geva'),
            'pisga' => Yii::t('common.rate', 'Pisga'),
            'start_date' => Yii::t('common.rate', 'Start date'),
            'end_date' => Yii::t('common.rate', 'End date'),
            'rate_type_id' => Yii::t('common.rate', 'Type'),
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
            [
                'class' => ToTimestampBehavior::className(),
                'attributes' => [
                    'start_date',
                    'end_date',
                ],
            ],
        ];
    }


    public function getRelationRateType() {
        return $this->hasOne(RateType::className(), ['id' => 'rate_type_id']);
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


    public static function getListRateTypes($criteria = []) {
        $query = AirRates::find()->andWhere(['in', 'status', [
            RateType::STATUS_ACTIVE,
        ]])->andWhere(['<>', 'is_taoz', 1]);
        $rows = $query->all();
        return ArrayHelper::map($rows, 'id', function ($model) {
            return $model->rate_name;
        });
    }

    public static function getListRate($criteria = []) {
        $query = RateName::find()->andWhere($criteria);
        $rows = $query->all();
        return ArrayHelper::map($rows, 'id', function ($model) {
            return $model->name;
        });
    }


    public function getAliasRateType() {
        if(($rate_type = $this->relationRateType) != null) {
            return $rate_type->name;
        }
    }


    public static function getListRateBaseTypeAssociations() {
        $rows = (new Query())
            ->select(['t.id', 't.type'])
            ->from(RateType::tableName() . ' t')
            ->andWhere(['in', 't.status', [
                RateType::STATUS_ACTIVE,
            ]])
            ->all();
        return ArrayHelper::map($rows, 'id', 'type');
    }


    public static function getAliasRateBaseTypeAssociation($value) {
        $list = self::getListRateBaseTypeAssociations();
        return (isset($list[$value])) ? $list[$value] : null;
    }


    public static function getListSeasons() {
        return [
            self::SEASON_WINTER => Yii::t('common.rate', 'Winter'),
            self::SEASON_SPRING => Yii::t('common.rate', 'Spring'),
            self::SEASON_SUMMER => Yii::t('common.rate', 'Summer'),
            self::SEASON_FALL => Yii::t('common.rate', 'Fall'),
        ];
    }


    public function getAliasSeason() {
        $list = self::getListSeasons();
        return (isset($list[$this->season])) ? $list[$this->season] : $this->season;
    }


    public function getIdentifier($consumption = null) {
        if(($rate_type = $this->relationRateType) != null) {
            switch($rate_type->type) {
                case RateType::TYPE_TAOZ:
                    switch($consumption) {
                        case self::CONSUMPTION_SHEFEL:
                            return $this->shefel_identifier;
                            break;
                        case self::CONSUMPTION_GEVA:
                            return $this->geva_identifier;
                            break;
                        case self::CONSUMPTION_PISGA:
                            return $this->pisga_identifier;
                            break;
                        default:
                            break;
                    }
                    break;
                case RateType::TYPE_FIXED:
                default:
                    return $this->identifier;
                    break;
            }
        }
    }


    /**
     * @param $start_date
     * @param $end_date
     * @param $rate_type_id
     * @return array|\yii\db\ActiveRecord[]|Rate[]
     */
    public static function getActiveWithinRangeByTypeId($start_date, $end_date, $rate_type_id) {
        $start_date = date_timestamp_get($start_date);
        $end_date = date_timestamp_get($end_date);
        $rates = Rate::find()
            ->andwhere([
                'and',
                ['rate_type_id' => $rate_type_id],
                ['status' => Rate::STATUS_ACTIVE],
            ])->andWhere('start_date < :to_date AND end_date > :from_date', [
                'to_date' => $end_date,
                'from_date' => $start_date,
            ])->orderBy(['start_date' => SORT_ASC])->all();
        return $rates;
    }
}
