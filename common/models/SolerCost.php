<?php
namespace common\models;

use DateTime;
use dezmont765\yii2bundle\models\MainActiveRecord;
use Yii;

/**
 * This is the model class for table "soler_cost".
 *
 * @property integer $id
 * @property string $from_date
 * @property string $to_date
 * @property double $cost
 */
class SolerCost extends MainActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'soler_cost';
    }


    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['from_date', 'to_date','fromDate','toDate'], 'safe'],
            [['cost'], 'number']
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'from_date' => 'From Date',
            'to_date' => 'To Date',
            'cost' => 'Cost',
        ];
    }

    public function getFromDate() {
        if(empty($this->from_date)) {
            return null;
        }
        return DateTime::createFromFormat('Y-m-d', $this->from_date)->format('m/d/Y');
    }


    public function setFromDate($date) {
        if(!empty($date)) {
            $this->from_date = \DateTime::createFromFormat('m/d/Y', $date)->format('Y-m-d');
        }
    }

    public function getToDate() {
        if(empty($this->to_date)) {
            return null;
        }
        return DateTime::createFromFormat('Y-m-d', $this->to_date)->format('m/d/Y');
    }


    public function setToDate($date) {
        if(!empty($date)) {
            $this->to_date = \DateTime::createFromFormat('m/d/Y', $date)->format('Y-m-d');
        }
    }

}
