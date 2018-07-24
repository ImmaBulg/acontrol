<?php

namespace backend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\Tenant;
use common\models\TenantBillingSetting;
use common\models\Rate;
use common\models\RateType;
use common\models\events\logs\EventLogTenant;

/**
 * FormTenants is the class for site tenants mass edit.
 */
class FormTenants extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	const TENANTS_FIELD_NAME = 'tenants';

	public $rate_type_id;
	public $fixed_payment;

	public function rules()
	{
		return [
			['rate_type_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\RateType', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					RateType::STATUS_INACTIVE,
					RateType::STATUS_ACTIVE,
				]]);
			}],
			[['fixed_payment'], 'number', 'min' => 0],
			[['fixed_payment'], 'compare', 'compareValue' => 0, 'operator' => '>='],
		];
	}

	public function attributeLabels()
	{
		return [
			'rate_type_id' => Yii::t('backend.tenant', 'Rate type'),
			'fixed_payment' => Yii::t('backend.tenant', 'Fixed payment'),
		];
	}

	public function save()
	{
		if (!$this->validate()) return false;
		$tenants = Yii::$app->request->getQueryParam(self::TENANTS_FIELD_NAME);
		if ($tenants == null) return false;

		$transaction = Yii::$app->db->beginTransaction();

		try	{
			$models = Tenant::find()->where(['in', 'id', $tenants])->all();

			if ($models != null) {
				foreach ($models as $model) {
					$model_setting = $model->relationTenantBillingSetting;

					if ($model_setting == null) {
						$model_setting = new TenantBillingSetting();
						$model_setting->site_id = $model->relationSite->id;
						$model_setting->tenant_id = $model->id;
					}

					$model_setting->rate_type_id = $this->rate_type_id;
					$model_setting->fixed_payment = $this->fixed_payment;

					if ($model_setting->getUpdatedAttributes() != null) {
						$event = new EventLogTenant();
						$event->model = $model;
						$model->on(EventLogTenant::EVENT_INIT, [$event, EventLogTenant::METHOD_UPDATE]);
						$model->init();
					}

					if (!$model_setting->save()) {
						throw new BadRequestHttpException(implode(' ', $model_setting->getFirstErrors()));
					}
				}
			}

			$transaction->commit();
			return true;
		} catch(Exception $e) {
			$transaction->rollback();
			throw new BadRequestHttpException($e->getMessage());
		}
	}
}
