<?php
namespace backend\models\searches\models;

use Yii;

use common\components\i18n\Formatter;

/**
 * Vat search model.
 */
class Vat extends \common\models\Vat
{
	public $modificator_name;

	public function rules()
	{
		return [
			[['modificator_name'], 'string'],
			[['id'], 'integer'],
			[['vat'], 'number'],
			[['start_date', 'end_date'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
		];
	}
}
