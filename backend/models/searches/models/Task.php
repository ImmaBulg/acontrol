<?php
namespace backend\models\searches\models;

use Yii;

use common\components\i18n\Formatter;

/**
 * Task search model.
 */
class Task extends \common\models\Task
{
	public $user_name;
	public $user_role;
	public $site_name;
	public $site_contact_name;
	public $meter_name;
	public $channel_name;
	public $date_timestamp;

	public function rules()
	{
		return [
			[['id', 'date_timestamp', 'user_name'], 'integer'],
			[['description', 'site_name', 'site_contact_name', 'meter_name', 'channel_name', 'ip_address'], 'string'],
			[['date'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['user_role', 'in', 'range' => array_keys(self::getListRoles())],
			['type', 'in', 'range' => array_keys(self::getListTypes())],
			['color', 'in', 'range' => array_keys(self::getListColors())],
			['urgency', 'in', 'range' => array_keys(self::getListUrgencies())],
			['status', 'in', 'range' => array_keys(self::getListStatuses())],
		];
	}
}
