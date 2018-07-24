<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\User;
use common\helpers\Html;
use backend\models\forms\FormUserLogin;

/**
 * UserAuthController
 */
class UserAuthController extends \backend\components\Controller
{
	public $layout = 'login-layout';
	
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
						'actions' => ['login'],
						'allow' => true,
					],
					[
						'actions' => ['logout'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'logout' => ['post'],
				],
			],
		]);
	}

	public function actionLogin()
	{
		if (!Yii::$app->user->isGuest) {
			return $this->goHome();
		}
		
		$form = new FormUserLogin();

		if ($form->load(Yii::$app->request->post()) && $form->save()) {
			return $this->goBack();
		}

		return $this->render('login', [
			'form' => $form,
		]);
	}
	
	public function actionLogout()
	{
		Yii::$app->user->logout();
		return $this->goHome();
	}
}
