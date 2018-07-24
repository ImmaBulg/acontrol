<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\SiteContact;
use common\models\events\logs\EventLogSiteContact;

/**
 * FormSiteContact is the class for site contact create/edit.
 */
class FormSiteContact extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;
	private $_site_id;

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
			[['phone', 'cell_phone'], 'match', 'pattern' => SiteContact::PHONE_VALIDATION_PATTERN],
			['fax', 'match', 'pattern' => SiteContact::FAX_VALIDATION_PATTERN],
			[['job', 'phone', 'fax'], 'string', 'max' => 255],
			[['address', 'comment'], 'string'],
		];
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('backend.site', 'Name'),
			'email' => Yii::t('backend.site', 'Email'),
			'address' => Yii::t('backend.site', 'Address'),
			'job' => Yii::t('backend.site', 'Job'),
			'phone' => Yii::t('backend.site', 'Phone'),
			'cell_phone' => Yii::t('backend.site', 'Cell phone'),
			'fax' => Yii::t('backend.site', 'Fax'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_CREATE:
				$this->_site_id = $model->id;
				break;

			case self::SCENARIO_EDIT:
				$this->_id = $model->id;
				$this->_site_id = $model->site_id;

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

		$model = new SiteContact();
		$model->site_id = $this->_site_id;
		$model->name = $this->name;
		$model->email = $this->email;
		$model->address = $this->address;
		$model->job = $this->job;
		$model->phone = $this->phone;
		$model->cell_phone = $this->cell_phone;
		$model->fax = $this->fax;
		$model->comment = $this->comment;

		$event = new EventLogSiteContact();
		$event->model = $model;
		$model->on(EventLogSiteContact::EVENT_AFTER_INSERT, [$event, EventLogSiteContact::METHOD_CREATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$model = SiteContact::findOne($this->_id);
		$model->name = $this->name;
		$model->email = $this->email;
		$model->address = $this->address;
		$model->job = $this->job;
		$model->phone = $this->phone;
		$model->cell_phone = $this->cell_phone;
		$model->fax = $this->fax;
		$model->comment = $this->comment;

		$event = new EventLogSiteContact();
		$event->model = $model;
		$model->on(EventLogSiteContact::EVENT_BEFORE_UPDATE, [$event, EventLogSiteContact::METHOD_UPDATE]);

		if (!$model->save()) {
			throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
		}

		return $model;
	}
}
