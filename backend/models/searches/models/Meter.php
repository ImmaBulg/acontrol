<?php
namespace backend\models\searches\models;

use Yii;

use common\components\i18n\Formatter;

/**
 * Meter search model.
 */
class Meter extends \common\models\Meter
{
	public $type_name;
	public $site_name;

	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['name', 'type_name', 'site_name', 'old_id', 'type'], 'string'],
			['communication_type', 'in', 'range' => array_keys(self::getListCommunicationTypes())],
			[['start_date'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['status', 'in', 'range' => array_keys(self::getListStatuses())],
            ['type', 'in', 'range' => array_keys(self::getMeterCategories())],
		];
	}
}
