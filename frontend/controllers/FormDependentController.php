<?php
namespace frontend\controllers;

use backend\models\searches\models\Tenant;
use Yii;
use yii\base\Object;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;

/**
 * FormDependentController
 */
class FormDependentController extends \frontend\components\Controller
{
    public $enableCsrfValidation = false;


    /**
     * @inheritdoc
     */
    public function behaviors() {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }


    public function actionSites() {
        $user = Yii::$app->user->identity;
        $list = ['output' => '', 'selected' => ''];
        if(($parents = Yii::$app->request->post('depdrop_parents')) != null &&
           ($sites = $user->getSitesByClientId(ArrayHelper::getValue($parents, '0'))) != null
        ) {
            $output = ArrayHelper::map($sites, 'id', function ($item) {
                return ['id' => $item->id, 'name' => $item->name];
            });
            $list['output'] = $output;
            $list['selected'] = reset($output)['id'];
        }
        return Json::encode($list);
    }


    public function actionTenants() {
        $user = Yii::$app->user->identity;
        $list = ['output' => '', 'selected' => ''];
        if(($parents = Yii::$app->request->post('depdrop_parents')) != null &&
           ($tenants = $user->getTenantsBySiteId(ArrayHelper::getValue($parents, '0'))) != null
        ) {
            $output = ArrayHelper::map($tenants, 'id', function ($item) {
                return ['id' => $item->id, 'name' => $item->name];
            });
            $list['output'] = $output;
            $list['selected'] = reset($output)['id'];
        }
        return Json::encode($list);
    }


    public function actionMeters() {
        $user = Yii::$app->user->identity;
        $list = ['output' => '', 'selected' => ''];
        if(($parents = Yii::$app->request->post('depdrop_parents')) != null &&
           ($meters = $user->getMetersByTenantId(ArrayHelper::getValue($parents, '0'))) != null
        ) {
            $output = ArrayHelper::map($meters, 'id', function ($item) {
                    return ['id' => $item->id, 'name' => "{$item->name} - ({$item->getAliasType()})"];
                });
            array_unshift($output,['id'=>-1,'name'=>'Not set']);
            $list['output'] = $output;
            $list['selected'] = reset($output)['id'];
        }
        return Json::encode($list);
    }


    public function actionChannels() {
        $user = Yii::$app->user->identity;
        $list = ['output' => '', 'selected' => ''];
        if(($parents = Yii::$app->request->post('depdrop_parents')) != null && ($channels =
                $user->getChannelsByTenantIdAndMeterId(ArrayHelper::getValue($parents, '0'),
                                                       ArrayHelper::getValue($parents, '1'))) != null
        ) {
            $output = ArrayHelper::map($channels, 'id', function ($item) use ($parents) {
                    $tenant = Tenant::findOne(ArrayHelper::getValue($parents, '0'));
                    $result =
                        ['id' => $item->id, 'name' => Yii::t('frontend.view', '{tenant} - {name} - (M={v})', [
                            'tenant' => $tenant->name,
                            'name' => $item->channel,
                            'v' => $item->meter_multiplier,
                        ])];
                    return $result;
                });
            array_unshift($output,['id'=>-1,'name'=>'Not set']);
            $list['output'] = $output;
            $list['selected'] = reset($output)['id'];
        }

        return Json::encode($list);
    }
}
