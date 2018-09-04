<?php
namespace frontend\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\data\ArrayDataProvider;
use yii\base\Exception;
use yii\base\UserException;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\web\Response;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use common\widgets\Alert;
use common\components\rbac\Role;
use common\helpers\MetmonRealTime;
use common\models\User;
use common\models\Site;
use common\models\Tenant;
use common\models\Report;
use common\models\Meter;
use common\models\MeterChannel;
use frontend\models\forms\FormUserSwitch;
use frontend\models\forms\FormHistoryConsumption;
use frontend\models\searches\SearchReport;

/**
 * DashboardController
 */
class DashboardController extends \frontend\components\Controller
{
    public $enableCsrfValidation = false;


    /**
     * @inheritdoc
     */
    public function behaviors() {
        return array_merge(parent::behaviors(), [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['metmon', 'canvas'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['error'],
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


    public function actionIndex() {
        $metmon = [];
        $realtime = [];
        $metmon_url = null;
        $user = Yii::$app->user->identity;
        $session = Yii::$app->session;
        $form_switch = new FormUserSwitch();
//		$form_switch->scenario = FormUserSwitch::SCENARIO_CHANNEL;
        if($form_switch->load(Yii::$app->request->get())) {
            if($form_switch->validate()) {
                $session->set('switch', [
                    'client_id' => $form_switch->client_id,
                    'site_id' => $form_switch->site_id,
                    'tenant_id' => $form_switch->tenant_id,
                    'meter_id' => $form_switch->meter_id,
                    'channel_id' => $form_switch->channel_id,
                ]);
            }
        }
        else {
            $form_switch->client_id = ArrayHelper::getValue($user->getSelectedClient(), 'id');
            $form_switch->site_id = ArrayHelper::getValue($user->getSelectedSite(), 'id');
            $form_switch->tenant_id = ArrayHelper::getValue($user->getSelectedTenant(), 'id');
            $form_switch->meter_id = ArrayHelper::getValue($user->getSelectedMeter(), 'id');
            $form_switch->channel_id = ArrayHelper::getValue($user->getSelectedChannel(), 'id');
        }
        if(($site = Site::findOne($form_switch->site_id)) != null &&
           ($meter = Meter::findOne($form_switch->meter_id)) != null &&
           ($channel = MeterChannel::findOne($form_switch->channel_id)) != null
        ) {
            $metmon = MetmonRealTime::generate($site, $meter, $channel);
            $realtime = $this->buildRealTime($site, $meter, $channel, $metmon);
            $metmon_url = $this->buildMetmonUrl($site, $meter, $channel);
        }
        $data_provider = new ArrayDataProvider([
                                                   'allModels' => [
                                                       [
                                                           'energy' => Yii::t('frontend.view', 'KWH'),
                                                           'shefel' => ArrayHelper::getValue($metmon,
                                                                                             'Tf1TotImpKWh.value', 0),
                                                           'geva' => ArrayHelper::getValue($metmon,
                                                                                           'Tf2TotImpKWh.value', 0),
                                                           'pisga' => ArrayHelper::getValue($metmon,
                                                                                            'Tf3TotImpKWh.value', 0),
                                                           'total' => ArrayHelper::getValue($metmon,
                                                                                            'TfTotImpKWh.value', 0),
                                                           'type' => 'TfTotImpKWh',
                                                       ],
                                                       [
                                                           'energy' => Yii::t('frontend.view', 'RKWH'),
                                                           'shefel' => ArrayHelper::getValue($metmon,
                                                                                             'Tf1TotExpKWh.value', 0),
                                                           'geva' => ArrayHelper::getValue($metmon, 'Tf2TotExpKW.value',
                                                                                           0),
                                                           'pisga' => ArrayHelper::getValue($metmon,
                                                                                            'Tf2TotExpKWh.value', 0),
                                                           'total' => ArrayHelper::getValue($metmon,
                                                                                            'TfTotExpKWh.value', 0),
                                                           'type' => 'TfTotExpKWh',
                                                       ],
                                                   ],
                                                   'pagination' => false,
                                                   'sort' => [
                                                       'attributes' => ['energy', 'shefel', 'geva', 'pisga', 'total',
                                                                        'type'],
                                                   ],
                                               ]);
        if(Yii::$app->request->isPjax) {
            return $this->renderAjax('index', [
                'realtime' => $realtime,
                'metmon_url' => $metmon_url,
                'user' => $user,
                'form_switch' => $form_switch,
                'metmon' => $metmon,
                'data_provider' => $data_provider,
            ]);
        }
        else {
            return $this->render('index', [
                'realtime' => $realtime,
                'metmon_url' => $metmon_url,
                'user' => $user,
                'form_switch' => $form_switch,
                'metmon' => $metmon,
                'data_provider' => $data_provider,
            ]);
        }
    }


    public function actionMetmon() {
        $metmon = [];
        $realtime = [];
        $user = Yii::$app->user->identity;
        $site = $user->getSelectedSite();
        $meter = $user->getSelectedMeter();
        $channel = $user->getSelectedChannel();
        if($site != null && $meter != null && $channel != null) {
            $metmon = MetmonRealTime::generate($site, $meter, $channel);
            $realtime = $this->buildRealTime($site, $meter, $channel, $metmon);
        }
        return $metmon;
    }

    public function actionCanvas() {
        $metmon = [];
        $realtime = [];
        $user = Yii::$app->user->identity;
        $site = $user->getSelectedSite();
        $meter = $user->getSelectedMeter();
        $channel = $user->getSelectedChannel();
        if($site != null && $meter != null && $channel != null) {
            $metmon = MetmonRealTime::generate($site, $meter, $channel);
            $realtime = $this->buildRealTime($site, $meter, $channel, $metmon);
        }
        return $realtime;
    }


    public function actionHistoryConsumption() {
        $user = Yii::$app->user->identity;
        $session = Yii::$app->session;
        $form_switch = new FormUserSwitch();
//		$form_switch->scenario = FormUserSwitch::SCENARIO_CHANNEL;
        if($form_switch->load(Yii::$app->request->get())) {
            if($form_switch->validate()) {
                $session->set('switch', [
                    'client_id' => $form_switch->client_id,
                    'site_id' => $form_switch->site_id,
                    'tenant_id' => $form_switch->tenant_id,
                    'meter_id' => $form_switch->meter_id,
                    'channel_id' => $form_switch->channel_id,
                ]);
            }
        }
        else {
            $form_switch->client_id = ArrayHelper::getValue($user->getSelectedClient(), 'id');
            $form_switch->site_id = ArrayHelper::getValue($user->getSelectedSite(), 'id');
            $form_switch->tenant_id = ArrayHelper::getValue($user->getSelectedTenant(), 'id');
            $form_switch->meter_id = ArrayHelper::getValue($user->getSelectedMeter(), 'id');
            $form_switch->channel_id = ArrayHelper::getValue($user->getSelectedChannel(), 'id');
        }
        $form = new FormHistoryConsumption();
        $form->from_date = Yii::$app->formatter->asDate(strtotime('first day of this month'), 'dd-MM-yyyy');
        $form->to_date = Yii::$app->formatter->asDate(time(), 'dd-MM-yyyy');
        $form->drilldown = $form::DRILLDOWN_DAILY;
        $form->load(Yii::$app->request->get());
        if($form->compare_from_date == null) {
            $form->compare_from_date =
                Yii::$app->formatter->asDate(strtotime('first day of previous month', strtotime($form->from_date)),
                                             'dd-MM-yyyy');
        }
        if($form->compare_to_date == null) {
            $form->compare_to_date = Yii::$app->formatter->asDate(strtotime($form->to_date), 'dd-MM-yyyy');
        }
        $form->tenant_id = $form_switch->tenant_id;
        $form->meter_id = $form_switch->meter_id;
        $form->channel_id = $form_switch->channel_id;
        $data_provider = $form->generateDataProvider();
        $compared_data_provider = $form->generateComparedDataProvider();
        if(Yii::$app->request->isPjax) {
            return $this->renderAjax('history_consumption', [
                'user' => $user,
                'form' => $form,
                'form_switch' => $form_switch,
                'data_provider' => $data_provider,
                'compared_data_provider' => $compared_data_provider,
            ]);
        }
        else {
            return $this->render('history_consumption', [
                'user' => $user,
                'form' => $form,
                'form_switch' => $form_switch,
                'data_provider' => $data_provider,
                'compared_data_provider' => $compared_data_provider,
            ]);
        }
    }


    public function actionReports() {
        $user = Yii::$app->user->identity;
        $session = Yii::$app->session;
        $form_switch = new FormUserSwitch();
        $form_switch->scenario =
            ($user->role == Role::ROLE_TENANT) ? FormUserSwitch::SCENARIO_TENANT : FormUserSwitch::SCENARIO_DEFAULT;
        if($form_switch->load(Yii::$app->request->get())) {
            if($form_switch->validate()) {
                $session->set('switch', [
                    'client_id' => $form_switch->client_id,
                    'site_id' => $form_switch->site_id,
                    'tenant_id' => $form_switch->tenant_id,
                    'meter_id' => null,
                    'channel_id' => null,
                ]);
            }
        }
        else {
            $form_switch->client_id = ArrayHelper::getValue($user->getSelectedClient(), 'id');
            $form_switch->site_id = ArrayHelper::getValue($user->getSelectedSite(), 'id');
            $form_switch->tenant_id =
                ($user->role == Role::ROLE_TENANT) ? ArrayHelper::getValue($user->getSelectedTenant(), 'id') : null;
        }
        $search = new SearchReport();
        $data_provider = $search->search();
        switch($user->role) {
            case Role::ROLE_SITE:
                $data_provider->query->andWhere([Report::tableName() . '.site_id' => $form_switch->site_id]);
                $data_provider->query->andWhere([Report::tableName() . '.level' => Report::LEVEL_SITE]);
                break;
            case Role::ROLE_TENANT:
                $data_provider->query->andWhere([Report::tableName() . '.site_id' => $form_switch->site_id]);
                $data_provider->query->andWhere([Report::tableName() . '.level' => Report::LEVEL_TENANT]);
                break;
            case Role::ROLE_CLIENT:
            default:
                $data_provider->query->andWhere([Report::tableName() . '.site_id' => $form_switch->site_id]);
                $data_provider->query->andWhere([Report::tableName() . '.level' => Report::LEVEL_SITE]);
                break;
        }
        $data_provider->query->andWhere([Report::tableName() . '.is_public' => true]);
        $filter_model = $search->filter();
        if(Yii::$app->request->isPjax) {
            return $this->renderAjax('reports', [
                'user' => $user,
                'form_switch' => $form_switch,
                'data_provider' => $data_provider,
                'filter_model' => $filter_model,
            ]);
        }
        else {
            return $this->render('reports', [
                'user' => $user,
                'form_switch' => $form_switch,
                'data_provider' => $data_provider,
                'filter_model' => $filter_model,
            ]);
        }
    }


    public function actionExportExcel($from_date, $to_date, $drilldown) {
        $user = Yii::$app->user->identity;
        $form = new FormHistoryConsumption();
        $form->from_date = $from_date;
        $form->to_date = $to_date;
        $form->drilldown = $drilldown;
        $form->tenant_id = ArrayHelper::getValue($user->getSelectedTenant(), 'id');
        $form->meter_id = ArrayHelper::getValue($user->getSelectedMeter(), 'id');
        $form->channel_id = ArrayHelper::getValue($user->getSelectedChannel(), 'id');
        $data_provider = $form->generateDataProvider();
        if(($items = $data_provider->getModels()) != null) {
            $excel = new \common\components\data\ExcelView();
            $objPHPExcel = $excel->getObjPHPExcel();
            $objPHPExcelActiveSheet = $objPHPExcel->getActiveSheet();
            $r = 1;
            $headColumns = [
                Yii::t('frontend.view', 'Date'),
                Yii::t('frontend.view', 'Pisga Kwh'),
                Yii::t('frontend.view', 'Geva Kwh'),
                Yii::t('frontend.view', 'Shefel Kwh'),
                Yii::t('frontend.view', 'Max demand'),
                Yii::t('frontend.view', 'Kvar/h'),
            ];
            foreach($headColumns as $headColumn => $headValue) {
                $objPHPExcelActiveSheet->setCellValue(\common\components\data\ExcelView::columnName($headColumn + 1) .
                                                      $r, $headValue);
                $objPHPExcelActiveSheet->getStyle(\common\components\data\ExcelView::columnName($headColumn + 1) . $r)
                                       ->applyFromArray([
                                                            'font' => [
                                                                'size' => 10,
                                                            ],
                                                            'fill' => [
                                                                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                                                'color' => ['rgb' => 'e2e2e2'],
                                                            ],
                                                            'borders' => [
                                                                'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                                                            ],
                                                            'alignment' => [
                                                                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                            ],
                                                        ]);
                $objPHPExcelActiveSheet->getColumnDimension(\common\components\data\ExcelView::columnName($headColumn +
                                                                                                          1))
                                       ->setAutoSize(true);
            }
            $r++;
            foreach($items as $item) {
                $rowColumns = [
                    $item['date'],
                    Yii::$app->formatter->asNumberFormat($item['pisga']),
                    Yii::$app->formatter->asNumberFormat($item['geva']),
                    Yii::$app->formatter->asNumberFormat($item['shefel']),
                    Yii::$app->formatter->asNumberFormat($item['max_demand']),
                    Yii::$app->formatter->asNumberFormat($item['kvar']),
                ];
                foreach($rowColumns as $rowColumn => $rowValue) {
                    $objPHPExcelActiveSheet->setCellValue(\common\components\data\ExcelView::columnName($rowColumn +
                                                                                                        1) . $r,
                                                          $rowValue);
                    $objPHPExcelActiveSheet->getStyle(\common\components\data\ExcelView::columnName($rowColumn + 1) .
                                                      $r)->applyFromArray([
                                                                              'font' => [
                                                                                  'size' => 10,
                                                                              ],
                                                                              'borders' => [
                                                                                  'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],
                                                                              ],
                                                                              'alignment' => [
                                                                                  'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                                                              ],
                                                                          ]);
                    $objPHPExcelActiveSheet->getColumnDimension(\common\components\data\ExcelView::columnName($rowColumn +
                                                                                                              1))
                                           ->setAutoSize(true);
                }
                $r++;
            }
            $objWriter = \PHPExcel_IOFactory::createWriter($excel->generateObjPHPExcel(), 'Excel5');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="history.consumption.' . time() . '.xls"');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
        }
        return $this->goBackReferrer();
    }


    public function actionError() {
        if(($exception = Yii::$app->getErrorHandler()->exception) === null) {
            // action has been invoked not from error handler, but by direct route, so we display '404 Not Found'
            $exception = new HttpException(404);
        }
        if($exception instanceof HttpException) {
            $code = $exception->statusCode;
        }
        else {
            $code = $exception->getCode();
        }
        if($exception instanceof Exception) {
            $name = $exception->getName();
        }
        else {
            $name = Yii::t('frontend.controller', 'Error');
        }
        if($code) {
            $name .= " (#$code)";
        }
        if($exception instanceof UserException) {
            $message = $exception->getMessage();
        }
        else {
            $message = Yii::t('frontend.controller', 'An internal server error occurred.');
        }
        Yii::getLogger()->log(json_encode(['message' => $message, 'code' => $code, 'file' => $exception->getFile(),
                                           'line' => $exception->getLine(), 'trace' => $exception->getTrace()]),
                              Logger::LEVEL_ERROR, 'application');
        if(Yii::$app->getRequest()->getIsAjax()) {
            return "$name: $message";
        }
        else {
            if(Yii::$app->user->isGuest) {
                $this->layout = 'site/auth';
            }
            return $this->render('error', [
                'name' => $name,
                'code' => $code,
                'message' => $message,
                'exception' => $exception,
            ]);
        }
    }


    /**
     * Build real time data
     *
     * @param \common\models\Site $site
     * @param \common\models\Meter $meter
     * @param \common\models\MeterChannel $channel
     * @param array $metmon
     * @return array
     */
    protected function buildRealTime(Site $site, Meter $meter, MeterChannel $channel, $metmon = []) {
        $interval = 5; // 30 min.
        $realtime = [];
        $cache = Yii::$app->cache;
        $incoming_temp = ArrayHelper::getValue($metmon, 'incoming_temp');
        $outgoing_temp = ArrayHelper::getValue($metmon, 'outgoing_temp');
        $cubic_meter = ArrayHelper::getValue($metmon, 'cubic_meter');
        $cubic_meter_hour = ArrayHelper::getValue($metmon, 'cubic_meter_hour');
        $kilowatt = ArrayHelper::getValue($metmon, 'kilowatt');
        $kilowatt_hour = ArrayHelper::getValue($metmon, 'kilowatt_hour');
        if($incoming_temp != null || $outgoing_temp != null || $cubic_meter != null || $cubic_meter_hour != null || $kilowatt != null || $kilowatt_hour != null) {
            $suffix = "{$site->id}-{$meter->name}-{$channel->channel}";
            $data = $cache->get('realtime_' . Yii::$app->user->id, []);
            $incoming_temp_date = ArrayHelper::getValue($incoming_temp, 'date');
            $outgoing_temp_date = ArrayHelper::getValue($outgoing_temp, 'date');
            $cubic_meter_date = ArrayHelper::getValue($cubic_meter, 'date');
            $cubic_meter_hour_date = ArrayHelper::getValue($cubic_meter_hour, 'date');
            $kilowatt_date = ArrayHelper::getValue($kilowatt, 'date');
            $kilowatt_hour_date = ArrayHelper::getValue($kilowatt_hour, 'date');

            if($incoming_temp != null && $incoming_temp_date != null) {

                if(ArrayHelper::getValue($data, "incoming_temp.{$incoming_temp['date']}") == null) {

                    if(!empty($data['incoming_temp'])) {
                        $data['incoming_temp'] =
                            array_filter($data['incoming_temp'], function ($item) use ($incoming_temp, $interval) {
                                return ($incoming_temp['date'] - $item['date'] <= $interval);
                            });
                    }
                    if (count($data['incoming_temp']) >= 23) {
                        array_shift($data['incoming_temp']);
                    }

                    $data['incoming_temp'][$incoming_temp['date']] = $incoming_temp;
                }
            }
            if($outgoing_temp != null && $outgoing_temp_date != null) {
                if(ArrayHelper::getValue($data, "outgoing_temp.{$outgoing_temp['date']}") == null) {
                    if(!empty($data['outgoing_temp'])) {
                        $data['outgoing_temp'] =
                            array_filter($data['outgoing_temp'], function ($item) use ($outgoing_temp, $interval) {
                                return ($outgoing_temp['date'] - $item['date'] <= $interval);
                            });
                    }
                    /*while (count($data['outgoing_temp']) > 3) {
                        $data['outgoing_temp'] = array_shift($data['outgoing_temp']);
                    }
                    */
                    if (count($data['outgoing_temp']) >= 23) {
                        array_shift($data['outgoing_temp']);
                    }
                    $data['outgoing_temp'][$outgoing_temp['date']] = $outgoing_temp;
                }
            }
            if($cubic_meter != null && $cubic_meter_date != null) {
                if(ArrayHelper::getValue($data, "cubic_meter.{$cubic_meter['date']}") == null) {
                    if(!empty($data['cubic_meter'])) {
                        $data['cubic_meter'] =
                            array_filter($data['cubic_meter'], function ($item) use ($cubic_meter, $interval) {
                                return ($cubic_meter['date'] - $item['date'] <= $interval);
                            });
                    }
                    if (count($data['cubic_meter']) >= 23) {
                        array_shift($data['cubic_meter']);
                    }
                    $data['cubic_meter'][$cubic_meter['date']] = $cubic_meter;
                }
            }
            if($cubic_meter_hour != null && $cubic_meter_hour_date != null) {
                if(ArrayHelper::getValue($data, "cubic_meter_hour.{$cubic_meter_hour['date']}") == null) {
                    if(!empty($data['cubic_meter_hour'])) {
                        $data['cubic_meter_hour'] =
                            array_filter($data['cubic_meter_hour'], function ($item) use ($cubic_meter_hour, $interval) {
                                return ($cubic_meter_hour['date'] - $item['date'] <= $interval);
                            });
                    }
                    if (count($data['cubic_meter_hour']) >= 23) {
                        array_shift($data['cubic_meter_hour']);
                    }
                    $data['cubic_meter_hour'][$cubic_meter_hour['date']] = $cubic_meter_hour;
                }
            }
            if($kilowatt != null && $kilowatt_date != null) {
                if(ArrayHelper::getValue($data, "kilowatt.{$kilowatt['date']}") == null) {
                    if(!empty($data['kilowatt'])) {
                        $data['kilowatt'] =
                            array_filter($data['kilowatt'], function ($item) use ($kilowatt, $interval) {
                                return ($kilowatt['date'] - $item['date'] <= $interval);
                            });
                    }
                    if (count($data['kilowatt']) >= 23) {
                        array_shift($data['kilowatt']);
                    }
                    $data['kilowatt'][$kilowatt['date']] = $kilowatt;
                }
            }
            if($kilowatt_hour != null && $kilowatt_hour_date != null) {
                if(ArrayHelper::getValue($data, "kilowatt_hour.{$kilowatt_hour['date']}") == null) {
                    if(!empty($data['kilowatt_hour'])) {
                        $data['kilowatt_hour'] =
                            array_filter($data['kilowatt_hour'], function ($item) use ($kilowatt_hour, $interval) {
                                return ($kilowatt_hour['date'] - $item['date'] <= $interval);
                            });
                    }
                    if (count($data['kilowatt_hour']) >= 23) {
                        array_shift($data['kilowatt_hour']);
                    }
                    $data['kilowatt_hour'][$kilowatt_hour['date']] = $kilowatt_hour;
                }
            }
            $cache->set('realtime_' . Yii::$app->user->id, $data, 2 * 60 * 60);
            if(!empty($data)) {
                if(!empty($data['incoming_temp'])) {
                    $data['incoming_temp'] = array_values($data['incoming_temp']);
                }
                if(!empty($data['outgoing_temp'])) {
                    $data['outgoing_temp'] = array_values($data['outgoing_temp']);
                }
                if(!empty($data['cubic_meter'])) {
                    $data['cubic_meter'] = array_values($data['cubic_meter']);
                }
                if(!empty($data['cubic_meter_hour'])) {
                    $data['cubic_meter_hour'] = array_values($data['cubic_meter_hour']);
                }
                if(!empty($data['kilowatt'])) {
                    $data['kilowatt'] = array_values($data['kilowatt']);
                }
                if(!empty($data['kilowatt_hour'])) {
                    $data['kilowatt_hour'] = array_values($data['kilowatt_hour']);
                }
                $realtime = $data;
            }
        }

        return $realtime;
    }


    protected function buildMetmonUrl(Site $site, Meter $meter, MeterChannel $channel) {
        $ip = $meter->getIpAddress();
        $port = '10203';
        $meter_id = $meter->name;
        $meter_type = $meter->relationMeterType;
        $meter_type_name = strtolower($meter_type->name);
        $phases = $meter_type->phases;
        $auth = '';
        if($phases > 1) {
            $channel_id = implode(',', ArrayHelper::map($channel->relationMeterSubchannels, 'id', 'channel'));
            $criteria_meter_types = ['qng3', 'mc4', 'mc5', 'rsm4', 'rsm5'];
            foreach($criteria_meter_types as $criteria_meter_type) {
                if(strpos($meter_type_name, $criteria_meter_type) !== false) {
                    $channel_id = $channel->channel;
                    break;
                }
            }
        }
        else {
            $channel_id = $channel->channel;
        }

        return "http://$ip:$port/rt?" . urldecode(http_build_query([
                                                                       'meter_id' => $meter_id,
                                                                       'channel' => $channel_id,
                                                                       'auth' => $auth,
                                                                   ], '', '&', PHP_QUERY_RFC3986));
    }
}
