<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\models\UserContact;
use common\components\rbac\Role;
use common\models\events\logs\EventLogUserContact;

/**
 * FormUserContact is the class for user contact create/edit.
 */
class FormUserContact extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;
	private $_user_id;

	public $name;
	public $email;
	public $address;
	public $phone;
	public $cell_phone;
	public $fax;
	public $job;
	public $comment;

	public function rules()
	{
		return [
			[['name', 'address', 'job', 'comment'], 'filter', 'filter' => 'strip_tags'],
			[['name', 'email', 'address', 'job', 'comment'], 'filter', 'filter' => 'trim'],
			[['name'], 'required'],
			['email', 'email'],
			[['phone', 'cell_phone'], 'match', 'pattern' => UserContact::PHONE_VALIDATION_PATTERN],
			['fax', 'match', 'pattern' => UserContact::FAX_VALIDATION_PATTERN],
			[['job', 'phone', 'fax'], 'string', 'max' => 255],
			[['address', 'comment'], 'string'],
		];
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('backend.user', 'Name'),
			'email' => Yii::t('backend.user', 'Email'),
			'address' => Yii::t('backend.user', 'Address'),
			'job' => Yii::t('backend.user', 'Job'),
			'phone' => Yii::t('backend.user', 'Phone'),
			'cell_phone' => Yii::t('backend.user', 'Cell phone'),
			'fax' => Yii::t('backend.user', 'Fax'),
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

				$this->name = $model->name;
				$this->email = $model->email;
				$this->address = $model->address;
				$this->job = $model->job;
				$this->phone = $model->phone;
				$this->cell_phone = $model->cell_phone;
				$this->fax = $model->fax;
				$this->comment = $model->comment;
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$model = new UserContact();
		$model->user_id = $this->_user_id;
		$model->name = $this->name;
		$model->email = $this->email;
		$model->address = $this->address;
		$model->job = $this->job;
		$model->phone = $this->phone;
		$model->cell_phone = $this->cell_phone;
		$model->fax = $this->fax;
		$model->comment = $this->comment;

		$event = new EventLogUserContact();
		$event->model = $model;
		$model->on(EventLogUserContact::EVENT_AFTER_INSERT, [$event, EventLogUserContact::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = UserContact::findOne($this->_id);
		$model->name = $this->name;
		$model->email = $this->email;
		$model->address = $this->address;
		$model->job = $this->job;
		$model->phone = $this->phone;
		$model->cell_phone = $this->cell_phone;
		$model->fax = $this->fax;
		$model->comment = $this->comment;

		$event = new EventLogUserContact();
		$event->model = $model;
		$model->on(EventLogUserContact::EVENT_BEFORE_UPDATE, [$event, EventLogUserContact::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
