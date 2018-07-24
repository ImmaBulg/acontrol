<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\TenantContact;
use common\models\events\logs\EventLogTenantContact;

/**
 * FormTenantContact is the class for site contact create/edit.
 */
class FormTenantContact extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;
	private $_tenant_id;

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
			[['phone', 'cell_phone'], 'match', 'pattern' => TenantContact::PHONE_VALIDATION_PATTERN],
			['fax', 'match', 'pattern' => TenantContact::FAX_VALIDATION_PATTERN],
			[['job', 'phone', 'fax'], 'string', 'max' => 255],
			[['address', 'comment'], 'string'],
		];
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('backend.tenant', 'Name'),
			'email' => Yii::t('backend.tenant', 'Email'),
			'address' => Yii::t('backend.tenant', 'Address'),
			'job' => Yii::t('backend.tenant', 'Job'),
			'phone' => Yii::t('backend.tenant', 'Phone'),
			'cell_phone' => Yii::t('backend.tenant', 'Cell phone'),
			'fax' => Yii::t('backend.tenant', 'Fax'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_CREATE:
				$this->_tenant_id = $model->id;
				break;

			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

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

		$model = new TenantContact();
		$model->tenant_id = $this->_tenant_id;
		$model->name = $this->name;
		$model->email = $this->email;
		$model->address = $this->address;
		$model->job = $this->job;
		$model->phone = $this->phone;
		$model->cell_phone = $this->cell_phone;
		$model->fax = $this->fax;
		$model->comment = $this->comment;

		$event = new EventLogTenantContact();
		$event->model = $model;
		$model->on(EventLogTenantContact::EVENT_AFTER_INSERT, [$event, EventLogTenantContact::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = TenantContact::findOne($this->_id);
		$model->name = $this->name;
		$model->email = $this->email;
		$model->address = $this->address;
		$model->job = $this->job;
		$model->phone = $this->phone;
		$model->cell_phone = $this->cell_phone;
		$model->fax = $this->fax;
		$model->comment = $this->comment;

		$event = new EventLogTenantContact();
		$event->model = $model;
		$model->on(EventLogTenantContact::EVENT_BEFORE_UPDATE, [$event, EventLogTenantContact::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
