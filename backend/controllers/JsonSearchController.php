<?php

namespace backend\controllers;

use Yii;
use yii\db\Query;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use common\models\Rate;
use common\models\RateType;
use common\models\Meter;
use common\models\MeterChannel;
use common\models\ElectricityMeterRawData;
use common\models\RuleSingleChannel;
use common\models\Tenant;
use common\models\Site;
use common\models\SiteBillingSetting;

/**
 * JsonSearchController
 */
class JsonSearchController extends \backend\components\Controller
{
    public $enableCsrfValidation = false;


    /**
     * @inheritdoc
     */
    public function behaviors() {
        return array_merge(parent::behaviors(), [
            'accessMeterChannels' => [
                'class' => AccessControl::className(),
                'only' => ['meter-channels'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['JsonSearchController.actionMeterChannels',
                                    'JsonSearchController.actionMeterChannelsOwner',
                                    'JsonSearchController.actionMeterChannelsSiteOwner'],
                    ],
                ],
            ],
            'accessMeterChannelInfo' => [
                'class' => AccessControl::className(),
                'only' => ['meter-channel-info'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['JsonSearchController.actionMeterChannelInfo'],
                    ],
                ],
            ],
            'accessRateFixedPayment' => [
                'class' => AccessControl::className(),
                'only' => ['rate-fixed-payment'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['JsonSearchController.actionRateFixedPayment',
                                    'JsonSearchController.actionRateFixedPaymentOwner',
                                    'JsonSearchController.actionRateFixedPaymentSiteOwner',
                                    'JsonSearchController.actionRateFixedPaymentTenantOwner'],
                    ],
                ],
            ],
            'accessSiteFixedPayment' => [
                'class' => AccessControl::className(),
                'only' => ['site-fixed-payment'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $id_site = Yii::$app->request->getQueryParam('site_id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('JsonSearchController.actionSiteFixedPayment') ||
                                   Yii::$app->user->can('JsonSearchController.actionSiteFixedPaymentOwner',
                                                        ['model' => $model_site]) ||
                                   Yii::$app->user->can('JsonSearchController.actionSiteFixedPaymentSiteOwner',
                                                        ['model' => $model_site]) ||
                                   Yii::$app->user->can('JsonSearchController.actionSiteFixedPaymentTenantOwner',
                                                        ['model' => $model_site]);
                        },
                    ],
                ],
            ],
            'accessSiteTenants' => [
                'class' => AccessControl::className(),
                'only' => ['site-tenants'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $id_site = Yii::$app->request->getQueryParam('site_id');
                            $model_site = $this->loadSite($id_site);
                            return Yii::$app->user->can('JsonSearchController.actionSiteTenants') ||
                                   Yii::$app->user->can('JsonSearchController.actionSiteTenantsOwner',
                                                        ['model' => $model_site]) ||
                                   Yii::$app->user->can('JsonSearchController.actionSiteTenantsSiteOwner',
                                                        ['model' => $model_site]);
                        },
                    ],
                ],
            ],
        ]);
    }


    public function actionMeterChannels($meter_id) {
        $list = Meter::getListMeterChannels($meter_id);
        return Json::encode($list);
    }


    public function actionMeterChannelInfo($channel_id) {
        $list = [];
        $model = MeterChannel::findOne($channel_id);
        if($model != null) {
            $list['current_multiplier'] = $model->current_multiplier;
            $list['voltage_multiplier'] = $model->voltage_multiplier;
            $list['edit_link'] = Url::to(['meter-channel/edit', 'id' => $model->id]);
        }
        return Json::encode($list);
    }


    public function actionRateFixedPayment($id = '') {
        $list = [];
        $model = Rate::find()->andWhere(['rate_type_id' => $id])->andWhere(['in', 'status', [
            Rate::STATUS_INACTIVE,
            Rate::STATUS_ACTIVE,
        ]])->orderBy(['end_date' => SORT_DESC])->one();
        if($model != null) {
            $list = ['fixed_payment' => $model->fixed_payment];
        }
        return Json::encode($list);
    }


    public function actionSiteBillingSettings($site_id = '') {
        $list = [];
        $model = SiteBillingSetting::findOne(['site_id' => $site_id]);
        $attributes = Yii::$app->request->get('attributes');
        if($model != null) {
            if(is_array($attributes)) {
                foreach($attributes as $attribute) {
                    $list[$attribute] = $model->$attribute;
                }
            }
        }
        return Json::encode($list);
    }


    public function actionSiteTenants($site_id) {
        $data = [];
        $list = Site::getListTenants($site_id);
        if($list != null) {
            foreach($list as $id => $value) {
                $data[] = [
                    'id' => $id,
                    'value' => $value,
                ];
            }
        }
        return Json::encode($data);
    }


    private function loadSite($id) {
        $model = Site::find()->andWhere([
                                            'id' => $id,
                                        ])->andWhere(['in', 'status', [
            Site::STATUS_INACTIVE,
            Site::STATUS_ACTIVE,
        ]])->one();
        if($model == null) {
            throw new NotFoundHttpException(Yii::t('backend.controller', 'Site not found'));
        }
        return $model;
    }
}
