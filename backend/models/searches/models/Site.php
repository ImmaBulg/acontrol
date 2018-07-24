<?php
namespace backend\models\searches\models;

use Yii;

use common\models\Rate;
use common\models\SiteBillingSetting;
use common\components\i18n\Formatter;

/**
 * Site search model.
 */
class Site extends \common\models\Site
{
	public $user_name;
	public $site_name;
	public $rate_type_id;
	public $fixed_payment;
	public $square_meters;

	public function rules()
	{
		return [
			[['id', 'rate_type_id'], 'integer'],
			[['user_name', 'site_name', 'electric_company_id', 'old_id'], 'string'],
			[['square_meters'], 'integer'],
			['to_issue', 'in', 'range' => array_keys(self::getListToIssues()), 'skipOnEmpty' => true],
			[['fixed_payment'], 'number'],
		];
	}
}
