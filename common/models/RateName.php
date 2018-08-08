<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 03.08.2018
 * Time: 9:00
 */

namespace common\models;

use common\components\db\ActiveRecord;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * RateName is class for table rate_name
 * @property $id
 * @property $name
 * @property $count
 **/

class RateName extends ActiveRecord
{
    const LOW = 11;
    const HIGH = 12;
    const HOME = 13;
    const GENERAL = 14;

    public static function tableName() {
        return 'rate_name';
    }

    public function rules() {
        return [
            ['name', 'string', 'required'],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => Yii::t('common.rate', 'ID'),
            'name' => Yii::t('common.rate', 'Rate name'),
            'count' => Yii::t('common.rate', 'Air rate count'),
        ];
    }

    public function getAliasAirRates() {
        $query = (new Query())
            ->select('*')
            ->from(AirRates::tableName())
            ->where([
                'rate_name' => $this->name,
            ]);
        return $query->all();
    }

    public static function getListName() {
        $query =  self::find()->andWhere(['<>', 'count', 0]);
        $rows = $query->all();

        return ArrayHelper::map($rows, 'id', function($model) {
            return $model->name;
        });
    }

}