<?php
namespace common\components\behaviors;

use Yii;
use yii\db\BaseActiveRecord;
use yii\behaviors\AttributeBehavior;

class UserIdBehavior extends AttributeBehavior
{
	public $createdByAttribute = 'created_by';
	public $modifiedByAttribute = 'modified_by';

	public function events()
	{
		return [
			BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
			BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
		];
	}

	public function beforeSave()
	{
		$user = Yii::$app->get('user', false);

		if ($user) {
			$model = $this->owner;
			$createdByAttribute = $this->createdByAttribute;
			$modifiedByAttribute = $this->modifiedByAttribute;

			if ($createdByAttribute != null && $model->$createdByAttribute == null) {
				$model->$createdByAttribute = $user->id;
			}

			if ($modifiedByAttribute != null) {
				$model->$modifiedByAttribute = $user->id;
			}
		}
	}
}