<?php
namespace backend\models\searches\models;

use Yii;

use common\components\i18n\Formatter;

/**
 * RateType search model.
 */
class RateType extends \common\models\RateType
{
	public $name;

	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['name'], 'string'],
			['type', 'in', 'range' => array_keys(self::getListTypes())],
			['level', 'in', 'range' => array_keys(self::getListLevels())],
			['status', 'in', 'range' => array_keys(self::getListStatuses())],
		];
	}
}
