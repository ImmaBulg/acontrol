<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 11.08.2017
 * Time: 17:45
 * @var \common\components\calculators\data\TenantData $tenant_data
 * @var string $direction
 * @var \common\components\i18n\Formatter $formatter
 */
require_once(Yii::getAlias('@common/components/chart/pchart/class/pData.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pDraw.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pPie.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pImage.class.php'));
use common\helpers\Html;

?>
<table dir="<?php echo $direction; ?>"
       style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-top:20px;margin-bottom:5px;"
       cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <?php
        $graphKwhAxisLabel = [];
        $graphKwhDataSetPisga = [];
        $graphKwhDataSetGeva = [];
        $graphKwhDataSetShefel = [];
        ?>
        <?php foreach($tenant_data->getYearly()->getMonthlyData() as $monthly): ?>
            <?php
            $graphKwhAxisLabel[] = Yii::t('common.graph', $monthly->getStartDate()->format('M'));
            $graphKwhDataSetPisga[] = $monthly->getPisgaConsumption();
            $graphKwhDataSetGeva[] = $monthly->getGevaConsumption();
            $graphKwhDataSetShefel[] = $monthly->getShefelConsumption();
            ?>
        <?php endforeach; ?>
        <td style="padding:5px;">
            <?php
            $MyData = new \pData();
            $MyData->addPoints($graphKwhDataSetPisga, Yii::t('common.graph', 'Pisga'));
            $MyData->addPoints($graphKwhDataSetGeva, Yii::t('common.graph', 'Geva'));
            $MyData->addPoints($graphKwhDataSetShefel, Yii::t('common.graph', 'Shefel'));
            $MyData->addPoints($graphKwhAxisLabel, "Labels");
            $MyData->setAbscissa("Labels");
            $MyData->setPalette(Yii::t('common.graph', 'Pisga'), ["R" => 196, "G" => 2, "B" => 51]);
            $MyData->setPalette(Yii::t('common.graph', 'Geva'), ["R" => 0, "G" => 163, "B" => 104]);
            $MyData->setPalette(Yii::t('common.graph', 'Shefel'), ["R" => 0, "G" => 136, "B" => 191]);
            $myPicture = new \pImage(550, 230, $MyData, true);
            $myPicture->setFontProperties(["FontName" => Yii::getAlias('@common/components/chart/pchart/fonts/arimo.ttf'),
                                           "FontSize" => 8]);
            $myPicture->setGraphArea(50, 30, 500, 200);
            $myPicture->drawScale(["DrawSubTicks" => true, "Mode" => SCALE_MODE_ADDALL_START0]);
            $myPicture->setShadow(false);
            $myPicture->drawStackedBarChart(["Surrounding" => -15, "InnerSurrounding" => 15]);
            $myPicture->drawLegend(0, 220, ["Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL]);
            $myPicture->drawText(250, 20, Yii::t('common.graph', 'History of consumption in Kwh'),
                                 ["FontSize" => 9, "Align" => TEXT_ALIGN_BOTTOMMIDDLE]);
            ob_start();
            imagepng($myPicture->Picture);
            $contents = ob_get_contents();
            ob_end_clean();
            echo Html::img('data:image/png;base64,' . base64_encode($contents), ['scheme' => 'data']);
            ?>
        </td>
        <td style="padding:5px;">
            <?php
            $MyData = new \pData();
            $MyData->addPoints([
                                   $tenant_data->getPisgaConsumption(),
                                   $tenant_data->getGevaConsumption(),
                                   $tenant_data->getShefelConsumption(),
                               ], "ScoreA");
            $MyData->addPoints([
                                   Yii::t('common.graph', 'Pisga ({value} NIS)', [
                                       'value' => $formatter->asNumberFormat($tenant_data->getPisgaConsumption()),
                                   ]),
                                   Yii::t('common.graph', 'Geva ({value} NIS)', [
                                       'value' => $formatter->asNumberFormat($tenant_data->getGevaConsumption()),
                                   ]),
                                   Yii::t('common.graph', 'Shefel ({value} NIS)', [
                                       'value' => $formatter->asNumberFormat($tenant_data->getShefelConsumption()),
                                   ]),
                               ], "Labels");
            $MyData->setAbscissa("Labels");
            $myPicture = new \pImage(250, 230, $MyData, true);
            $myPicture->setFontProperties(["FontName" => Yii::getAlias('@common/components/chart/pchart/fonts/arimo.ttf'),
                                           "FontSize" => 8, "R" => 80, "G" => 80, "B" => 80]);
            $PieChart = new \pPie($myPicture, $MyData);
            $PieChart->setSliceColor(0, ["R" => 196, "G" => 2, "B" => 51]);
            $PieChart->setSliceColor(1, ["R" => 0, "G" => 163, "B" => 104]);
            $PieChart->setSliceColor(2, ["R" => 0, "G" => 136, "B" => 191]);
            $myPicture->setShadow(false);
            $PieChart->draw3DPie(125, 120, ["WriteValues" => true, "Radius" => 100, "DataGapAngle" => 12,
                                            "DataGapRadius" => 10, "Border" => false]);
            $myPicture->setFontProperties(["FontName" => Yii::getAlias('@common/components/chart/pchart/fonts/arimo.ttf'),
                                           "FontSize" => 8, "R" => 0, "G" => 0, "B" => 0]);
            $PieChart->drawPieLegend(0, 195, ["Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL]);
            $myPicture->drawText(125, 20, Yii::t('common.graph', 'Charges in NIS based on seasons'),
                                 ["FontSize" => 9, "Align" => TEXT_ALIGN_BOTTOMMIDDLE]);
            ob_start();
            imagepng($myPicture->Picture);
            $contents = ob_get_contents();
            ob_end_clean();
            echo Html::img('data:image/png;base64,' . base64_encode($contents), ['scheme' => 'data']);
            ?>
        </td>
    </tr>
    </tbody>
</table>
