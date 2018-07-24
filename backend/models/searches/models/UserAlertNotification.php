<?php
namespace backend\models\searches\models;

use Yii;

/**
 * UserAlertNotification search model.
 */
class UserAlertNotification extends \common\models\UserAlertNotification
{
	public $site_owner_name;
	public $site_name;
	
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['site_owner_name', 'site_name'], 'string'],
		];
	}
}
