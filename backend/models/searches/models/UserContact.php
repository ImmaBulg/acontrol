<?php
namespace backend\models\searches\models;

use Yii;

/**
 * UserContact search model.
 */
class UserContact extends \common\models\UserContact
{
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['name', 'email', 'job', 'phone', 'cell_phone', 'fax', 'old_id'], 'string'],
		];
	}
}
