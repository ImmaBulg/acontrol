<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.09.2018
 * Time: 15:43
 */

namespace backend\controllers;

use backend\models\searches\models\SearchHoliday;
use common\models\events\logs\EventLogHoliday;
use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use common\models\Holiday;
use common\widgets\Alert;
use backend\models\forms\FormHoliday;

class HolidayController extends \backend\components\Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'accessCreate' => [
                'class' => AccessControl::className(),
                'only' => ['create'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['HolidayController.actionCreate'],
                    ],
                ],
            ],
            'accessEdit' => [
                'class' => AccessControl::className(),
                'only' => ['edit'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['HolidayController.actionEdit'],
                    ],
                ],
            ],
            'accessDelete' => [
                'class' => AccessControl::className(),
                'only' => ['delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['HolidayController.actionDelete'],
                    ],
                ],
            ],
            'accessList' => [
                'class' => AccessControl::className(),
                'only' => ['list'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['HolidayController.actionList'],
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

    public function actionCreate()
    {
        $form = new FormHoliday();

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Holiday have been added.'));
            return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/holiday/list']));
        }

        return $this->render('create', [
            'form' => $form,
        ]);
    }

    public function actionEdit($id)
    {
        $model = $this->loadHoliday($id);
        $form = new FormHoliday();
        $form->loadAttributes(FormHoliday::SCENARIO_EDIT, $model);

        if ($form->load(Yii::$app->request->post()) && $form->edit()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Holiday have been updated.'));
            return $this->redirect(ArrayHelper::merge(Yii::$app->request->get(), ['/holiday/list']));
        }

        return $this->render('edit', [
            'form' => $form,
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->loadHoliday($id);

        $event = new EventLogHoliday();
        $event->model = $model;
        $model->on(EventLogHoliday::EVENT_BEFORE_DELETE, [$event, EventLogHoliday::METHOD_DELETE]);

        if ($model->delete()) {
            Yii::$app->session->setFlash(Alert::ALERT_SUCCESS, Yii::t('backend.controller', 'Holiday have been deleted.'));
            return $this->goBackReferrer();
        } else {
            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
        }
    }

    public function actionList()
    {
        $search = new SearchHoliday();
        $data_provider = $search->search();
        $filter_model = $search->filter();

        return $this->render('list', [
            'data_provider' => $data_provider,
            'filter_model' => $filter_model,
        ]);
    }

    private function loadHoliday($id)
    {
        $model = Holiday::find()->where(['id' => $id])->one();

        if ($model == null) {
            throw new NotFoundHttpException(Yii::t('backend.controller', 'Holiday not found'));
        }

        return $model;
    }
}