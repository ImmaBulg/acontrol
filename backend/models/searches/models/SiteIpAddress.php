<?php
namespace backend\models\searches\models;

use Yii;

/**
 * SiteIpAddress search model.
 */
class SiteIpAddress extends \common\models\SiteIpAddress
{
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['ip_address'], 'string'],
			[['is_main'], 'boolean'],
			['status', 'in', 'range' => array_keys(self::getListStatuses())],
		];
	}
}
