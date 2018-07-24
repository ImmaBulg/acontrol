<?php
namespace backend\models\searches\models;

use Yii;
use common\components\i18n\Formatter;

/**
 * MeterChannelMultiplier search model.
 */
class MeterChannelMultiplier extends \common\models\MeterChannelMultiplier
{
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['current_multiplier', 'voltage_multiplier'], 'number'],
			[['start_date', 'end_date'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
		];
	}
}
