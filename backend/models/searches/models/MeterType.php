<?php
namespace backend\models\searches\models;

use Yii;

/**
 * MeterType search model.
 */
class MeterType extends \common\models\MeterType
{
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['name', 'old_id'], 'string'],
			[['channels'], 'integer'],
			[['modbus'], 'number'],
			['phases', 'in', 'range' => array_keys(self::getListPhases())],
            ['type','safe']
		];
	}
}
