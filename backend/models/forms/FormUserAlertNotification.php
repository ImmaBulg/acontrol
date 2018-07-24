<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\models\Site;
use common\models\Tenant;
use common\models\UserAlertNotification;
use common\models\events\logs\EventLogUserAlertNotification;

/**
 * FormUserAlertNotification is the class for user alert notification create/edit.
 */
class FormUserAlertNotification extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;
	private $_user_id;

	public $site_owner_id;
	public $site_id;

	public function rules()
	{
		return [
			[['site_owner_id', 'site_id'], 'required'],
			[['site_owner_id', 'site_id'], 'integer'],
			['site_owner_id', 'in', 'range' => array_keys(Site::getListUsers()), 'skipOnEmpty' => false],
			['site_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Site', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['status' => Site::STATUS_ACTIVE]);
			}],
			// On scenario create
			['site_id', 'unique', 'targetClass' => '\common\models\UserAlertNotification', 'filter' => function($model){
				return $model->andWhere(['user_id' => $this->_user_id])
				->andWhere(['in', 'status', [
					UserAlertNotification::STATUS_INACTIVE,
					UserAlertNotification::STATUS_ACTIVE,
				]]);
			}, 'on' => self::SCENARIO_CREATE, 'message' => Yii::t('backend.user', '{attribute} has already been taken.')],

			// On scenario edit
			['site_id', 'unique', 'targetClass' => '\common\models\UserAlertNotification', 'filter' => function($model){
				return $model->andWhere(['user_id' => $this->_user_id])
				->andWhere('id != :id', ['id' => $this->_id])
				->andWhere(['in', 'status', [
					UserAlertNotification::STATUS_INACTIVE,
					UserAlertNotification::STATUS_ACTIVE,
				]]);
			}, 'on' => self::SCENARIO_EDIT, 'message' => Yii::t('backend.user', '{attribute} has already been taken.')],
		];
	}

	public function attributeLabels()
	{
		return [
			'site_owner_id' => Yii::t('backend.user', 'Client'),
			'site_id' => Yii::t('backend.user', 'Site'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_CREATE:
				$this->_user_id = $model->id;
				break;

			case self::SCENARIO_EDIT:
				$this->_id = $model->id;
				$this->_user_id = $model->user_id;

				$model->site_owner_id = $this->site_owner_id;
				$model->site_id = $this->site_id;				
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new UserAlertNotification();
		$model->user_id = $this->_user_id;
		$model->site_owner_id = $this->site_owner_id;
		$model->site_id = $this->site_id;

		$event = new EventLogUserAlertNotification();
		$event->model = $model;
		$model->on(EventLogUserAlertNotification::EVENT_AFTER_INSERT, [$event, EventLogUserAlertNotification::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = UserAlertNotification::findOne($this->_id);
		$model->site_owner_id = $this->site_owner_id;
		$model->site_id = $this->site_id;

		$event = new EventLogUserAlertNotification();
		$event->model = $model;
		$model->on(EventLogUserAlertNotification::EVENT_BEFORE_UPDATE, [$event, EventLogUserAlertNotification::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
