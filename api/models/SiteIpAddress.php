<?php
namespace api\models;

use Yii;

/**
 * SiteIpAddress is the class for the table "site_ip_address".
 */
class SiteIpAddress extends \common\models\SiteIpAddress
{
	public function fields()
	{
		return [
			'id',
			'site_id',
			'ip_address',
			'is_main',
		];
	}
}
