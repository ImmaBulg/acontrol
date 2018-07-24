<?php

namespace common\models;

use backend\models\searches\models\Rate;
use Carbon\Carbon;
use common\components\behaviors\ToTimestampBehavior;
use common\constants\DataCategories;
use DateTime;
use dezmont765\yii2bundle\models\MainActiveRecord;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "air_rates".
 *
 * @property integer $id
 * @property integer $rate_type_id
 * @property integer $season
 * @property string $start_date
 * @property string $end_date
 * @property integer $status
 * @property string $create_at
 * @property string $modified_at
 * @property integer $created_by
 * @property integer $modified_by
 *
 * @property SubAirRates[] $subAirRates
 * @property SubAirRatesTaoz[] subAirRatesTaoz
 * @property User $createdBy
 * @property User $modifiedBy
 * @property RateType $rateType
 */
class AirRates extends MainActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'air_rates';
    }


    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['rate_type_id', 'startDate', 'endDate', 'start_date', 'end_date', 'fixed_payment'], 'required'],
            [['rate_type_id', 'season', 'status', 'created_by', 'modified_by'], 'integer'],
            [['start_date', 'end_date', 'create_at', 'modified_at'], 'safe'],
            ['status', 'default', 'value' => Rate::STATUS_ACTIVE],
        ];
    }


    public function getSubAirRatesTaoz() {
        $query = SubAirRatesTaoz::find();
        $query->multiple = true;
        $query->joinWith('subAirRate')->where([SubAirRates::tableName() .
                                               '.rate_id' => $this->id]);
        return $query;
    }


    public function getGevaPrice() {
        return $this->getSubAirRatesTaoz()
                    ->andWhere([SubAirRatesTaoz::tableName() . '.type' => DataCategories::GEVA])->select('rate')
                    ->scalar();
    }


    public function getShefelPrice() {
        return $this->getSubAirRatesTaoz()
                    ->andWhere([SubAirRatesTaoz::tableName() . '.type' => DataCategories::SHEFEL])->select('rate')
                    ->scalar();
    }


    public function getPisgaPrice() {
        return $this->getSubAirRatesTaoz()
                    ->andWhere([SubAirRatesTaoz::tableName() . '.type' => DataCategories::PISGA])->select('rate')
                    ->scalar();
    }

//    public function behaviors() {
//            return [
//                [
//                    'class' => ToTimestampBehavior::className(),
//                    'attributes' => [
//                        'start_date',
//                        'end_date',
//                    ],
//                ],
//            ];
//    }
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'rate_type_id' => 'Rate Type ID',
            'season' => 'Season',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'status' => 'Status',
            'create_at' => 'Create At',
            'modified_at' => 'Modified At',
            'created_by' => 'Created By',
            'modified_by' => 'Modified By',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy() {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModifiedBy() {
        return $this->hasOne(User::className(), ['id' => 'modified_by']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRateType() {
        return $this->hasOne(RateType::className(), ['id' => 'rate_type_id']);
    }


    public function getStartDate() {
        if(empty($this->start_date)) {
            return null;
        }
        return DateTime::createFromFormat('Y-m-d', $this->start_date)->format('d-m-Y');
    }


    public function setStartDate($date) {
        if(!empty($date)) {
            $this->start_date = \DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }
    }


    public function getEndDate() {
        if(empty($this->end_date)) {
            return null;
        }
        return DateTime::createFromFormat('Y-m-d', $this->end_date)->format('d-m-Y');
    }


    public function setEndDate($date) {
        if(!empty($date)) {
            $this->end_date = \DateTime::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }
    }


    private $rate_type_name = null;


    public function getRateTypeName() {
        if($this->rate_type_name == null) {
            if($this->rateType instanceof RateType) {
                $this->rate_type_name = $this->rateType->getName();
            }
        }
        return $this->rate_type_name;
    }


    private $season_name = null;


    public function getSeasonName() {
        if($this->season_name == null) {
            $this->season_name = $this->getSeason();
        }
        return $this->season_name;
    }


    public function getSeason() {
        if(isset(Rate::getListSeasons()[$this->season])) {
            return Rate::getListSeasons()[$this->season];
        }
        else return null;
    }


    public function getSubAirRates() {
        return $this->hasMany(SubAirRates::class, ['rate_id' => 'id']);
    }


    /**
     * @param $start_date
     * @param $end_date
     * @param $rate_type_id
     * @return ActiveQuery
     */
    public static function getActiveWithinRangeByTypeId(Carbon $start_date, Carbon $end_date, $rate_type_id) {
        $rates_query = self::find()
                           ->andwhere([
                                          'and',
                                          ['rate_type_id' => $rate_type_id],
                                          ['status' => Rate::STATUS_ACTIVE],
                                      ])->andWhere(['AND', ['<', 'start_date', $end_date->format('Y-m-d')],
                                                    ['>', 'end_date', $start_date->format('Y-m-d')]])
                           ->orderBy(['start_date' => SORT_ASC]);
        return $rates_query;
    }
}
