<?php
namespace backend\models\searches\models;

use Yii;

use common\components\i18n\Formatter;

/**
 * TenantGroup search model.
 */
class TenantGroup extends \common\models\TenantGroup
{
	public $user_name;
	public $site_name;
	public $group_tenants;

	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['name', 'user_name', 'site_name'], 'string'],
			[['created_at'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			[['group_tenants'], 'integer'],
		];
	}
}
