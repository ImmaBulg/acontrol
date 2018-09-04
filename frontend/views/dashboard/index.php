<?php

use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\ActiveForm;
use common\widgets\chart\Chart;
use frontend\widgets\chart\GaugeChart;

$this->title = Yii::t('frontend.view', 'Real time');
?>
<?php echo Html::beginTag('div', ['class' => 'wrap', 'id' => ($realtime != null) ? 'realtime-enabled' : 'realtime-disabled']); ?>
    <div id="realtime-page" class="wrap">
        <?php echo $this->render('_header'); ?>
        <div id="main">
            <div class="container">
                <div>URL - <?php echo $metmon_url; ?></div>
                <div class="col-lg-12">
                    <?php echo $this->render('_switch', [
                        'action' => ['index'],
                        'user' => $user,
                        'form' => $form_switch,
                        'show_clients' => true,
                        'show_sites' => true,
                        'show_tenants' => true,
                        'show_meters' => true,
                        'show_channels' => true,
                    ]); ?>
                    <?php //if ($metmon_url): ?>
                    <!--div class="alert alert-info">
							<?php //echo Html::a($metmon_url, $metmon_url, ['target' => '_blank', 'class' => 'alert-link']); ?>
						</div-->
                    <?php //endif; ?>
                    <h1 class="page-header"><?php echo Yii::t('frontend.view', 'Real time'); ?></h1>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="box-total">
                                <div class="inner">
									<span>
										<?php echo Yii::t('frontend.view', 'Cubic meter per hour'); ?>
									</span>
                                    <strong class="cubic_meter_hour">
                                        <?php echo Yii::t('frontend.view', '{value} m³/h', [
                                            'value' => ArrayHelper::getValue($metmon, 'cubic_meter_hour.value', 0),
                                        ]);  ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="box-total">
                                <div class="inner">
									<span>
										<?php echo Yii::t('frontend.view', 'Kilowatt hour'); ?>
									</span>
                                    <strong class="kilowatt_hour">
                                        <?php echo Yii::t('frontend.view', '{value} Kwh', [
                                            'value' => ArrayHelper::getValue($metmon, 'kilowatt_hour.value', 0),
                                        ]);  ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    <div class="row">
                        <?php
                        $data = ArrayHelper::getValue($realtime, 'incoming_temp', []);
                        $incoming_temp = [];
                        foreach ($data as $dt) {
                            $incoming_temp[] = [strtotime($dt['date']) * 1000, (float)$dt['value']];

                        }
                        $data = ArrayHelper::getValue($realtime, 'outgoing_temp', []);
                        $outgoing_temp = [];
                        foreach ($data as $dt) {
                            $outgoing_temp[] = [strtotime($dt['date']) * 1000, (float)$dt['value']];
                        }
                        ?>
                        <?php echo Chart::widget([
                            'id' => "metmon-area-temp",
                            'clientOptions' => [
                                'chart' => [
                                    'type' => 'area',
                                    'zoomType' => 'x',
                                ],
                                'title' => ['text' => Yii::t('frontend.view', 'Incoming & Outgoing temperatures')],
                                'legend' => ['enabled' => false],
                                'xAxis' => [
                                    'type' => 'datetime',
                                    'title' => [
                                        'text' => 'Time'
                                    ],
                                    'labels' => [
                                        'formatter' => new JsExpression("function () {
                                            var time = (new Date(this.value)).toUTCString().split(' ')[4];
                                            return time.split(':')[0] + ':' + time.split(':')[1] }"),
                                    ],
                                ],
                                'yAxis' => [
                                    'title' => [
                                        'text' => 'Temperatures',
                                    ],
                                    'labels' => [
                                        'formatter' => new JsExpression("function () {
                                            return '' + this.value + ' ' + ' C°'}"),
                                    ],
                                ],
                                'plotOptions' => [
                                    'area' => [
                                        'marker' => ['radius' => 2],
                                        'lineWidth' => 1,
                                        'threshold' => null,
                                        'states' => ['hover' => ['lineWidth' => 1]],
                                    ],
                                ],
                                'series' => [
                                    [
                                        'name' => Yii::t('frontend.view', 'Incoming temperatures'),
                                        'color' => '#4286f4',
                                        'fillOpacity' => 0.2,
                                        'data' => $incoming_temp,
                                    ],
                                    [
                                        'name' => Yii::t('frontend.view', 'Outgoing temperatures'),
                                        'color' => '#f44a41',
                                        'fillOpacity' => 0.2,
                                        'data' => $outgoing_temp,
                                    ],
                                ],
                            ],
                        ]); ?>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <?php
                            $data = ArrayHelper::getValue($realtime, 'cubic_meter', []);
                            $cubic_meter = [];
                            foreach ($data as $dt) {
                                $cubic_meter[] = [strtotime($dt['date']) * 1000, (float)$dt['value']];

                            }
                            ?>
                            <?php echo Chart::widget([
                                'id' => "metmon-area-cubic_meter",
                                'clientOptions' => [
                                    'chart' => [
                                        'type' => 'area',
                                        'zoomType' => 'x',
                                    ],
                                    'title' => ['text' => Yii::t('frontend.view', 'Flow')],
                                    'legend' => ['enabled' => false],
                                    'xAxis' => [
                                        'type' => 'datetime',
                                        'title' => [
                                            'text' => 'Time'
                                        ],
                                        'labels' => [
                                            'formatter' => new JsExpression("function () {
                                            var time = (new Date(this.value)).toUTCString().split(' ')[4];
                                            return time.split(':')[0] + ':' + time.split(':')[1] }"),
                                        ],
                                    ],
                                    'yAxis' => [
                                        'labels' => [
                                            'formatter' => new JsExpression("function () {
                                            return '' + this.value + ' ' + ' m³'}"),
                                        ],
                                    ],
                                    'plotOptions' => [
                                        'area' => [
                                            'marker' => ['radius' => 2],
                                            'lineWidth' => 1,
                                            'threshold' => null,
                                            'states' => ['hover' => ['lineWidth' => 1]],
                                        ],
                                    ],
                                    'series' => [
                                        [
                                            'name' => Yii::t('frontend.view', 'Cubic meter'),
                                            'color' => '#4286f4',
                                            'fillOpacity' => 0.2,
                                            'data' => $cubic_meter,
                                        ],
                                    ],
                                ],
                            ]); ?>
                        </div>
                        <div class="col-lg-6">
                            <?php
                            $data = ArrayHelper::getValue($realtime, 'cubic_meter_hour', []);
                            $cubic_meter_hour = [];
                            foreach ($data as $dt) {
                                $cubic_meter_hour[] = [strtotime($dt['date']) * 1000, (float)$dt['value']];

                            }
                            ?>
                            <?php echo Chart::widget([
                                'id' => "metmon-area-cubic_meter_hour",
                                'clientOptions' => [
                                    'chart' => [
                                        'type' => 'area',
                                        'zoomType' => 'x',
                                    ],
                                    'title' => ['text' => Yii::t('frontend.view', 'Air energy')],
                                    'legend' => ['enabled' => false],
                                    'xAxis' => [
                                        'type' => 'datetime',
                                        'title' => [
                                            'text' => 'Time'
                                        ],
                                        'labels' => [
                                            'formatter' => new JsExpression("function () {
                                            var time = (new Date(this.value)).toUTCString().split(' ')[4];
                                            return time.split(':')[0] + ':' + time.split(':')[1] }"),
                                        ],
                                    ],
                                    'yAxis' => [
                                        'labels' => [
                                            'formatter' => new JsExpression("function () {
                                            return '' + this.value + ' ' + ' m³/h'}"),
                                        ],
                                    ],
                                    'plotOptions' => [
                                        'area' => [
                                            'marker' => ['radius' => 2],
                                            'lineWidth' => 1,
                                            'threshold' => null,
                                            'states' => ['hover' => ['lineWidth' => 1]],
                                        ],
                                    ],
                                    'series' => [
                                        [
                                            'name' => Yii::t('frontend.view', 'Cubic meter hour'),
                                            'color' => '#4286f4',
                                            'fillOpacity' => 0.2,
                                            'data' => $cubic_meter_hour,
                                        ],
                                    ],
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <?php
                            $data = ArrayHelper::getValue($realtime, 'kilowatt', []);
                            $kilowatt = [];
                            foreach ($data as $dt) {
                                $kilowatt[] = [strtotime($dt['date']) * 1000, (float)$dt['value']];

                            }
                            ?>
                            <?php echo Chart::widget([
                                'id' => "metmon-area-kilowatt",
                                'clientOptions' => [
                                    'chart' => [
                                        'type' => 'area',
                                        'zoomType' => 'x',
                                    ],
                                    'title' => ['text' => Yii::t('frontend.view', 'Power')],
                                    'legend' => ['enabled' => false],
                                    'xAxis' => [
                                        'type' => 'datetime',
                                        'title' => [
                                            'text' => 'Time'
                                        ],
                                        'labels' => [
                                            'formatter' => new JsExpression("function () {
                                            var time = (new Date(this.value)).toUTCString().split(' ')[4];
                                            return time.split(':')[0] + ':' + time.split(':')[1] }"),
                                        ],
                                    ],
                                    'yAxis' => [
                                        'labels' => [
                                            'formatter' => new JsExpression("function () {
                                            return '' + this.value + ' ' + ' KW'}"),
                                        ],
                                    ],
                                    'plotOptions' => [
                                        'area' => [
                                            'marker' => ['radius' => 2],
                                            'lineWidth' => 1,
                                            'threshold' => null,
                                            'states' => ['hover' => ['lineWidth' => 1]],
                                        ],
                                    ],
                                    'series' => [
                                        [
                                            'name' => Yii::t('frontend.view', 'Kilowatt'),
                                            'color' => '#4286f4',
                                            'fillOpacity' => 0.2,
                                            'data' => $kilowatt,
                                        ],
                                    ],
                                ],
                            ]); ?>
                        </div>
                        <div class="col-lg-6">
                            <?php
                            $data = ArrayHelper::getValue($realtime, 'kilowatt_hour', []);
                            $kilowatt_hour = [];
                            foreach ($data as $dt) {
                                $kilowatt_hour[] = [strtotime($dt['date']) * 1000, (float)$dt['value']];

                            }
                            ?>
                            <?php echo Chart::widget([
                                'id' => "metmon-area-kilowatt_hour",
                                'clientOptions' => [
                                    'chart' => [
                                        'type' => 'area',
                                        'zoomType' => 'x',
                                    ],
                                    'title' => ['text' => Yii::t('frontend.view', 'Electrical energy')],
                                    'legend' => ['enabled' => false],
                                    'xAxis' => [
                                        'type' => 'datetime',
                                        'title' => [
                                            'text' => 'Time'
                                        ],
                                        'labels' => [
                                            'formatter' => new JsExpression("function () {
                                            var time = (new Date(this.value)).toUTCString().split(' ')[4];
                                            return time.split(':')[0] + ':' + time.split(':')[1] }"),
                                        ],
                                    ],
                                    'yAxis' => [
                                        'labels' => [
                                            'formatter' => new JsExpression("function () {
                                            return '' + this.value + ' ' + ' KW/h'}"),
                                        ],
                                    ],
                                    'plotOptions' => [
                                        'area' => [
                                            'marker' => ['radius' => 2],
                                            'lineWidth' => 1,
                                            'threshold' => null,
                                            'states' => ['hover' => ['lineWidth' => 1]],
                                        ],
                                    ],
                                    'series' => [
                                        [
                                            'name' => Yii::t('frontend.view', 'Kilowatt hours'),
                                            'color' => '#4286f4',
                                            'fillOpacity' => 0.2,
                                            'data' => $kilowatt_hour,
                                        ],
                                    ],
                                ],
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php echo Html::endTag('div'); ?>