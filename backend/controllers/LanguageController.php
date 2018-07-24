<?php

namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Cookie;
use common\components\i18n\LanguageSelector;

/**
 * LanguageController
 */
class LanguageController extends \backend\components\Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'actions' => ['switch'],
						'allow' => true,
					],
					[
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		]);
	}

	public function actionSwitch($code)
	{
		$languages = LanguageSelector::getSupportedLanguages();

		if (array_key_exists($code, $languages)) {
			$languageCookie = new Cookie([
				'name' => 'language',
				'value' => $code,
				'expire' => time() + LanguageSelector::COOKIE_LIFETIME,
			]);
			Yii::$app->response->cookies->add($languageCookie);
		}

		return $this->goBackReferrer();
	}
}