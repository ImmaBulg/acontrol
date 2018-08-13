<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 19.07.2017
 * Time: 15:04
 * @var $model \common\models\Tenant
 * @var $extra_model \common\models\Site|\common\models\User
 * @var $form_active ActiveForm
 * @var $form FormTenant
 */
use backend\models\forms\FormTenant;
use common\helpers\Html;
use common\models\Rate;
use common\models\Report;
use common\models\Site;
use common\models\Tenant;
use common\models\User;
use common\widgets\ActiveForm;
use common\widgets\DepDrop;
use common\widgets\Select2;
use dezmont765\yii2bundle\widgets\AutoRegistrableScriptBlock;
use kartik\time\TimePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;

$this->registerCssFile('@web/css/smarttime.css');
$this->registerJsFile('//ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js', [
    'position' => View::POS_END,
]);

$this->registerJsFile('@web/js/plugins/smarttime.js', [
    'position' => View::POS_END,
]);

$this->registerJsFile('@web/js/irregular_hours.js', [
    'position' => View::POS_END,
]);

$is_create = !isset($model);
$this->title = $is_create ? Yii::t('backend.view', 'Create a new tenant') :
    Yii::t('backend.view', 'Tenant - {name}', ['name' => $model->name]);
if ($is_create) {
    if (!isset($extra_model)) {
        $this->params['breadcrumbs'][] = [
            'label' => Yii::t('backend.view', 'Tenants'),
            'url' => ['/tenant/list'],
        ];
        $this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new tenant');
    } else {
        switch ($extra_model) {
            case $extra_model instanceof Site :
                $this->params['breadcrumbs'][] = [
                    'label' => Yii::t('backend.view', 'Clients'),
                    'url' => ['/client/list'],
                ];
                $this->params['breadcrumbs'][] = [
                    'label' => $extra_model->relationUser->name,
                    'url' => ['/client/view', 'id' => $extra_model->relationUser->id],
                ];
                $this->params['breadcrumbs'][] = [
                    'label' => Yii::t('backend.view', 'Sites'),
                    'url' => ['/client/sites', 'id' => $extra_model->relationUser->id],
                ];
                $this->params['breadcrumbs'][] = [
                    'label' => $extra_model->name,
                    'url' => ['/site/view', 'id' => $extra_model->id],
                ];
                $this->params['breadcrumbs'][] = [
                    'label' => Yii::t('backend.view', 'Tenants'),
                    'url' => ['/site/tenants', 'id' => $extra_model->id],
                ];
                break;
            case $extra_model instanceof User :
                $this->params['breadcrumbs'][] = [
                    'label' => Yii::t('backend.view', 'Clients'),
                    'url' => ['/client/list'],
                ];
                $this->params['breadcrumbs'][] = [
                    'label' => $extra_model->name,
                    'url' => ['/client/view', 'id' => $extra_model->id],
                ];
                $this->params['breadcrumbs'][] = [
                    'label' => Yii::t('backend.view', 'Tenants'),
                    'url' => ['/client/tenants', 'id' => $extra_model->id],
                ];
                $this->params['breadcrumbs'][] = Yii::t('backend.view', 'Create a new tenant');
                break;
        }
    }
} else {
    $this->params['breadcrumbs'][] = [
        'label' => Yii::t('backend.view', 'Clients'),
        'url' => ['/client/list'],
    ];
    $this->params['breadcrumbs'][] = [
        'label' => $model->relationUser->name,
        'url' => ['/client/view', 'id' => $model->relationUser->id],
    ];
    $this->params['breadcrumbs'][] = [
        'label' => Yii::t('backend.view', 'Sites'),
        'url' => ['/client/sites', 'id' => $model->relationUser->id],
    ];
    $this->params['breadcrumbs'][] = [
        'label' => $model->relationSite->name,
        'url' => ['/site/view', 'id' => $model->relationSite->id],
    ];
    $this->params['breadcrumbs'][] = [
        'label' => Yii::t('backend.view', 'Tenants'),
        'url' => ['/site/tenants', 'id' => $model->relationSite->id],
    ];
    $this->params['breadcrumbs'][] = $model->name;
}
?>
<?php if ($is_create): ?>
    <h1 class="page-header"><?php echo $this->title; ?></h1>
<?php else : ?>
    <?php echo $this->render('_tenant_menu', ['model' => $model]); ?>
<?php endif ?>
<div class="well">
    <ul class="nav nav-pills">
        <li class="active"><a data-toggle="pill" href="#edit-form">Main data</a></li>
        <li><a data-toggle="pill" href="#irregular-data">Irregular hours</a></li>
    </ul>
    <div class="tab-content">
        <div id="edit-form" class="tab-pane fade in active">
            <?php $form_active = ActiveForm::begin([
                'id' => 'tenant-form',
                'enableOneProcessSubmit' => true,
            ]); ?>
            <fieldset>
                <div class="row">
                    <div class="col-md-8">
                        <fieldset>
                            <legend><?php echo Yii::t('backend.tenant', 'Tenant settings'); ?></legend>
                            <div class="row">
                                <div class="col-lg-6">

                                    <?php echo $form_active->field($form, 'site_id')->widget(Select2::classname(), [
                                        'data' => Tenant::getListSites($extra_model instanceof \common\models\User ?
                                            $extra_model->id :
                                            null),
                                    ]); ?>
                                    <?php echo $form_active->field($form, 'name')->textInput(); ?>
                                    <?php echo $form_active->field($form, 'type')->widget(Select2::classname(), [
                                        'data' => Tenant::getListTypes(),
                                    ]); ?>
                                    <?php if ($is_create): ?>
                                        <?php echo $form_active->field($form, 'to_issue')->widget(DepDrop::classname(), [
                                            'type' => DepDrop::TYPE_SELECT2,
                                            'options' => [
                                                'data' => [
                                                    'included-reports' => FormTenant::getListToIssueIncludedReports(),
                                                ],
                                            ],
                                            'pluginOptions' => [
                                                'depends' => [Html::getInputId($form, 'site_id')],
                                                'url' => Url::to([
                                                    '/form-dependent/site-to-issues',
                                                ]),
                                                'initialize' => true,
                                                'placeholder' => false,
                                                'loadingText' => Yii::t('backend.view', 'Loading'),
                                            ],
                                        ]); ?>
                                    <?php else : ?>
                                        <?php echo $form_active->field($form, 'to_issue')->widget(Select2::classname(), [
                                            'data' => Site::getListToIssues(),
                                            'options' => [
                                                'data' => [
                                                    'included-reports' => FormTenant::getListToIssueIncludedReports(),
                                                ],
                                            ],
                                        ]); ?>
                                    <?php endif ?>

                                    <?php echo $form_active->field($form, 'rate_type_id')->widget(Select2::classname(), [
                                        'data' => ArrayHelper::merge(['' => Yii::t('backend.view', 'Not set')],
                                            Rate::getListRate()),
                                    ]); ?>
                                    <!--                            --><?php //echo $form_active->field($form, 'site_rate')->widget(DepDrop::classname(), [
                                    //                                'type' => DepDrop::TYPE_SELECT2,
                                    ////                                'disabled' => true,
                                    //                                'pluginOptions' => [
                                    //                                    'depends' => [Html::getInputId($form, 'site_id')],
                                    //                                    'url' => Url::to([
                                    //                                                         '/form-dependent/site-rates',
                                    //                                                     ]),
                                    //                                    'initialize' => true,
                                    //                                    'placeholder' => false,
                                    //                                    'loadingText' => Yii::t('backend.view', 'Loading'),
                                    //                                ],
                                    //                                'pluginEvents' => [
                                    //                                    "depdrop.change" => "function(event, id, value, count) {
                                    //								jQuery(event.currentTarget).prop('disabled', true);
                                    //							}",
                                    //                                ],
                                    //                            ])->label(false); ?>
                                    <?php echo $form_active->field($form, 'square_meters')
                                        ->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
                                    <?php echo $form_active->field($form, 'entrance_date')->dateInput(); ?>
                                    <?php echo $form_active->field($form, 'exit_date')->dateInput(); ?>
                                </div>
                                <div class="col-lg-6">
                                    <?php echo $form_active->field($form, 'comment')->textArea(); ?>
                                    <?php echo $form_active->field($form, 'fixed_payment')
                                        ->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
                                    <?php echo $form_active->field($form, 'site_fixed_payment')->textInput([
                                        'disabled' => true,
                                        'allow_only' => Html::TYPE_NUMBER,
                                    ])->label(false); ?>
                                    <?php echo $form_active->field($form, 'id_with_client')->textInput(); ?>
                                    <?php echo $form_active->field($form, 'accounting_number')->textInput(); ?>
                                    <?php echo $form_active->field($form, 'billing_content')->textArea(); ?>
                                    <?php echo $form_active->field($form, 'included_in_cop')->checkbox(); ?>
                                    <?php echo $form_active->field($form, 'included_reports')
                                        ->checkboxList(Report::getTenantListTypes()); ?>
                                    <?php echo $form_active->field($form, 'hide_drilldown')->checkbox(); ?>
                                    <?php echo $form_active->field($form, 'is_visible_on_dat_file')->checkbox(); ?>
                                    <?php /*echo $form_active->field($form, 'irregular_hours_from')
                                        ->widget(TimePicker::classname(), [
                                            'pluginOptions' => [
                                                'showSeconds' => false,
                                                'template' => 'dropdown',
                                                'showMeridian' => false,
                                                'minuteStep' => 15,
                                                'showInputs' => false,
                                                'defaultTime' => false,
                                            ],
                                            'addonOptions' => [
                                                'asButton' => true,
                                                'buttonOptions' => ['class' => 'btn btn-info'],
                                            ]]); */?><!--
                                    <?php /*echo $form_active->field($form, 'site_irregular_hours_from')
                                        ->textInput(['disabled' => true])->label(false) */?>
                                    <?php /*echo $form_active->field($form, 'irregular_hours_to')
                                        ->widget(TimePicker::classname(), [
                                            'pluginOptions' => [
                                                'showSeconds' => false,
                                                'template' => 'dropdown',
                                                'showMeridian' => false,
                                                'minuteStep' => 15,
                                                'showInputs' => false,
                                                'defaultTime' => false,
                                            ],
                                            'addonOptions' => [
                                                'asButton' => true,
                                                'buttonOptions' => ['class' => 'btn btn-info'],
                                            ]]); */?>
                                    <?php /*echo $form_active->field($form, 'site_irregular_hours_to')
                                        ->textInput(['disabled' => true])->label(false) */?>
                                    <?php /*echo $form_active->field($form, 'irregular_additional_percent')->textInput() */?>
                                    --><?php /*echo $form_active->field($form, 'site_irregular_additional_percent')
                                        ->textInput(['disabled' => true])->label(false) */?>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-lg-4">
                        <fieldset>
                            <legend><?php echo Yii::t('backend.tenant', 'Barcode settings'); ?></legend>
                            <?php echo $form_active->field($form, 'prefix')->textInput(); ?>
                            <?php echo $form_active->field($form, 'ending')->textInput(); ?>
                            <?php echo $form_active->field($form, 'client_code')->textInput(); ?>
                            <?php echo $form_active->field($form, 'contract_id')->textInput(); ?>
                            <?php echo $form_active->field($form, 'property_id')->textInput(); ?>
                            <?php echo $form_active->field($form, 'formatting')->textInput(); ?>
                            <?php echo $form_active->field($form, 'option_visible_barcode')->checkbox(); ?>
                        </fieldset>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo Html::submitInput(Yii::t('backend.view', $is_create ? 'Create' : 'Update'),
                        ['class' => 'btn btn-success']); ?>
                </div>
            </fieldset>
            <?php ActiveForm::end(); ?>
        </div>
        <div id="irregular-data" class="tab-pane fade">
            <div class="row">
                <div class="col-lg-6">
                    <div ng-app="irregularHours" ng-cloak>
                        <div class="col-lg-6">
                            <div class="loader-wrap">
                                <div ng-controller="irregularCalendar"
                                     data-init='<?php echo json_encode($irregular_data); ?>'>
                                    <hr style="border-color: grey">
                                    <div ng-repeat="(day_number,day_of_week) in days_of_week">
                                        <h3>{{day_of_week}}</h3>
                                        <div>
                                            <div ng-repeat="(index, row) in model_data[day_number]">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="input-group">
                                                            <div class="smart-time form-control time-picker" smart-time
                                                                 smt-value="row.hours_from"></div>
                                                            <span class="input-group-addon"><i
                                                                        class="glyphicon glyphicon-time"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="input-group">
                                                            <div class="smart-time form-control time-picker" smart-time
                                                                 smt-value="row.hours_to"></div>
                                                            <span class="input-group-addon"><i
                                                                        class="glyphicon glyphicon-time"></i></span>
                                                        </div>
                                                    </div>
                                                    <input ng-model="row.id" type="hidden">
                                                    <div class="form-group col-sm-4">
                                                        <button type="button" ng-click="deleteHours(index, day_number)"
                                                                class="btn btn-danger">{{texts.delete_text}}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" ng-click="addHours(day_number)"
                                                class="btn btn-success smt-button">{{texts.add_text}}
                                        </button>
                                        <hr style="border-color: grey">
                                    </div>
                                    <br>
                                    <div class="btn btn-success update-irregular smt-button" ng-click="saveIrregular()">
                                        {{texts.update_text}}
                                    </div>
                                    <div class="modal fade" id="success-modal" role="dialog">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-body text-center">
                                                    <h3>{{texts.success_text}}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="loader" ng-show="preloader"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="loader-wrap">
                                <div ng-controller="irregularHour"
                                     data-init='<?php echo json_encode($irregular_hour) ?>'>
                                    <hr style="border-color: grey">
                                    <div class="row">
                                        <!--<div class="col-sm-4">
                                            <label class="control-label">{{ texts.from_text }}</label>
                                            <div class="input-group">
                                                <div class="smart-time form-control time-picker ng-isolate-scope" smart-time
                                                     smt-value="model_data[0].irregular_hours_from"></div>
                                                <span class="input-group-addon"><i
                                                            class="glyphicon glyphicon-time"></i></span>
                                            </div>
                                            <input type="text" ng-model="site_options.site_irregular_hours_from" class="form-control" disabled>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="control-label">{{ texts.to_text }}</label>
                                            <div class="input-group">
                                                <div class="smart-time form-control time-picker ng-isolate-scope" smart-time
                                                     smt-value="model_data[0].irregular_hours_to"></div>
                                                <span class="input-group-addon"><i
                                                            class="glyphicon glyphicon-time"></i></span>
                                            </div>
                                            <input type="text" ng-model="site_options.site_irregular_hours_to" class="form-control" disabled>
                                        </div>-->
                                        <div class="col-lg-12">
                                            <label class="control-label">{{ texts.percent_text }}</label>
                                            <div class="input-group" style="width:100%">
                                                <input type="text" ng-model="model_data[0].irregular_additional_percent" name="percent" class="form-control" placeholder="0">
                                            </div>
                                            <input type="text" ng-model="site_options.site_irregular_additional_percent" class="form-control" disabled>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="btn btn-success update-irregular smt-button" ng-click="saveHour()">
                                        {{texts.update_text}}
                                    </div>
                                    <div class="btn btn-danger update-irregular smt-button" ng-click="delHour()" style="margin-top: 10px">
                                        {{texts.delete_text}}
                                    </div>
                                    <div class="modal fade" id="success-modal" role="dialog">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-body text-center">
                                                    <h3>{{texts.success_text}}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="loader" ng-show="preloader"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    <!--<div ng-app="irregularHours" ng-cloak>
                        <div class="loader-wrap">
                            <div ng-controller="irregularCalendar"
                                 data-init='<?php /*echo json_encode($irregular_data); */?>'>
                                <hr style="border-color: grey">
                                <div ng-repeat="(day_number,day_of_week) in days_of_week">
                                    <h3>{{day_of_week}}</h3>
                                    <div>
                                        <div ng-repeat="(index, row) in model_data[day_number]">
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <div class="input-group">
                                                        <div class="smart-time form-control time-picker" smart-time
                                                             smt-value="row.hours_from"></div>
                                                        <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-time"></i></span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="input-group">
                                                        <div class="smart-time form-control time-picker" smart-time
                                                             smt-value="row.hours_to"></div>
                                                        <span class="input-group-addon"><i
                                                                    class="glyphicon glyphicon-time"></i></span>
                                                    </div>
                                                </div>
                                                <input ng-model="row.id" type="hidden">
                                                <div class="form-group col-sm-3">
                                                    <button type="button" ng-click="deleteHours(index, day_number)"
                                                            class="btn btn-danger">{{texts.delete_text}}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" ng-click="addHours(day_number)"
                                            class="btn btn-success smt-button">{{texts.add_text}}
                                    </button>
                                    <hr style="border-color: grey">
                                </div>
                                <br>
                                <div class="btn btn-success update-irregular smt-button" ng-click="saveIrregular()">
                                    {{texts.update_text}}
                                </div>
                                <div class="modal fade" id="success-modal" role="dialog">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-body text-center">
                                                <h3>{{texts.success_text}}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="loader" ng-show="preloader"></div>
                            </div>
                        </div>
                    </div>
                </div>-->
            </div>
        </div>
    </div>
</div>

<?php
$field_site_id = Html::getInputId($form, 'site_id');
$field_site_fixed_payment = Html::getInputId($form, 'site_fixed_payment');
$field_site_irregular_hours_from = Html::getInputId($form, 'site_irregular_hours_from');
$field_site_irregular_hours_to = Html::getInputId($form, 'site_irregular_hours_to');
$field_site_irregular_additional_percent = Html::getInputId($form, 'site_irregular_additional_percent');
$site_fixed_payments_url = Url::to(['/json-search/site-billing-settings']);
$field_rate = Html::getInputId($form, 'rate_type_id');
$field_fixed_payment = Html::getInputId($form, 'fixed_payment');
$rate_fixed_payments_url = Url::to(['/json-search/rate-fixed-payment']);
$field_to_issue = Html::getInputId($form, 'to_issue');
$field_included_reports = Html::getInputId($form, 'included_reports');
AutoRegistrableScriptBlock::begin();
?>
<script>

    function getSiteAttributes(element, attributes) {
        $.ajax({
            url: '<?=$site_fixed_payments_url?>',
            type: 'GET',
            data: {
                site_id: element.val(),
                attributes: Object.keys(attributes)
            },
            dataType: 'json',
            success: function (data) {
                if (!$.isEmptyObject(data)) {
                    Object.keys(data).forEach(function (val) {
                        attributes[val].val(data[val]);
                    });


                }
            }
        });
    }
    var attributeInputMap = {
        'fixed_payment': $('#<?=$field_site_fixed_payment?>'),
        'irregular_hours_from': $('#<?=$field_site_irregular_hours_from?>'),
        'irregular_hours_to': $('#<?=$field_site_irregular_hours_to?>'),
        'irregular_additional_percent': $('#<?=$field_site_irregular_additional_percent?>'),
    };
    $('#<?=$field_site_id?>').on('change', function () {
        let element = $(this);
        getSiteAttributes(element, attributeInputMap);
    });

    $('#<?=$field_site_id?>').each(function () {
        let element = $(this);
        getSiteAttributes(element, attributeInputMap);
    });
    $('#<?=$field_to_issue?>').on('change', function () {
        let form = $(this).parents('form');
        let data = $(this).data('included-reports');
        let type = $(this).val();
        let field = form.find('#<?=$field_included_reports?>');

        if (data[type]) {
            for (let key in data[type]) {
                if (data[type].hasOwnProperty(key)) {
                    field.find('input[type="checkbox"][value="' + key + '"]').prop('checked', data[type][key]);
                }
            }
        }
    });
    <?php if($is_create): ?>
    $('#<?=$field_rate?>').on('change', function () {
        let element = $(this);
        $.getJSON('<?=$rate_fixed_payments_url?>', {
            type: element.val()
        }, function (data) {
            if (!$.isEmptyObject(data)) {
                $('#<?=$field_fixed_payment?>').val(data['fixed_payment']);
            } else {
                $('#<?=$field_fixed_payment?>').val('');
            }
        })
    });
    $('#<?=$field_rate?>').each(function () {
        let element = $(this);
        $.getJSON('<?=$rate_fixed_payments_url?>', {
            type: element.val()
        }, function (data) {
            if (!$.isEmptyObject(data)) {
                $('#<?=$field_fixed_payment?>').val(data['fixed_payment']);
            } else {
                $('#<?=$field_fixed_payment?>').val('');
            }
        })
    });
    <?php else : ?>
    $('#<?=$field_rate?>').on('change', function () {
        let element = $(this);
        $.getJSON('<?=$rate_fixed_payments_url?>', {
            id: element.val()
        }, function (data) {
            if (!$.isEmptyObject(data)) {
                $('#<?=$field_fixed_payment?>').val(data['fixed_payment']);
            } else {
                $('#<?=$field_fixed_payment?>').val('');
            }
        })
    });
    <?php endif ?>
</script>
<?php AutoRegistrableScriptBlock::end() ?>


