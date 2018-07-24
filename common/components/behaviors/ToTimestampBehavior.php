<?php
namespace common\components\behaviors;

use common\components\i18n\Formatter;
use Yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use yii\db\Query;

class ToTimestampBehavior extends Behavior
{
    public $attributes = [];
    public $viaSQL = false;


    public function events() {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }


    public function beforeSave() {
        $attributes = $this->attributes;
        if($attributes != null) {
            $model = $this->owner;
            foreach($attributes as $attribute) {
                if($model->$attribute != null) {
                    if(!$this->viaSQL) {
                        $model->$attribute = Yii::$app->formatter->asTimestamp($model->$attribute);
                    }
                    else {
                        $sql_date_time_format = Formatter::SQL_DATE_TIME_FORMAT;
                        $model->$attribute =
                            (new Query())->select(["UNIX_TIMESTAMP(STR_TO_DATE(\"{$model->$attribute}\", \"$sql_date_time_format\"))"])
                                         ->scalar();
                    }
                }
            }
        }
    }
}