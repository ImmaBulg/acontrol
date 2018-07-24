<?php
namespace backend\models\searches\models;

use Yii;

/**
 * SiteContact search model.
 */
class SiteContact extends \common\models\SiteContact
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
