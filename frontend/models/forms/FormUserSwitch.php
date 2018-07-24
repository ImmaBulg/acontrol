<?php

namespace frontend\models\forms;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use common\models\User;
use common\models\Site;
use common\models\Tenant;

/**
 * FormUserSwitch
 */
class FormUserSwitch extends \yii\base\Model
{
	const SCENARIO_TENANT = 'tenant';
//	const SCENARIO_CHANNEL = 'channel';

	public $client_id;
	public $site_id;
	public $tenant_id;
	public $meter_id;
	public $channel_id;

	public function rules()
	{
		return [
			[['client_id', 'site_id'], 'required'],
			[['tenant_id'], 'required', 'on' => static::SCENARIO_TENANT],
//			[['tenant_id', 'meter_id', 'channel_id'], 'required', 'on' => static::SCENARIO_CHANNEL],
			[['client_id', 'site_id', 'tenant_id', 'meter_id', 'channel_id'], 'integer'],
		];
	}

	public function attributeLabels()
	{
		return [
			'client_id' => Yii::t('frontend.view', 'Client'),
			'site_id' => Yii::t('frontend.view', 'Site'),
			'tenant_id' => Yii::t('frontend.view', 'Tenant'),
			'meter_id' => Yii::t('frontend.view', 'Meter'),
			'channel_id' => Yii::t('frontend.view', 'Channel'),
		];
	}
}
