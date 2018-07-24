<?php
namespace backend\models\searches\models;

use Yii;

use common\components\i18n\Formatter;

/**
 * Rate search model.
 */
class Rate extends \common\models\Rate
{
	public function rules()
	{
		return [
			[['id', 'rate_type_id'], 'integer'],
			[['fixed_payment'], 'number'],
			['season', 'in', 'range' => array_keys(self::getListSeasons())],
			[['start_date', 'end_date'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
		];
	}
}
