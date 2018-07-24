<?php
namespace frontend\models\searches\models;

use Yii;

use common\components\rbac\Role;
use common\components\i18n\Formatter;

/**
 * User search model.
 */
class User extends \common\models\User
{
	public $phone;
	public $fax;
	public $job;
	public $sites;

	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['name', 'nickname', 'email', 'phone', 'fax', 'job', 'old_id', 'sites'], 'string'],
			[['created_at'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['status', 'in', 'range' => array_keys(self::getListStatuses())],
			['role', 'in', 'range' => array_keys(Role::getListRoles())],
		];
	}
}
