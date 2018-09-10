<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.09.2018
 * Time: 15:37
 */

namespace common\models;

use common\components\db\ActiveRecord;
use Yii;

class Holiday extends ActiveRecord
{
    public static function tableName() {
        return 'holiday';
    }

    public function rules() {
        return [
            [['date', 'name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'date' => Yii::t('common.view', 'Holiday date'),
            'name' => Yii::t('common.view', 'Holiday name'),
        ];
    }
}