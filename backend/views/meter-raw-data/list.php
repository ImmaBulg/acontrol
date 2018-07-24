<?php
use backend\models\forms\FormMeterRawDataFilter;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use yii\helpers\Url;
use common\components\i18n\Formatter;
use kartik\datetime\DateTimePicker;

/**@var $model \common\models\MeterChannel */
$this->title = Yii::t('backend.view', 'Channel raw data management {meter} - {channel}', [
    'meter' => $model->relationMeter->name,
    'channel' => $model->channel,
]);
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('backend.view', 'Meters'),
    'url' => ['/meter/list'],
];
$this->params['breadcrumbs'][] = [
    'label' => $model->relationMeter->name,
    'url' => ['/meter/edit', 'id' => $model->relationMeter->id],
];
$this->params['breadcrumbs'][] = [
    'label' => Yii::t('backend.view', 'Meter channels'),
    'url' => ['/meter-channel/list', 'id' => $model->relationMeter->id],
];
$this->params['breadcrumbs'][] = [
    'label' => $model->relationMeterChannel->channel,
    'url' => ['/meter-channel/edit', 'id' => $model->relationMeterChannel->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Channel raw data management', [
    'meter' => $model->relationMeter->name,
    'channel' => $model->channel,
]);
$avg_data = $form_avg->getAvgData();
?>
    <div class="page-header">
        <?php echo FormMeterRawDataFilter::getGoBackLink(['class' => 'btn btn-info pull-right']); ?>
        <h1><?php echo $this->title; ?></h1>
    </div>
<?php $form_active = ActiveForm::begin([
                                           'id' => 'form-meter-raw-data-filter',
                                           'enableOneProcessSubmit' => true,
                                           'method' => 'GET',
                                           'action' => ['/meter-raw-data/list',
                                                        'meter_id' => $model->relationMeter->name,
                                                        'channel_id' => $model->channel],
                                       ]); ?>
    <fieldset>
        <div class="row">
            <div class="col-lg-3">
                <?php echo $form_active->field($form_filter, 'period')->widget(Select2::classname(), [
                    'data' => FormMeterRawDataFilter::getListPeriods(),
                    'options' => [
                        'options' => FormMeterRawDataFilter::getListPeriodAttributes(),
                    ],
                ]); ?>
            </div>
            <div class="col-lg-3">
                <!--<div class="form-group">
                    <label class="control-label">From</label>

                </div>-->
                <?php echo $form_active->field($form_filter, 'from_date')
                    ->widget(DateTimePicker::className(), [
                        'name' => 'from_date',
                        'type' => DateTimePicker::TYPE_INPUT,
                        'options' => ['placeholder' => 'Input date'],
                        'convertFormat' => true,
                        'value' => date(Yii::$app->formatter->asDatetime($form_filter->from_date, 'dd-MM-Y HH:mm')),
                        'pluginOptions' => [
                            'format' => Formatter::PHP_DATE_TIME_FORMAT,
                            'autoclose'=>true,
                            'weekStart'=>1, //неделя начинается с понедельника
                            'startDate' => '01.05.2010 00:00', //самая ранняя возможная дата
                            'todayBtn'=>true, //снизу кнопка "сегодня"
                            'minView' => 1,
                        ],
                    ]); ?>
                <?php /*echo $form_active->field($form_filter, 'from_date')->dateInput(); */?>
            </div>
            <div class="col-lg-3">
                <?php echo $form_active->field($form_filter, 'to_date')
                    ->widget(DateTimePicker::className(), [
                        'name' => 'to_date',
                        'type' => DateTimePicker::TYPE_INPUT,
                        'options' => ['placeholder' => 'Input date'],
                        'convertFormat' => true,
                        'value'=> date(Formatter::PHP_DATE_TIME_FORMAT,(integer) $form_filter->to_date),
                        'pluginOptions' => [
                            'format' => Formatter::PHP_DATE_TIME_FORMAT,
                            'autoclose'=>true,
                            'weekStart'=>1, //неделя начинается с понедельника
                            'startDate' => '01.05.2010 00:00', //самая ранняя возможная дата
                            'todayBtn'=>true, //снизу кнопка "сегодня"
                            'minView' => 1,
                        ],
                    ]); ?>
                <?php /*echo $form_active->field($form_filter, 'to_date')->dateInput(); */?>
            </div>
            <div class="col-lg-3">
                <?php echo Html::submitInput(Yii::t('backend.view', 'Search'),
                                             ['class' => 'btn btn-primary control-label-offset']); ?>
                <?php /*echo Html::a(Yii::t('backend.view', 'Delete all'),
                    ['/meter-raw-data/delete', 'meter_id' => $model->relationMeter->name,
                        'channel_id' => $model->channel], [
                        'class' => 'btn btn-warning control-label-offset',
                        'style' => 'float: right;',
                        'data' => [
                            'toggle' => 'confirm',
                            'confirm-post' => true,
                            'confirm-text' => Yii::t('backend.view',
                                'Are you sure you want to delete all these data?'),
                            'confirm-button' => Yii::t('backend.view', 'Delete'),
                        ],
                    ]); *///@Todo delete this button when "raw data avg form is back"?>
                <?php echo $form_active->field($form_filter, 'go_back_source')->hiddenInput()->label(false); ?>
                <?php echo $form_active->field($form_filter, 'go_back_url')->hiddenInput()->label(false); ?>
            </div>
        </div>
    </fieldset>
<?php ActiveForm::end(); ?>

<?php
$field_period = Html::getInputId($form_filter, 'period');
$from_date = Html::getInputId($form_filter, 'from_date');
$to_date = Html::getInputId($form_filter, 'to_date');
$script = <<< JS
$('#$field_period').on('change', function(){
	var form = $(this).parents('form');
	var period = $.parseJSON(this.options[this.selectedIndex].getAttribute('data-period'));
	form.find('#$from_date').val(period.from_date);
	form.find('#$to_date').val(period.to_date);
});
JS;
$this->registerJs($script);
?>

    <div class="row" id="form-meter-raw-data-avg-fields-hide">
        <div class="col-lg-6">
            <?php $form_active = ActiveForm::begin([
                                                       'id' => 'form-meter-raw-data-avg',
                                                       'enableOneProcessSubmit' => true,
                                                       'method' => 'GET',
                                                   ]); ?>
            <fieldset>
                <div class="row">
                    <div class="col-lg-3">
                        <?php echo $form_active->field($form_avg, 'period_from')
                            ->widget(DateTimePicker::className(), [
                                'name' => 'period_from',
                                'type' => DateTimePicker::TYPE_INPUT,
                                'options' => ['placeholder' => 'Input date'],
                                'convertFormat' => true,
                                'value' => date(Formatter::PHP_DATE_TIME_FORMAT,(integer) $form_avg->period_from),
                                'pluginOptions' => [
                                    'format' => Formatter::PHP_DATE_TIME_FORMAT,
                                    'autoclose'=> true,
                                    'weekStart'=> 1, //неделя начинается с понедельника
                                    'startDate' => '01.05.2010 00:00', //самая ранняя возможная дата
                                    'todayBtn'=> true, //снизу кнопка "сегодня"
                                    'minView' => 1,
                                    //'minuteStep' => 60,
                                ],
                            ]); ?>
                    </div>
                    <?php date(Formatter::PHP_DATE_TIME_FORMAT,(integer) $form_avg)?>
                    <div class="col-lg-3">
                        <?php echo $form_active->field($form_avg, 'period_to')
                            ->widget(DateTimePicker::className(), [
                                'name' => 'period_to',
                                'type' => DateTimePicker::TYPE_INPUT,
                                'options' => ['placeholder' => 'Input date'],
                                'convertFormat' => true,
                                'value'=> date(Formatter::PHP_DATE_TIME_FORMAT,(integer) $form_avg->period_to),
                                'pluginOptions' => [
                                    'format' => Formatter::PHP_DATE_TIME_FORMAT,
                                    'autoclose'=>true,
                                    'weekStart'=>1, //неделя начинается с понедельника
                                    'startDate' => '01.05.2010 00:00', //самая ранняя возможная дата
                                    'todayBtn'=>true, //снизу кнопка "сегодня"
                                    'minView' => 1,
                                    'minuteStep' => 60,
                                ],
                            ]); ?>
                    </div>
                    <div class="col-lg-3">
                        <?php echo $form_active->field($form_avg, 'direction')->widget(Select2::classname(), [
                            'data' => [Yii::t('backend.view', 'Backward'), Yii::t('backend.view', 'Forward')],
                        ]); ?>
                    </div>
                    <div class="col-lg-3">
                        <?php echo Html::submitInput(Yii::t('backend.view', 'Run rule'),
                                                     ['class' => 'btn btn-primary btn-block control-label-offset']); ?>
                    </div>
                </div>
            </fieldset>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="col-lg-6">
                <?php if(Yii::$app->request->getQueryParam('avg', false)): ?>
                    <?php echo Html::a(Yii::t('backend.view', 'Disable apply AVG'), Url::current(['avg' => false]), [
                        'class' => 'btn btn-default control-label-offset',
                    ]); ?>
                <?php else: ?>
                    <?php echo Html::a(Yii::t('backend.view', 'Apply AVG'), Url::current(['avg' => true]), [
                        'class' => 'btn btn-default control-label-offset',
                    ]); ?>
                <?php endif; ?>
                <?php echo Html::a(Yii::t('backend.view', 'Delete all'),
                                   ['/meter-raw-data/delete', 'meter_id' => $model->relationMeter->name,
                                    'channel_id' => $model->channel], [
                                       'class' => 'btn btn-warning control-label-offset',
                                       'data' => [
                                           'toggle' => 'confirm',
                                           'confirm-post' => true,
                                           'confirm-text' => Yii::t('backend.view',
                                                                    'Are you sure you want to delete all these data?'),
                                           'confirm-button' => Yii::t('backend.view', 'Delete'),
                                       ],
                                   ]); ?>
        </div>
    </div>

<?php $form_active = ActiveForm::begin([
                                           'id' => 'form-meter-raw-data',
                                           'enableOneProcessSubmit' => true,
                                       ]); ?>
<?php echo $form_active->errorSummary($form); ?>
<?php
$this->registerJsFile('@web/js/plugins/sticky.js');
$this->registerJs("jQuery('#sticky-meter-raw-data-head').sticky({topSpacing:0});");
?>
<?php if($model->relationMeter->type === \common\models\Meter::TYPE_AIR): ?>
    <?= $this->render('air-list', ['model' => $model, 'form' => $form,
                                   'form_active' => $form_active,
                                   'form_filter' => $form_filter,
                                   'form_avg' => $form_avg,
                                   'data_provider' => $data_provider,
                                   'showAvg' => $showAvg,]) ?>
<?php else : ?>
    <?= $this->render('electricity-list', ['model' => $model, 'form' => $form,
                                           'form_active' => $form_active,
                                           'form_filter' => $form_filter,
                                           'form_avg' => $form_avg,
                                           'data_provider' => $data_provider,
                                            'showAvg' => $showAvg,]) ?>
<?php endif ?>
<?php ActiveForm::end(); ?>