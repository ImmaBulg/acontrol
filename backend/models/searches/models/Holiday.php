<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.09.2018
 * Time: 15:51
 */

namespace backend\models\searches\models;

use Yii;

class Holiday extends \common\models\Holiday
{
    public function rules()
    {
        return [
            [['name'], 'string'],
            ['date', 'date'],
        ];
    }
}