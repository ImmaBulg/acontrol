<?php

use common\components\i18n\LanguageSelector;
/**
 * @var \common\components\i18n\Formatter $formatter
 */
$formatter = Yii::$app->formatter;
$direction = LanguageSelector::getAliasLanguageDirection();

require_once(Yii::getAlias('@common/components/chart/pchart/class/pData.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pDraw.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pPie.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pImage.class.php'));

?>
<table dir="<?php echo $direction; ?>" style="width:100%;font-size:12px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td style="padding:5px;font-size:14px;" colspan="2">
            <strong><?php echo Yii::t('common.view', 'Kwh report'); ?></strong>
        </td>
    </tr>
    <tr>
        <td style="padding:5px;" colspan="2">
            <?php echo Yii::t('common.view', 'To'); ?>: <?php echo $data['site_owner']; ?>
        </td>
    </tr>
    <tr>
        <td style="padding:5px;width:60%">
            <?php echo Yii::t('common.view', 'Kwh summary report for'); ?>: <?php echo $data['site']; ?>
        </td>
        <td style="padding:5px;width:40%;">
            <?php echo Yii::t('common.view', 'Issue date'); ?>: <?php echo Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'); ?>
        </td>
    </tr>
    <tr>
        <td style="padding:5px;" colspan="2">
            <?php echo Yii::t('common.view', 'Report range'); ?>: (<?php echo Yii::$app->formatter->asDate($report->from_date, 'dd/MM/yy'); ?> - <?php echo Yii::$app->formatter->asDate($report->to_date, 'dd/MM/yy'); ?>)
        </td>
    </tr>
    </tbody>
</table>
<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
    <thead>
        <tr bgcolor="#7e7e7e">
            <th style="color:#fff;padding:5px;width:8%;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
                <?php echo Yii::t('common.view', 'Row number'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:8%;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
                <?php echo Yii::t('common.view', 'Tenant ID'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
                <?php echo Yii::t('common.view', 'Tenant name'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
                <?php echo Yii::t('common.view', 'Meter ID / Group Name'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center" colspan="2">
                <?php echo Yii::t('common.view', 'Pisga Kwh'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center" colspan="2">
                <?php echo Yii::t('common.view', 'Geva Kwh'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center" colspan="2">
                <?php echo Yii::t('common.view', 'Shefel Kwh'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
                <?php echo Yii::t('common.view', 'Total Air consumption'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
                <?php echo Yii::t('common.view', 'Total Kwh'); ?>
            </th>
        </tr>
        <tr bgcolor="#7e7e7e">
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Air consumption'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Kwh consumption'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Air consumption'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Kwh consumption'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Air consumption'); ?>
            </th>
            <th style="color:#fff;padding:5px;width:12%;font-weight:normal;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Kwh consumption'); ?>
            </th>
        </tr>
    <tbody>
        <?php $number = 1; ?>
        <?php foreach ($data['tenants'] as $tenant) { ?>
            <tr>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $number++; ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $tenant['tenant_id']; ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $tenant['tenant_name']; ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $tenant['meter_id']; ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asNumberFormat($tenant['pisga_reading']); ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asNumberFormat($tenant['pisga_consumption']); ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asNumberFormat($tenant['geva_reading']); ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asNumberFormat($tenant['geva_consumption']); ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asNumberFormat($tenant['shefel_reading']); ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asNumberFormat($tenant['shefel_consumption']); ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asNumberFormat($tenant['consumption_total']); ?>

                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asNumberFormat($tenant['pisga_reading'] + $tenant['geva_reading'] + $tenant['shefel_reading']); ?>

                </td>
            </tr>
        <?php } ?>
        <tr>
           <td colspan="8" style="padding-top: 20px"></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Total consumption'); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['site_total']['pisga_consumption']); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['site_total']['geva_consumption']); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['site_total']['shefel_consumption']); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['site_total']['consumption_total']); ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Electric company bill'); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['site_total']['pisga_reading']); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['site_total']['geva_reading']); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['site_total']['shefel_reading']); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['site_total']['reading_total']); ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Diff in Kwh'); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['diff']['pisga_consumption']); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['diff']['geva_consumption']); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['diff']['shefel_consumption']); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['diff']['consumption_total']); ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Diff in %'); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['diff_percent']['pisga']).'%'; ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['diff_percent']['geva']).'%'; ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['diff_percent']['shefel']).'%'; ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asNumberFormat($data['diff_percent']['consumption_total']).'%'; ?>
            </td>
        </tr>
    </tbody>
</table>
<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:5px;" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td style="padding:5px;">
            <?php
            $graphPieKwhDataSet = [
                $data['site_total']['pisga_consumption'],
                $data['site_total']['geva_consumption'],
                $data['site_total']['shefel_consumption']
            ];
            $MyData = new \pData();
            $MyData->addPoints($graphPieKwhDataSet,"ScoreA");
            $MyData->addPoints([
                Yii::t('common.graph', 'Pisga ({value} Kwh)', [
                    'value' => $formatter->asNumberFormat(\yii\helpers\ArrayHelper::getValue($graphPieKwhDataSet, 0, 0)),
                ]),
                Yii::t('common.graph', 'Geva ({value} Kwh)', [
                    'value' => $formatter->asNumberFormat(\yii\helpers\ArrayHelper::getValue($graphPieKwhDataSet, 1, 0)),
                ]),
                Yii::t('common.graph', 'Shefel ({value} Kwh)', [
                    'value' => $formatter->asNumberFormat(\yii\helpers\ArrayHelper::getValue($graphPieKwhDataSet, 2, 0)),
                ]),
            ],"Labels");
            $MyData->setAbscissa("Labels");

            $myPicture = new \pImage(300,230,$MyData,TRUE);
            $myPicture->setFontProperties(array("FontName"=>Yii::getAlias('@common/components/chart/pchart/fonts/arimo.ttf'),"FontSize"=>8,"R"=>80,"G"=>80,"B"=>80));

            $PieChart = new \pPie($myPicture,$MyData);
            $PieChart->setSliceColor(0,array("R"=>196,"G"=>2,"B"=>51));
            $PieChart->setSliceColor(1,array("R"=>0,"G"=>163,"B"=>104));
            $PieChart->setSliceColor(2,array("R"=>0,"G"=>136,"B"=>191));
            $myPicture->setShadow(FALSE);
            $PieChart->draw3DPie(125,120,array("WriteValues"=>TRUE, "Radius"=>100,"DataGapAngle"=>12,"DataGapRadius"=>10,"Border"=>FALSE));
            $myPicture->setFontProperties(array("FontName"=>Yii::getAlias('@common/components/chart/pchart/fonts/arimo.ttf'),"FontSize"=>8,"R"=>0,"G"=>0,"B"=>0));
            $PieChart->drawPieLegend(0,195,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL));

            ob_start();
            imagepng($myPicture->Picture);
            $contents =  ob_get_contents();
            ob_end_clean();

            echo \common\helpers\Html::img('data:image/png;base64,' .base64_encode($contents), ['scheme' => 'data']);
            ?>
        </td>
    </tr>
    </tbody>
</table>