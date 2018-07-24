<?php
namespace backend\components;

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
	public $layout = 'base-layout';
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