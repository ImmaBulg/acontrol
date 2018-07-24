<fieldset>
    <?php if ($showAvg): ?>
        <div class="row mediumAvg">
            <div class="row">
                <div class="col-lg-2">Kilowatt hour</div>
                <div class="col-lg-2">Cubic meter</div>
                <div class="col-lg-2">Kilowatt</div>
                <div class="col-lg-2">Cubic meter hour</div>
                <div class="col-lg-2">Incoming temp</div>
                <div class="col-lg-2">Outgoing temp</div>
            </div>
            <div class="row">
                <div class="col-lg-2"><?php echo $form_avg->getAvgData()[Yii::$app->formatter->asDatetime($form_filter->from_date, 'dd-MM-Y HH:mm')]['kilowatt_hour'] ?></div>
                <div class="col-lg-2"><?php echo $form_avg->getAvgData()[Yii::$app->formatter->asDatetime($form_filter->from_date, 'dd-MM-Y HH:mm')]['cubic_meter'] ?></div>
                <div class="col-lg-2"><?php echo $form_avg->getAvgData()[Yii::$app->formatter->asDatetime($form_filter->from_date, 'dd-MM-Y HH:mm')]['kilowatt'] ?></div>
                <div class="col-lg-2"><?php echo $form_avg->getAvgData()[Yii::$app->formatter->asDatetime($form_filter->from_date, 'dd-MM-Y HH:mm')]['cubic_meter_hour'] ?></div>
                <div class="col-lg-2"><?php echo $form_avg->getAvgData()[Yii::$app->formatter->asDatetime($form_filter->from_date, 'dd-MM-Y HH:mm')]['incoming_temp'] ?></div>
                <div class="col-lg-2"><?php echo $form_avg->getAvgData()[Yii::$app->formatter->asDatetime($form_filter->from_date, 'dd-MM-Y HH:mm')]['outgoing_temp'] ?></div>
            </div>
        </div>
    <?php endif; ?>
    <div id="meter-raw-data-head" class="form-group">
        <div class="row">
            <div class="col-lg-12">
                <div class="list-view-head-columns">
                    <hr>
                    <?php use common\helpers\Html;
                            use common\widgets\ListView;
                            ?>
                    <div class="row">
                        <div class="col-lg-2">
                            <?php echo $form->getAttributeLabel('date'); ?>
                        </div>
                        <div class="col-lg-8">
                            <div class="row">
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Kilowatt hour'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Cubic meter'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Kilowatt'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Cubic meter hour'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Incoming temp'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Outgoing temp'); ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?php echo ListView::widget([
                                        'dataProvider' => $data_provider,
                                        'id' => 'list-meter-raw-data',
                                        'itemView' => 'item-view/air-meter-raw-data',
                                        'viewParams' => [
                                            'form' => $form,
                                            'form_active' => $form_active,
                                            'form_filter' => $form_filter,
                                            'form_avg' => $form_avg,
                                            'avg_data' => $form_avg->getAvgData(),
                                        ],
                                        'layout' => '{items}{pager}',
                                    ]); ?>
    </div>
    <?php if($data_provider->totalCount): ?>
        <div class="form-group">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    <?php echo Html::submitInput(Yii::t('backend.view', 'Save'), ['class' => 'btn btn-success btn-block']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</fieldset>