<?php
namespace backend\models\searches\models;

use Yii;

/**
 * TenantContact search model.
 */
class TenantContact extends \common\models\TenantContact
{
	public $user_name;
	
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['name', 'email', 'job', 'phone', 'cell_phone', 'fax', 'old_id', 'user_name'], 'string'],
		];
	}
}
