<?php
 
namespace backend\models\forms;

use Exception;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\models\Site;
use common\models\SiteBillingSetting;
use common\models\Tenant;
use common\models\TenantBillingSetting;
use common\models\Rate;
use common\models\Report;
use common\models\RateType;
use common\components\rbac\Role;
use common\models\events\logs\EventLogSite;

/**
 * FormSite is the class for site create/edit.
 */
class FormSite extends \yii\base\Model
{
	const SCENARIO_CREATE = 'create';
	const SCENARIO_EDIT = 'edit';

	private $_id;

	public $user_id;
	public $name;
	public $electric_company_id;
	public $to_issue;

	public $rate_type_id;
	public $fixed_payment;
	public $billing_day;
	public $include_vat;
	public $comment;
	public $fixed_addition_type;
	public $fixed_addition_load;
	public $fixed_addition_value;
	public $fixed_addition_comment;
	public $auto_issue_reports;
	public $power_factor_visibility = Site::POWER_FACTOR_DONT_SHOW;

	public function rules()
	{
		return [
			[['name', 'electric_company_id'], 'filter', 'filter' => 'strip_tags'],
			[['name', 'electric_company_id'], 'filter', 'filter' => 'trim'],
			[['user_id', 'name', 'to_issue'], 'required'],
			[['name'], 'string', 'max' => 255],
			[['electric_company_id'], 'string'],
			['user_id', 'in', 'range' => array_keys(User::getListClients()), 'skipOnEmpty' => false],
			[['rate_type_id', 'billing_day'], 'required'],
			[['fixed_payment'], 'number', 'min' => 0],
			[['fixed_payment'], 'compare', 'compareValue' => 0, 'operator' => '>='],
			[['fixed_addition_value'], 'number'],
			[['include_vat'], 'default', 'value' => SiteBillingSetting::NO],
			[['include_vat'], 'boolean'],
			[['comment', 'fixed_addition_comment'], 'string'],
			['to_issue', 'in', 'range' => array_keys(Site::getListToIssues()), 'skipOnEmpty' => false],
			['billing_day', 'in', 'range' => array_keys(SiteBillingSetting::getListBillingDays()), 'skipOnEmpty' => false],
			['rate_type_id', '\common\components\validators\ModelExistsValidator', 'modelClass' => '\common\models\RateType', 'modelAttribute' => 'id', 'filter' => function($model){
				return $model->andWhere(['in', 'status', [
					RateType::STATUS_INACTIVE,
					RateType::STATUS_ACTIVE,
				]]);
			}],
			['fixed_addition_type', 'in', 'range' => array_keys(SiteBillingSetting::getListFixedAdditionTypes()), 'skipOnEmpty' => true],
			['fixed_addition_load', 'in', 'range' => array_keys(SiteBillingSetting::getListFixedAdditionLoads()), 'skipOnEmpty' => true],
			[['fixed_addition_load', 'fixed_addition_value'], 'required', 'when' => function($model) {
				return $model->fixed_addition_type != null;
			}, 'enableClientValidation' => false],
			['auto_issue_reports', 'each', 'rule' => ['in', 'range' => array_keys(Report::getListTypes()), 'skipOnEmpty' => true]],
            ['power_factor_visibility','in','range' => array_keys(Site::getListPowerFactors())]
		];
	}

	public function attributeLabels()
	{
		return [
			'name' => Yii::t('backend.site', 'Name'),
			'electric_company_id' => Yii::t('backend.site', 'Electric company ID'),
			'user_id' => Yii::t('backend.site', 'Client'),
			'to_issue' => Yii::t('backend.site', 'To issue'),
			'auto_issue_reports' => Yii::t('backend.site', 'Auto issue reports'),

			'rate_type_id' => Yii::t('backend.site', 'Rate type'),
			'fixed_payment' => Yii::t('backend.site', 'Fixed payment'),
			'billing_day' => Yii::t('backend.site', 'Day of billing'),
			'include_vat' => Yii::t('backend.site', 'Include VAT'),
			'comment' => Yii::t('backend.site', 'Comment'),
			'fixed_addition_type' => Yii::t('backend.site', 'Fixed addition of'),
			'fixed_addition_load' => Yii::t('backend.site', 'Load as'),
			'fixed_addition_value' => Yii::t('backend.site', 'Value (money, kwh or percentage)'),
			'fixed_addition_comment' => Yii::t('backend.site', 'Comment for fixed addition'),
		];
	}

	public function loadAttributes($scenario, $model)
	{
		switch ($scenario) {
			case self::SCENARIO_EDIT:
				$this->_id = $model->id;

				$this->user_id = $model->user_id;
				$this->name = $model->name;
				$this->electric_company_id = $model->electric_company_id;
				$this->to_issue = $model->to_issue;
				$this->auto_issue_reports = $model->getAutoIssueReports();
                $this->power_factor_visibility = $model->power_factor_visibility;
				
				$model_billing = $model->relationSiteBillingSetting;
				$this->rate_type_id = $model_billing->rate_type_id;
				$this->fixed_payment = $model_billing->fixed_payment;
				$this->billing_day = $model_billing->billing_day;
				$this->include_vat = $model_billing->include_vat;
				$this->comment = $model_billing->comment;
				$this->fixed_addition_type = $model_billing->fixed_addition_type;
				$this->fixed_addition_load = $model_billing->fixed_addition_load;
				$this->fixed_addition_value = $model_billing->fixed_addition_value;
				$this->fixed_addition_comment = $model_billing->fixed_addition_comment;

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
			$model = new Site();
			$model->user_id = $this->user_id;
			$model->name = $this->name;
			$model->electric_company_id = $this->electric_company_id;
			$model->to_issue = $this->to_issue;
			$model->power_factor_visibility = $this->power_factor_visibility;
			$model->setAutoIssueReports($this->auto_issue_reports);

			$event = new EventLogSite();
			$event->model = $model;
			$model->on(EventLogSite::EVENT_AFTER_INSERT, [$event, EventLogSite::METHOD_CREATE]);

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			$model_billing = new SiteBillingSetting();
			$model_billing->site_id = $model->id;
			$model_billing->rate_type_id = $this->rate_type_id;
			$model_billing->fixed_payment = $this->fixed_payment;
			$model_billing->billing_day = $this->billing_day;
			$model_billing->include_vat = $this->include_vat;
			$model_billing->comment = $this->comment;

			switch ($this->fixed_addition_type) {
				case SiteBillingSetting::FIXED_ADDITION_TYPE_MONEY:
				case SiteBillingSetting::FIXED_ADDITION_TYPE_KWH:
					$model_billing->fixed_addition_type = $this->fixed_addition_type;
					$model_billing->fixed_addition_load = $this->fixed_addition_load;
					$model_billing->fixed_addition_value = $this->fixed_addition_value;
					$model_billing->fixed_addition_comment = $this->fixed_addition_comment;
					break;
				
				default:
					$model_billing->fixed_addition_type = $this->fixed_addition_type;
					$model_billing->fixed_addition_load = null;
					$model_billing->fixed_addition_value = null;
					$model_billing->fixed_addition_comment = null;
					break;
			}

			if (!$model_billing->save()) {
				throw new BadRequestHttpException(implode(' ', $model_billing->getFirstErrors()));
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

			$model = Site::findOne($this->_id);
			$model->user_id = $this->user_id;
			$model->name = $this->name;
			$model->electric_company_id = $this->electric_company_id;
			$model->to_issue = $this->to_issue;
            $model->power_factor_visibility = $this->power_factor_visibility;
			$model->setAutoIssueReports($this->auto_issue_reports);

			$updated_attributes = ArrayHelper::merge($model->getUpdatedAttributes(), $updated_attributes);

			if (ArrayHelper::getValue($model->getOldAttributes(), 'user_id') != $model->user_id) {
				Tenant::updateAll([
					'user_id' => $model->user_id
				], [
					'site_id' => $model->id,
				]);
			}

			if (!$model->save()) {
				throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
			}

			$model_billing = $model->relationSiteBillingSetting;
			$model_billing->rate_type_id = $this->rate_type_id;
			$model_billing->fixed_payment = $this->fixed_payment;
			$model_billing->billing_day = $this->billing_day;
			$model_billing->include_vat = $this->include_vat;
			$model_billing->comment = $this->comment;

			switch ($this->fixed_addition_type) {
				case SiteBillingSetting::FIXED_ADDITION_TYPE_MONEY:
				case SiteBillingSetting::FIXED_ADDITION_TYPE_KWH:
					$model_billing->fixed_addition_type = $this->fixed_addition_type;
					$model_billing->fixed_addition_load = $this->fixed_addition_load;
					$model_billing->fixed_addition_value = $this->fixed_addition_value;
					$model_billing->fixed_addition_comment = $this->fixed_addition_comment;
					break;
				
				default:
					$model_billing->fixed_addition_type = $this->fixed_addition_type;
					$model_billing->fixed_addition_load = null;
					$model_billing->fixed_addition_value = null;
					$model_billing->fixed_addition_comment = null;
					break;
			}

			$updated_attributes = ArrayHelper::merge($model_billing->getUpdatedAttributes(), $updated_attributes);

			if (!$model_billing->save()) {
				throw new BadRequestHttpException(implode(' ', $model_billing->getFirstErrors()));
			}

			if ($updated_attributes != null) {
				$event = new EventLogSite();
				$event->model = $model;
				$model->on(EventLogSite::EVENT_INIT, [$event, EventLogSite::METHOD_UPDATE]);
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
