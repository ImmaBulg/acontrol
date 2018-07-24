<?php

namespace backend\controllers;

use common\models\AirRates;
use common\models\AirRatesSearch;
use common\models\ASubAirRates;
use common\models\SubAirRates;
use common\models\SubAirRatesBase;
use dezmont765\yii2bundle\actions\CreateWithDynamicChildrenAction;
use dezmont765\yii2bundle\actions\DynamicFieldsAction;
use dezmont765\yii2bundle\actions\UpdateWithDynamicChildrenAction;
use dezmont765\yii2bundle\controllers\MainController;
use dezmont765\yii2bundle\models\MainActiveRecord;
use dosamigos\editable\EditableAction;
use dezmont765\yii2bundle\actions\AsJsonAction;
use dezmont765\yii2bundle\actions\CreateAction;
use dezmont765\yii2bundle\actions\DeleteAction;
use dezmont765\yii2bundle\actions\ListAction;
use dezmont765\yii2bundle\actions\MassDeleteAction;
use dezmont765\yii2bundle\actions\SelectionByAttributeAction;
use dezmont765\yii2bundle\actions\SelectionListAction;
use dezmont765\yii2bundle\actions\UpdateAction;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * AirRatesController implements the CRUD actions for AirRates model.
 */
class AirRatesController extends BackendController
{
    public $defaultAction = 'list';


    public function behaviors() {
        $behaviors = ArrayHelper::merge(parent::behaviors(), [
//            'layout' => AirRatesLayout::className(),
        ]);
        return $behaviors;
    }


    public function getRoleToLayoutMap($role) {
        $map = [];
        return $map;
    }


    public function actions() {
        return [
            'ajax-update' => [
                'class' => EditableAction::className(),
                'modelClass' => AirRates::className(),
                'forceCreate' => false,
            ],
            'list' => [
                'class' => ListAction::className(),
                'model_class' => AirRatesSearch::className(),
            ],
            'create' => [
                'class' => CreateWithDynamicChildrenAction::className(),
                'fields' => [
                    SubAirRates::className() => [
                        DynamicFieldsAction::SUB_MODEL_PARENT_CLASS => SubAirRates::className(),
                        DynamicFieldsAction::CHILD_BINDING_ATTRIBUTE => 'rate_id',
                        DynamicFieldsAction::PARENT_BINDING_ATTRIBUTE => 'id',
                        DynamicFieldsAction::CATEGORY_GET_STRATEGY => function ($type) {
                            $category = ASubAirRates::getCategoryByRateTypeId($type);
                            return $category;
                        },
                        DynamicFieldsAction::CATEGORY_POST_PARAM => 'type',
                    ],
                ],
            ],
            'update' => [
                'class' => UpdateWithDynamicChildrenAction::className(),
                'fields' => [
                    SubAirRates::className() => [
                        DynamicFieldsAction::SUB_MODEL_PARENT_CLASS => SubAirRates::className(),
                        DynamicFieldsAction::CHILD_BINDING_ATTRIBUTE => 'rate_id',
                        DynamicFieldsAction::PARENT_BINDING_ATTRIBUTE => 'id',
                        DynamicFieldsAction::CATEGORY_GET_STRATEGY => function ($type) {
                            $category = ASubAirRates::getCategoryByRateTypeId($type);
                            return $category;
                        },
                        DynamicFieldsAction::CATEGORY_POST_PARAM => 'type',
                    ],
                ],
            ],
            'delete' => [
                'class' => DeleteAction::className(),
            ],
            'mass-delete' => [
                'class' => MassDeleteAction::className(),
            ],
            'get-selection-list' => [
                'class' => SelectionListAction::className(),
            ],
            'get-selection-by-attribute' => [
                'class' => SelectionByAttributeAction::className(),
            ],
            'as-json' => [
                'class' => AsJsonAction::className(),
            ],
        ];
    }


//    public function actionCreate() {
//        /**
//         * @var $sub_model_class ASubAirRates
//         * @var $sub_models ASubAirRates[]
//         * @var $model AirRates
//         * @var $model_class AirRates
//         */
//        $model_class = $this->getModelClass();
//        $model = new $model_class;
//        $type = Yii::$app->request->getBodyParams()[$model_class::_formName()]['rate_type_id'];
//        $category = ASubAirRates::getCategoryByRateTypeId($type);
//        $sub_model_class = SubAirRates::getSubTableClassByCategory($category);
//        $sub_model_attribute_sets = Yii::$app->request->post($sub_model_class::_formName());
//        $sub_models = [];
//        foreach($sub_model_attribute_sets as $key => $sub_model_attribute_set) {
//            $sub_models[$key] = new $sub_model_class;
//            $sub_models[$key]->attributes = $sub_model_attribute_set;
//        }
//        if(empty($sub_models)) {
//            $sub_models[] = new $sub_model_class;
//        }
//        $result = $this->ajaxValidationMultiple(array_merge($sub_models, [$model]), null, [$model_class]);
//        if($result !== null) {
//            return $result;
//        }
//        if($model->load(Yii::$app->request->post())) {
//            if($model->save()) {
//                foreach($sub_models as $sub_model) {
//                    $sub_model->rate_id = $model->id;
//                    $sub_model->category = $category;
//                    $sub_model->save();
//                }
//                $this->redirect(['update', 'id' => $model->id]);
//            }
//        }
//        return $this->render('air-rates-form', ['model' => $model]);
//    }
//
//
//    public function actionUpdate($id) {
//        /**
//         * @var $sub_model_class ASubAirRates
//         * @var $sub_models ASubAirRates[]
//         * @var $model AirRates
//         * @var $model_class AirRates
//         */
//        $model_class = $this->getModelClass();
//        $model = $this->findModel($this->getModelClass(), $id);
//        $type = Yii::$app->request->getBodyParams()[$model_class::_formName()]['rate_type_id'];
//        $category = ASubAirRates::getCategoryByRateTypeId($type);
//        $sub_model_class = SubAirRates::getSubTableClassByCategory($category);
//        $sub_model_attribute_sets = Yii::$app->request->post($sub_model_class::_formName());
//        if($sub_model_class::tableName() !== SubAirRates::tableName()) {
//            $sub_models = $sub_model_class::find()
//                                          ->joinWith(['subAirRate' => function (ActiveQuery $query) use ($id, $category) {
//                                              $query->andWhere(['rate_id' => $id])
//                                                    ->andWhere(['category' => $category]);
//                                          }])
//                                          ->indexBy('id')
//                                          ->all();
//        }
//        else {
//            $sub_models =
//                $sub_model_class::find()->where(['AND', ['rate_id' => $id], ['category' => $category]])->indexBy('id')
//                                ->all();
//        }
//        foreach($sub_model_attribute_sets as $key => $sub_model_attribute_set) {
//            if(!$sub_models[$key] instanceof $sub_model_class) {
//                $sub_models[$key] = new $sub_model_class;
//            }
//            $sub_models[$key]->attributes = $sub_model_attribute_set;
//        }
//        if(empty($sub_models)) {
//            $sub_models[] = new $sub_model_class;
//        }
//        $result = $this->ajaxValidationMultiple(array_merge($sub_models, [$model]), null, [$model_class]);
//        if($result !== null) {
//            return $result;
//        }
//        if($model->load(Yii::$app->request->post())) {
//            if($model->save()) {
//                foreach($sub_models as $sub_model) {
//                    $sub_model->rate_id = $model->id;
//                    $sub_model->category = $category;
//                    $sub_model->save();
//                }
//            }
//        }
//        return $this->render('air-rates-form', ['model' => $model]);
//    }


    public function getModelClass() {
        return AirRates::className();
    }

}
