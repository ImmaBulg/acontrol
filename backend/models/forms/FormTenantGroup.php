<?php

namespace backend\models\forms;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\Tenant;
use common\models\TenantGroup;
use common\models\TenantGroupItem;
use common\models\events\logs\EventLogTenantGroup;

/**
 * FormTenantGroup is the class for tenant group create/edit.
 */
class FormTenantGroup extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $site_id;
	public $name;
	public $group_tenants;

	public function rules()
	{
		return [
			[['name'], 'filter', 'filter' => 'strip_tags'],
			[['name'], 'filter', 'filter' => 'trim'],
			[['name', 'site_id', 'group_tenants'], 'required'],
			[['name'], 'string', 'max' => 255],
			['site_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\Site', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['status' => Site::STATUS_ACTIVE]);
			}],
			['group_tenants', 'validateGroupTenants'],
		];
	}

	public function validateGroupTenants($attribute, $params)
	{
		$values = (array) $this->$attribute;

		$count = Tenant::find()->where(['in', 'id', array_values($values)])
		->andWhere(['in', 'status', [
			Tenant::STATUS_INACTIVE,
			Tenant::STATUS_ACTIVE,
		]])->count();

		if (count($values) != $count) {
			return $this->addError($attribute, Yii::t('backend.tenant', '{attribute} is invalid.', [
				'attribute' => $this->getAttributeLabel($attribute),
			]));
		}
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('backend.tenant', 'Name'),
			'site_id' => Yii::t('backend.tenant', 'Site'),
			'group_tenants' => Yii::t('backend.tenant', 'Tenants in group'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

				$this->site_id = $model->site_id;
				$this->name = $model->name;

				$group_items = $model->relationTenantGroupItems;

				foreach ($group_items as $group_item) {
					$this->group_tenants[] = $group_item->tenant_id;
				}
				break;

			default:
				break;
		}
	}

	public function save()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();
		
		try	{
			$model_site = Site::findOne($this->site_id);
			$model_site_owner = $model_site->relationUser;

			$model = new TenantGroup();
			$model->name = $this->name;
			$model->user_id = $model_site_owner->id;
			$model->site_id = $model_site->id;

			$event = new EventLogTenantGroup();
			$event->model = $model;
			$model->on(EventLogTenantGroup::EVENT_AFTER_INSERT, [$event, EventLogTenantGroup::METHOD_CREATE]);

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			$group_tenants = $this->group_tenants;

			foreach ($group_tenants as $tenant) {
				$model_item = new TenantGroupItem();
				$model_item->group_id = $model->id;
				$model_item->tenant_id = $tenant;

				if (!$model_item->save()) {
					throw new BadRequestHttpException(implode(' ', $model_item->getFirstErrors()));
				}
			}

			$transaction->commit();
			return $model;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}

	public function edit()
	{
		if (!$this->validate()) return false;

		$transaction = Yii::$app->db->beginTransaction();
		
		try	{
			$updated_attributes = [];

			$model_site = Site::findOne($this->site_id);
			$model_site_owner = $model_site->relationUser;
			
			$model = TenantGroup::findOne($this->_id);
			$model->name = $this->name;
			$model->user_id = $model_site_owner->id;
			$model->site_id = $model_site->id;

			$updated_attributes = ArrayHelper::merge($model->getUpdatedAttributes(), $updated_attributes);

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			$group_tenants = $this->group_tenants;
			$model_items = $model->relationTenantGroupItems;

			foreach ($model_items as $model_item) {
				if (in_array($model_item->tenant_id, $group_tenants)) {
					unset($group_tenants[array_search($model_item->tenant_id, $group_tenants)]);
				} else {
					$updated_attributes = ArrayHelper::merge([
						$model_item->id => TenantGroup::STATUS_DELETED,
					], $updated_attributes);
					$model_item->delete();
				}
			}

			if ($group_tenants != null) {
				foreach ($group_tenants as $tenant) {
					$model_item = new TenantGroupItem();
					$model_item->group_id = $model->id;
					$model_item->tenant_id = $tenant;

					$updated_attributes = ArrayHelper::merge($model_item->getUpdatedAttributes(), $updated_attributes);
					
					if (!$model_item->save()) {
						throw new BadRequestHttpException(implode(' ', $model_item->getFirstErrors()));
					}
				}
			}

			if ($updated_attributes != null) {
				$event = new EventLogTenantGroup();
				$event->model = $model;
				$model->on(EventLogTenantGroup::EVENT_INIT, [$event, EventLogTenantGroup::METHOD_UPDATE]);
				$model->init();
			}

			$transaction->commit();
			return $model;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}
}
