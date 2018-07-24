<?php
namespace backend\controllers;

use common\components\rbac\Role;
use dezmont765\yii2bundle\controllers\MainController;
use dezmont765\yii2bundle\filters\PageSaver;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.03.2017
 * Time: 0:45
 */
class BackendController extends MainController
{
    public $layout = 'base-layout';

    const ALL_ACCESS_RULE = 'all_access_rule';


    public function behaviors() {
        return ArrayHelper::merge(parent::behaviors(), [
            self::ACCESS_FILTER => [
                'class' => AccessControl::className(),
                'rules' => [
                    self::ALL_ACCESS_RULE => [
                        'allow' => true,
                        'roles' => array_flip(Role::getListRoles()),
                    ]
                ],
            ],
            PageSaver::className()
        ]);
    }


}