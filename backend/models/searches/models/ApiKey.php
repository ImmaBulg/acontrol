<?php
namespace backend\models\searches\models;

use Yii;


/**
 * ApiKey search model.
 */
class ApiKey extends \common\models\ApiKey
{
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['api_key'], 'string'],
			['status', 'in', 'range' => array_keys(self::getListStatuses()), 'skipOnEmpty' => true],
		];
	}
}
