<?php

namespace backend\controllers;

use common\models\MeterType;
use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\models\Meter;
use common\models\Site;
use common\models\SiteMeterChannel;
use common\models\SiteBillingSetting;
use common\models\Tenant;
use common\models\Rate;
use common\models\RateType;
use common\models\RuleGroupLoad;
use common\models\RuleSingleChannel;

/**
 * FormDependentController
 */
class FormDependentController extends \backend\components\Controller
{
	public $enableCsrfValidation = false;

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'accessRoleUsers' => [
				'class' => AccessControl::className(),
				'only' => ['role-users'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['FormDependentController.actionRoleUsers'],
					],
				],
			],
			'accessRuleSingleChannelMeterChannels' => [
				'class' => AccessControl::className(),
				'only' => ['rule-single-channel-meter-channels'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['FormDependentController.actionRuleSingleChannelMeterChannels'],
					],
				],
			],
			'accessRuleGroupLoadMeterChannels' => [
				'class' => AccessControl::className(),
				'only' => ['rule-group-load-meter-channels'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['FormDependentController.actionRuleGroupLoadMeterChannels'],
					],
				],
			],
			'accessUserSites' => [
				'class' => AccessControl::className(),
				'only' => ['user-sites'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['FormDependentController.actionUserSites', 'FormDependentController.actionUserSitesOwner', 'FormDependentController.actionUserSitesSiteOwner'],
					],
				],
			],
			'accessSiteRates' => [
				'class' => AccessControl::className(),
				'only' => ['site-rates'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['FormDependentController.actionSiteRates', 'FormDependentController.actionSiteRatesOwner', 'FormDependentController.actionSiteRatesSiteOwner', 'FormDependentController.actionSiteRatesTenantOwner'],
					],
				],
			],
			'accessSiteContacts' => [
				'class' => AccessControl::className(),
				'only' => ['site-contacts'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['FormDependentController.actionSiteContacts'],
					],
				],
			],
			'accessSiteToIssues' => [
				'class' => AccessControl::className(),
				'only' => ['site-to-issues'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['FormDependentController.actionSiteToIssues', 'FormDependentController.actionSiteToIssuesOwner', 'FormDependentController.actionSiteToIssuesSiteOwner'],
					],
				],
			],
			'accessSiteMeters' => [
				'class' => AccessControl::className(),
				'only' => ['site-meters'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['FormDependentController.actionSiteMeters', 'FormDependentController.actionSiteMetersOwner', 'FormDependentController.actionSiteMetersSiteOwner'],
					],
				],
			],
			'accessSiteMeterChannels' => [
				'class' => AccessControl::className(),
				'only' => ['site-meter-channels'],
				'rules' => [
					[
						'allow' => true,
						'roles' => ['FormDependentController.actionSiteMeterChannels'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'role-users' => ['post'],
					'rule-meter-channels' => ['post'],
					'user-sites' => ['post'],
					'site-rates' => ['post'],
					'site-contacts' => ['post'],
					'site-to-issues' => ['post'],
					'site-meters' => ['post'],
					'meter-types' => ['post'],
					'site-meter-channels' => ['post'],
					'site-ip-addresses' => ['post'],
				],
			],
		]);
	}

	public function actionRoleUsers()
	{
		$list = [
			'output' => '',
			'selected' => '',
		];
		$parents = Yii::$app->request->post('depdrop_parents');
		
		if ($parents != null) {
			$role = $parents[0];
			$output = User::getListByRole($role);

			if ($output != null) {
				array_walk($output, function(&$value, $key){
					$value = ['id' => $key, 'name' => $value];
				});
				$list['output'] = $output;
				$list['selected'] = (string) array_keys($output)[0];
			}
		}

		return Json::encode($list);
	}

	public function actionRuleSingleChannelMeterChannels($tenant_id = null)
	{
		$list = [
			'output' => '',
			'selected' => '',
		];
		$parents = Yii::$app->request->post('depdrop_parents');
		
		if ($parents != null) {			
			$action = $parents[0];
			$meter_id = $parents[1];
			$output = RuleSingleChannel::getListMeterChannels($action, $meter_id, $tenant_id);

			if ($output != null) {
				array_walk($output, function(&$value, $key){
					$value = ['id' => $key, 'name' => $value];
				});
				$list['output'] = $output;
				$list['selected'] = (string) array_keys($output)[0];
			}
		}

		return Json::encode($list);
	}

	public function actionRuleGroupLoadMeterChannels()
	{
		$list = [
			'output' => '',
			'selected' => '',
		];
		$parents = Yii::$app->request->post('depdrop_parents');
		
		if ($parents != null) {
			$meter_id = $parents[0];
			$output = RuleGroupLoad::getListMeterChannels($meter_id);

			if ($output != null) {
				array_walk($output, function(&$value, $key){
					$value = ['id' => $key, 'name' => $value];
				});
				$list['output'] = $output;
				$list['selected'] = (string) array_keys($output)[0];
			}
		}

		return Json::encode($list);
	}

	public function actionUserSites()
	{
		$list = [
			'output' => '',
			'selected' => '',
		];
		$parents = Yii::$app->request->post('depdrop_parents');
		
		if ($parents != null) {
			$user_id = $parents[0];
			$output = Tenant::getListSites($user_id);

			if ($output != null) {
				array_walk($output, function(&$value, $key){
					$value = ['id' => $key, 'name' => $value];
				});
				$list['output'] = $output;
				$list['selected'] = (string) array_keys($output)[0];
			}
		}

		return Json::encode($list);
	}

	public function actionSiteRates()
	{
		$list = [
			'output' => '',
			'selected' => '',
		];
		$parents = Yii::$app->request->post('depdrop_parents');
		
		if ($parents != null) {
			$site_id = $parents[0];
			$output = Rate::getListRateTypes();

			if ($output != null) {
				array_walk($output, function(&$value, $key){
					$value = ['id' => $key, 'name' => $value];
				});

				$model = SiteBillingSetting::findOne(['site_id' => $site_id]);

				if ($model != null && $model->rate_type_id != null) {
					$list['selected'] = (string) $model->rate_type_id;
				} else {
					$list['selected'] = (string) array_keys($output)[0];
				}

				$list['output'] = $output;
			}
		}

		return Json::encode($list);
	}

	public function actionSiteContacts()
	{
		$list = [
			'output' => '',
			'selected' => '',
		];
		$parents = Yii::$app->request->post('depdrop_parents');
		
		if ($parents != null) {
			$site_id = $parents[0];
			$output = Site::getListContacts($site_id);

			if ($output != null) {
				array_walk($output, function(&$value, $key){
					$value = ['id' => $key, 'name' => $value];
				});
				
				$list['output'] = $output;
				$list['selected'] = (string) array_keys($output)[0];
			}
		}

		return Json::encode($list);
	}

	public function actionSiteToIssues()
	{
		$list = [
			'output' => '',
			'selected' => '',
		];
		$parents = Yii::$app->request->post('depdrop_parents');
		
		if ($parents != null) {
			$site_id = $parents[0];
			$output = Site::getListToIssues();

			if ($output != null) {
				array_walk($output, function(&$value, $key){
					$value = ['id' => $key, 'name' => $value];
				});

				$model = Site::findOne($site_id);

				if ($model != null && $model->to_issue != null) {
					$list['selected'] = (string) $model->to_issue;
				} else {
					$list['selected'] = (string) array_keys($output)[0];
				}

				$list['output'] = $output;
			}
		}

		return Json::encode($list);
	}

	public function actionSiteMeters()
	{
		$list = [
			'output' => '',
			'selected' => '',
		];
		$parents = Yii::$app->request->post('depdrop_parents');
		
		if ($parents != null) {
			$site_id = $parents[0];
			$output = Meter::getAirListMeters($site_id);

			if ($output != null) {
				array_walk($output, function(&$value, $key){
					$value = ['id' => $key, 'name' => $value];
				});
				$list['output'] = $output;
				$list['selected'] = 'Select ...';
			}
		}
		return Json::encode($list);
	}

	public function actionSiteMeterChannels()
	{
		$list = [
			'output' => '',
			'selected' => '',
		];
		$parents = Yii::$app->request->post('depdrop_parents');
		
		if ($parents != null) {
			$meter_id = $parents[0];
			$output = Meter::getListMeterChannels($meter_id);

			if ($output != null) {
				array_walk($output, function(&$value, $key){
					$value = ['id' => $key, 'name' => $value];
				});
				$list['output'] = $output;
				$list['selected'] = (string) array_keys($output)[0];
			}
		}

		return Json::encode($list);
	}

	public function actionSiteIpAddresses()
	{
		$list = [
			'output' => '',
			'selected' => '',
		];
		$parents = Yii::$app->request->post('depdrop_parents');
		
		if ($parents != null) {
			$site_id = $parents[0];
			$output = Site::getListIpAddresses($site_id);

			if ($output != null) {
				array_walk($output, function(&$value, $key){
					$value = ['id' => $key, 'name' => $value];
				});
				$list['output'] = $output;
				$list['selected'] = (string) array_keys($output)[0];
			}
		}

		return Json::encode($list);
	}

	public function actionMeterTypes() {
        $list = [
            'output' => '',
            'selected' => '',
        ];
        $parents = Yii::$app->request->post('depdrop_parents');

        if ($parents != null) {
            $type_id = $parents[0];
            $output = Meter::getListTypesByTypeId($type_id);

            if ($output != null) {
                array_walk($output, function(&$value, $key){
                    $value = ['id' => $key, 'name' => $value];
                });
                $list['output'] = $output;
                $list['selected'] = (string) array_keys($output)[0];
            }
        }

        return Json::encode($list);
    }
}
