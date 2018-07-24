<?php

namespace backend\controllers;

use common\models\AirRates;
use common\models\ASubAirRates;
use common\models\SubAirRates;
use dezmont765\yii2bundle\actions\AddDynamicFieldsAction;
use dezmont765\yii2bundle\actions\DeleteAction;
use dezmont765\yii2bundle\actions\LoadSingleDynamicFieldsAction;
use dezmont765\yii2bundle\actions\ReplaceDynamicFieldsAction;
use dezmont765\yii2bundle\actions\SingleDynamicFieldsAction;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.04.2017
 * Time: 17:49
 */
class SubAirRatesController extends BackendController
{


    public function actions() {
        $get_field_action = [
            'class' => LoadSingleDynamicFieldsAction::className(),
            LoadSingleDynamicFieldsAction::MODEL_CLASS => AirRates::className(),
            LoadSingleDynamicFieldsAction::SUB_MODEL_PARENT_CLASS => SubAirRates::className(),
            LoadSingleDynamicFieldsAction::CATEGORY_POST_PARAM => 'type',
            LoadSingleDynamicFieldsAction::CATEGORY_GET_STRATEGY => function ($type) {
                $category = ASubAirRates::getCategoryByRateTypeId($type);
                return $category;
            },
            SingleDynamicFieldsAction::CHILD_BINDING_ATTRIBUTE => 'rate_id',
            SingleDynamicFieldsAction::PARENT_BINDING_ATTRIBUTE => 'id',
        ];
        $add_field_action = $get_field_action;
        $add_field_action['class'] = AddDynamicFieldsAction::className();
        $replace_field_action = $add_field_action;
        $replace_field_action['class'] = ReplaceDynamicFieldsAction::className();
        return [
            'get-fields' => $get_field_action,
            'add-fields' => $add_field_action,
            'replace-fields' => $replace_field_action,
            'delete' => [
                'class' => DeleteAction::className(),
                'is_redirect' => false,
            ],
        ];
    }


    public function getModelClass() {
        return SubAirRates::className();
    }


}