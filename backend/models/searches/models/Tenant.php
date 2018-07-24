<?php
namespace backend\models\searches\models;

use Yii;

use common\models\Site;
use common\models\Rate;
use common\components\i18n\Formatter;

/**
 * Tenant search model.
 */
class Tenant extends \common\models\Tenant
{
	public $site_name;
	public $tenant_name;
	public $rate_type_id;
	public $fixed_payment;

	public function rules()
	{
		return [
			[['id', 'rate_type_id'], 'integer'],
			[['site_name', 'tenant_name', 'old_id', 'old_channel_id'], 'string'],
			[['square_meters'], 'integer'],
			[['entrance_date', 'exit_date'], 'date', 'format' => Formatter::PHP_DATE_FORMAT],
			['to_issue', 'in', 'range' => array_keys(Site::getListToIssues()), 'skipOnEmpty' => true],
			[['fixed_payment'], 'number'],
		];
	}
}
