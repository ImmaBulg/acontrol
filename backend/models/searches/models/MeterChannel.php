<?php
namespace backend\models\searches\models;

use Yii;

/**
 * MeterChannel search model.
 */
class MeterChannel extends \common\models\MeterChannel
{
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['old_id'], 'string'],
			[['channel'], 'integer'],
			[['current_multiplier', 'voltage_multiplier'], 'number'],
		];
	}
}
