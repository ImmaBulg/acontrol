<fieldset>
    <?php if ($showAvg): ?>
        <div class="row mediumAvg">
            <div class="row">
                <div class="col-lg-4"><?php echo $form->getAttributeLabel('pisga'); ?></div>
                <div class="col-lg-4"><?php echo $form->getAttributeLabel('geva'); ?></div>
                <div class="col-lg-4"><?php echo $form->getAttributeLabel('shefel'); ?></div>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <?php
                    echo $form_avg->getAvgData()[Yii::$app->formatter->asDatetime($form_filter->from_date, 'dd-MM-Y HH:mm')]['pisga'];
                    ?>
                </div>
                <div class="col-lg-4">
                    <?php
                    echo $form_avg->getAvgData()[Yii::$app->formatter->asDatetime($form_filter->from_date, 'dd-MM-Y HH:mm')]['geva'];
                    ?>
                </div>
                <div class="col-lg-4">
                    <?php
                    echo $form_avg->getAvgData()[Yii::$app->formatter->asDatetime($form_filter->from_date, 'dd-MM-Y HH:mm')]['shefel'];
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div id="meter-raw-data-head" class="form-group">
        <div class="row">
            <div class="col-lg-12">
                <div class="list-view-head-columns">
                    <hr>
                    <div class="row">
                        <div class="col-lg-2"></div>
                        <div class="col-lg-3">
                            <?php use common\helpers\Html;
                            use common\widgets\ListView;

                            echo $form->getAttributeLabel('pisga'); ?>
                        </div>
                        <div class="col-lg-3">
                            <?php echo $form->getAttributeLabel('geva'); ?>
                        </div>
                        <div class="col-lg-3">
                            <?php echo $form->getAttributeLabel('shefel'); ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-2 reading-date-field">
                            <?php echo $form->getAttributeLabel('date'); ?>
                        </div>
                        <div class="col-lg-3 reading-labels">
                            <div class="row pisga-readings-titles">
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily reading in Kwh'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily reading in Kwh (from meter)'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily max demand in Kwh'); ?>
                                </div>

                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily export reading in Kwh'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily kvar reading'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 reading-labels">
                            <div class="row geva-readings-titles">
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily reading in Kwh'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily reading in Kwh (from meter)'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily max demand in Kwh'); ?>
                                </div>

                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily export reading in Kwh'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily kvar reading'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 reading-labels">
                            <div class="row shefel-readings-titles">
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily reading in Kwh'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily reading in Kwh (from meter)'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily max demand in Kwh'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily export reading in Kwh'); ?>
                                </div>
                                <div class="col-lg-2">
                                    <?php echo Yii::t('backend.view', 'Daily kvar reading'); ?>
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
            'itemView' => 'item-view/electricity-meter-raw-data',
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
    <?php if ($data_provider->totalCount): ?>
        <div class="form-group">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    <?php echo Html::submitInput(Yii::t('backend.view', 'Save'), ['class' => 'btn btn-success btn-block']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</fieldset>