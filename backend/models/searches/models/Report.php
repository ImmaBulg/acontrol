<?php
namespace backend\models\searches\models;

use Yii;

use common\components\i18n\Formatter;

/**
 * Report search model.
 */
class Report extends \common\models\Report
{
	public $site_owner_name;
	public $site_name;
	public $tenant_name;
	public $issued_by;

	public function rules()
	{
		return [
			[['id'], 'safe'],
			[['site_owner_name', 'site_name', 'tenant_name'], 'string'],
			[['is_public', 'is_automatically_generated'], 'boolean'],
			['type', 'in', 'range' => array_keys(self::getListTypes()), 'skipOnEmpty' => true],
			['level', 'in', 'range' => array_keys(self::getListLevels()), 'skipOnEmpty' => true],
			[['from_date', 'to_date', 'created_at'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			[['issued_by'], 'string'],
		];
	}
}
