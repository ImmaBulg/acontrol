<?php

namespace backend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use common\models\MeterType;
use common\widgets\Alert;
use backend\models\forms\FormMeterType;
use backend\models\searches\SearchMeterType;
use common\models\events\logs\EventLogMeterType;

/**
 * MeterTypeController
 */
class MeterTypeController extends \backend\components\Controller
{
    public $enableCsrfValidation = false;


    /**
     * @inheritdoc
     */
    public function behaviors() {
        return array_merge(parent::behaviors(), [
            'accessCreate' => [
                'class' => AccessControl::className(),
                'only' => ['create'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['MeterTypeController.actionCreate'],
                    ],
                ],
            ],
            'accessEdit' => [
                'class' => AccessControl::className(),
                'only' => ['edit'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['MeterTypeController.actionEdit'],
                    ],
                ],
            ],
            'accessDelete' => [
                'class' => AccessControl::className(),
                'only' => ['delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['MeterTypeController.actionDelete'],
                    ],
                ],
            ],
            'accessList' => [
                'class' => AccessControl::className(),
                'only' => ['list'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['MeterTypeController.actionList'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }


    public function actionCreate() {
        $form = new FormMeterType();
        $form->scenario = FormMeterType::SCENARIO_CREATE;
        if($form->load(Yii::$app->request->post()) && $form->save()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS,
                                         Yii::t('backend.controller', 'Meter type have been added.'));
            return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/meter-type/list']));
        }
        return $this->render('meter-type-form', [
            'form' => $form,
        ]);
    }


    public function actionEdit($id) {
        $model = $this->loadMeterType($id);
        $form = new FormMeterType();
        $form->scenario = FormMeterType::SCENARIO_EDIT;
        $form->loadAttributes(FormMeterType::SCENARIO_EDIT, $model);
        if($form->load(Yii::$app->request->post()) && $form->edit()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS,
                                         Yii::t('backend.controller', 'Meter type have been updated.'));
            return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/meter-type/list']));
        }
        return $this->render('meter-type-form', [
            'form' => $form,
            'model' => $model,
        ]);
    }


    public function actionDelete($id) {
        $model = $this->loadMeterType($id);
        $event = new EventLogMeterType();
        $event->model = $model;
        $model->on(EventLogMeterType::EVENT_BEFORE_DELETE, [$event, EventLogMeterType::METHOD_DELETE]);
        if($model->delete()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS,
                                         Yii::t('backend.controller', 'Meter type have been deleted.'));
            return $this->goBackReferrer();
        }
        else {
            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
        }
    }


    public function actionList() {
        $search = new SearchMeterType();
        $data_provider = $search->search();
        $filter_model = $search->filter();
        return $this->render('list', [
            'data_provider' => $data_provider,
            'filter_model' => $filter_model,
        ]);
    }


    private function loadMeterType($id) {
        $model = MeterType::find()->where([
                                              'id' => $id,
                                          ])->andWhere(['in', 'status', [
            MeterType::STATUS_INACTIVE,
            MeterType::STATUS_ACTIVE,
        ]])->one();
        if($model == null) {
            throw new NotFoundHttpException(Yii::t('backend.controller', 'Meter type not found'));
        }
        return $model;
    }
}
