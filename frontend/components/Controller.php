<?php
namespace frontend\components;

use Yii;
use yii\filters\AccessControl;

/**
 * Base controller
 */
class Controller extends \common\components\Controller
{
	public $menu = [];
	public $breadcrumbs = [];
	public $title = [];
	public $layout = '//site/main';
	public $trailMainMenu;

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		];
	}
}