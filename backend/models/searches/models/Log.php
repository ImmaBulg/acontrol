<?php
namespace backend\models\searches\models;

use Yii;

use common\components\i18n\Formatter;

/**
 * Log search model.
 */
class Log extends \common\models\Log
{
	public $user_name;

	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['action', 'ip_address', 'user_name'], 'string'],
			['type', 'in', 'range' => array_keys(self::getListTypes())],
			[['created_at'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
		];
	}
}
