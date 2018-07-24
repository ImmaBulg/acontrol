<?php

namespace backend\controllers;

use common\models\SolerCost;
use common\models\SolerCostSearch;
use dezmont765\yii2bundle\controllers\MainController;
use dosamigos\editable\EditableAction;
use dezmont765\yii2bundle\actions\AsJsonAction;
use dezmont765\yii2bundle\actions\CreateAction;
use dezmont765\yii2bundle\actions\DeleteAction;
use dezmont765\yii2bundle\actions\ListAction;
use dezmont765\yii2bundle\actions\MassDeleteAction;
use dezmont765\yii2bundle\actions\SelectionByAttributeAction;
use dezmont765\yii2bundle\actions\SelectionListAction;
use dezmont765\yii2bundle\actions\UpdateAction;
use yii\helpers\ArrayHelper;
/**
 * SolerCostController implements the CRUD actions for SolerCost model.
 */
class SolerCostController extends BackendController
{
    public $layout = 'base-layout';
    public $defaultAction = 'list';
    public function behaviors()
    {
        $behaviors = ArrayHelper::merge(parent::behaviors(),[
//            'layout' => SolerCostLayout::className(),
        ]);
        return $behaviors;
    }

    public function getRoleToLayoutMap($role) {
        $map = [];
        return $map;
    }

    public function actions()
    {
        return  [
            'ajax-update' => [
                'class' => EditableAction::className(),
                'modelClass' => SolerCost::className(),
                'forceCreate' => false
            ],
            'list' => [
                'class' => ListAction::className(),
                'model_class' => SolerCostSearch::className()
            ],
            'create' => [
                'class' => CreateAction::className(),
            ],
            'update' => [
                'class' => UpdateAction::className(),
            ],
            'delete' => [
                'class' => DeleteAction::className(),
            ],
            'mass-delete' => [
                'class' => MassDeleteAction::className(),
            ],
            'get-selection-list' => [
                'class' => SelectionListAction::className()
            ],
            'get-selection-by-attribute' => [
                'class' => SelectionByAttributeAction::className(),
            ],
            'as-json' => [
                'class' => AsJsonAction::className()
            ]
        ];
    }

    public function getModelClass() {
        return SolerCost::className();
    }

}
