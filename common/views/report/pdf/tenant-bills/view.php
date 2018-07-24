<?php
require_once(Yii::getAlias('@common/components/chart/pchart/class/pData.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pDraw.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pPie.class.php'));
require_once(Yii::getAlias('@common/components/chart/pchart/class/pImage.class.php'));
use common\helpers\CalculationHelper;
use common\models\Site;
use yii\helpers\ArrayHelper;
use common\models\Tenant;
use common\models\Meter;
use common\models\RateType;
use common\helpers\Html;
use common\components\i18n\LanguageSelector;
use common\models\helpers\reports\ReportGeneratorTenantBills;

$direction = LanguageSelector::getAliasLanguageDirection();
$power_factor_visibility = (!empty($additional_parameters)) ?
    ArrayHelper::getValue($additional_parameters, 'power_factor_visibility', Site::POWER_FACTOR_DONT_SHOW) :
    Site::POWER_FACTOR_DONT_SHOW;
?>

<?php $first_tenant = key($data); ?>

<?php foreach($data as $index => $row): ?>


    <?php if($index != $first_tenant): ?>
        <pagebreak />
    <?php endif; ?>


    <!-- HEADER -->
    <table dir="<?php echo $direction; ?>"
           style="width:100%;font-size:12px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0"
           cellspacing="0">
        <tbody>
        <tr>
            <td style="padding:5px;width:55%" rowspan="3">
                <p>
                    <strong><?php echo Yii::t('common.view', 'Tenant name'); ?>:</strong>
                    <?php echo $row['tenant']->name; ?>
                    <?php switch($report->data_usage_method) {
                        case Meter::DATA_USAGE_METHOD_IMPORT:
                        case Meter::DATA_USAGE_METHOD_IMPORT_PLUS_EXPORT:
                        case Meter::DATA_USAGE_METHOD_IMPORT_MINUS_EXPORT:
                        case Meter::DATA_USAGE_METHOD_EXPORT:
                            echo '(' .
                                 ArrayHelper::getValue(Meter::getListDataUsageMethods(), $report->data_usage_method) .
                                 ')';
                        default:
                            break;
                    } ?>
                </p>
                <p><strong><?php echo Yii::t('common.view', 'Site name'); ?>:</strong> <?php echo $row['site']->name; ?>
                </p>
                <p>
                    <?php if($entrance_date =
                        $row['tenant']->getEntranceDateReport($report->from_date, $report->to_date)
                    ): ?>
                        <?php echo Yii::t('common.view', 'Entry date: {date}', [
                            'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
                        ]); ?>
                    <?php endif; ?>

                    <?php if($exit_date = $row['tenant']->getExitDateReport($report->from_date, $report->to_date)): ?>
                        <?php echo Yii::t('common.view', 'Exit date: {date}', [
                            'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
                        ]); ?>
                    <?php endif; ?>
                </p>
            </td>
            <td style="padding:5px;width:30%;">
                <?php echo Yii::t('common.view', 'Issue date'); ?>:
            </td>
            <td style="padding:5px;width:15%;">
                <?php echo Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'); ?>
            </td>
        </tr>
        <tr>
            <td style="padding:5px;">
                <?php echo Yii::t('common.view', 'Current meter reading'); ?>:
            </td>
            <td style="padding:5px;">
                <?php echo Yii::$app->formatter->asDate($report->to_date, 'dd/MM/yy'); ?>
            </td>
        </tr>
        <tr>
            <td style="padding:5px;">
                <?php echo Yii::t('common.view', 'Previous meter reading'); ?>:
            </td>
            <td style="padding:5px;">
                <?php echo Yii::$app->formatter->asDate($report->from_date, 'dd/MM/yy'); ?>
            </td>
        </tr>
        <tr>
            <td style="padding:20px 5px 5px;" colspan="3">
                <?php $from_date = $report->from_date ?>
                <?php $to_date = $report->to_date ?>
                <?php \common\models\helpers\reports\ReportGenerator::shiftReportRangeByTenantEntranceAndExitDates($from_date,
                                                                                                                   $to_date,
                                                                                                                   $row['tenant'],
                                                                                                                   false); ?>
                <strong>
                    <?php echo Yii::t('common.view', 'Dear customer, here is the billing report for the period of'); ?>
                    <?php echo Yii::$app->formatter->asDate($from_date, 'dd/MM/yy'); ?>
                    - <?php echo Yii::$app->formatter->asDate($to_date, 'dd/MM/yy'); ?>:
                </strong>
            </td>
        </tr>
        </tbody>
    </table>


    <?php if(!empty($row['rules'])): ?>
        <?php
        $graphNisDataSetPisga = 0;
        $graphNisDataSetGeva = 0;
        $graphNisDataSetShefel = 0;
        ?>
        <?php foreach($row['rules'] as $rule): ?>
            <?php
            $graphNisDataSetPisga += ArrayHelper::getValue($rule, 'pisga_total_pay', 0);
            $graphNisDataSetGeva += ArrayHelper::getValue($rule, 'geva_total_pay', 0);
            $graphNisDataSetShefel += ArrayHelper::getValue($rule, 'shefel_total_pay', 0);
            ?>
            <?php if(!$row['tenant']->hide_drilldown): ?>
                <?php
                switch($rule['rule']['type']) {
                    case ReportGeneratorTenantBills::RULE_SINGLE_CHANNEL:
                        echo $this->render('_rule', [
                            'rate_type' => $row['rate_type'],
                            'rule' => $rule,
                            'additional_parameters' => $additional_parameters,
                        ]);
                        break;
                    case ReportGeneratorTenantBills::RULE_GROUP_LOAD:
                        echo $this->render('_rule_group_load', [
                            'rate_type' => $row['rate_type'],
                            'rule' => $rule,
                            'additional_parameters' => $additional_parameters,
                        ]);
                        break;
                    case ReportGeneratorTenantBills::RULE_FIXED_LOAD:
                        echo $this->render('_rule_fixed_load', [
                            'rate_type' => $row['rate_type'],
                            'rule' => $rule,
                            'additional_parameters' => $additional_parameters,
                        ]);
                        break;
                    default:
                        break;
                }
                ?>
            <?php endif; ?>

        <?php endforeach; ?>


        <!-- Total consumption for tenant -->
        <table dir="<?php echo $direction; ?>"
               style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:15px;"
               cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <td style="padding:5px;" colspan="7">
                    <strong>
                        <?php echo Yii::t('common.view', 'Total consumption for tenant'); ?>:
                    </strong>
                </td>
            </tr>
            <tr>
                <?php if($row['rate_type'] == RateType::TYPE_TAOZ): ?>
                    <th style="padding:5px;"></th>
                <?php else: ?>
                    <th style="padding:5px;" colspan="4"></th>
                <?php endif; ?>

                <th bgcolor="#7e7e7e" style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;"
                    align="center">
                    <?php echo Yii::t('common.view', 'Max demand'); ?>
                </th>

                <?php if($row['rate_type'] == RateType::TYPE_TAOZ): ?>
                    <th bgcolor="#7e7e7e"
                        style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;"
                        align="center">
                        <?php echo Yii::t('common.view', 'Total Pisga in Kwh'); ?>
                    </th>
                    <th bgcolor="#7e7e7e"
                        style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;"
                        align="center">
                        <?php echo Yii::t('common.view', 'Total Geva in Kwh'); ?>
                    </th>
                    <th bgcolor="#7e7e7e"
                        style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;"
                        align="center">
                        <?php echo Yii::t('common.view', 'Total Shefel in Kwh'); ?>
                    </th>
                <?php endif; ?>

                <th bgcolor="#7e7e7e" style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;"
                    align="center">
                    <?php echo Yii::t('common.view', 'Total consumption in Kwh'); ?>
                </th>
                <th bgcolor="#7e7e7e" style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;"
                    align="center">
                    <?php echo Yii::t('common.view', 'Total to pay'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php if($row['rate_type'] == RateType::TYPE_TAOZ): ?>
                    <th style="padding:5px;"></th>
                <?php else: ?>
                    <th style="padding:5px;" colspan="4"></th>
                <?php endif; ?>

                <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                    <?php if($row['max_consumption']): ?>
                        <?php echo Yii::$app->formatter->asNumberFormat($row['max_consumption']); ?>
                    <?php endif; ?>
                </td>

                <?php if($row['rate_type'] == RateType::TYPE_TAOZ): ?>
                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <?php echo Yii::$app->formatter->asNumberFormat($row['pisga_consumption']); ?>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <?php echo Yii::$app->formatter->asNumberFormat($row['geva_consumption']); ?>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <?php echo Yii::$app->formatter->asNumberFormat($row['shefel_consumption']); ?>
                    </td>
                <?php endif; ?>

                <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                    <?php echo Yii::$app->formatter->asNumberFormat($row['total_consumption']); ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                    <?php echo Yii::$app->formatter->asPrice($row['total_pay']); ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
        /**
         * REPLACE / SHOW fixed_payments on rates on priority `So Rate->Tenant->Site in order of priority`
         */
        $_fp = 0;
        if(CalculationHelper::isCorrectFixedPayment($row['fixed_payment'])) {
            $_fp = $row['fixed_payment'];
        }
        else {
            if(($row['site'] instanceof Tenant && CalculationHelper::isCorrectFixedPayment($site_fixed_payment = $row['site']->relationSite->relationSiteBillingSetting['fixed_payment']))
               || $row['site'] instanceof Site && CalculationHelper::isCorrectFixedPayment($site_fixed_payment = $row['site']->relationSiteBillingSetting['fixed_payment'])) {
                $_fp = $site_fixed_payment;
            }
            elseif(CalculationHelper::isCorrectFixedPayment($additional_parameters['rates_fixed_payments'])) {
                $_fp = $additional_parameters['rates_fixed_payments'];
            }
        }
        // replace
        $row['fixed_payment'] = $_fp;
        ?>


        <!-- VAT -->
        <table dir="<?php echo $direction; ?>"
               style="border-left:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;width:100%;font-size:11px;color:#000;vertical-align:top;"
               cellpadding="0" cellspacing="0">
            <tbody>
            <?php if($row['vat_included']): ?>
                <tr>
                    <td style="padding:5px;width:55%;" rowspan="3">
                        <?php if($row['tenant']->getBillingContent() != null): ?>
                            <p><?php echo $row['tenant']->getBillingContent(); ?></p>
                        <?php endif; ?>
                        <?php if($row['site']->getComment() != null): ?>
                            <p><?php echo $row['site']->getComment(); ?></p>
                        <?php endif; ?>
                    </td>
                    <td style="width:30%;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
                        <strong><?php echo Yii::t('common.view', 'Fixed payment'); ?></strong>
                    </td>
                    <td style="width:15%;padding:5px;" align="center" dir="ltr">
                        <?php echo Yii::$app->formatter->asPrice($row['fixed_payment']); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width:30%;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
                        <strong><?php echo Yii::t('common.view', 'Total'); ?></strong>
                    </td>
                    <td style="width:15%;padding:5px;" align="center" dir="ltr">
                        <?php echo Yii::$app->formatter->asPrice($row['total_pay'] + $row['fixed_payment']); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width:30%;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
                        <strong><?php echo Yii::t('common.view', 'VAT'); ?> <span
                                    dir="ltr"><?php echo Yii::$app->formatter->asPercentage($row['vat_percentage']); ?></span></strong>
                    </td>
                    <td style="width:15%;padding:5px;" align="center" dir="ltr">
                        <?php echo Yii::$app->formatter->asPrice($row['vat']); ?>
                    </td>
                </tr>
                <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                             Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                    <tr>
                        <td style="padding:5px;width:55%;"></td>
                        <td style="width:30%;border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
                            <strong><?php echo Yii::t('common.view', 'Power factor'); ?></strong>
                        </td>
                        <td style="width:15%;border-top:1px solid #000;padding:5px;" align="center" dir="ltr">
                            <?php echo Yii::$app->formatter->asNumberFormat($row['power_factor_value'], 3); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                             Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                    <tr>
                        <td style="padding:5px;width:55%;"></td>
                        <td style="width:30%;border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
                            <strong><?php echo Yii::t('common.view', 'Power factor addition'); ?></strong>
                        </td>
                        <td style="width:15%;border-top:1px solid #000;padding:5px;" align="center" dir="ltr">
                            <?php echo Yii::$app->formatter->asPrice($row['power_factor_pay']); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding:5px;width:55%;border-top:1px solid #000;border-bottom:1px solid #000;">
                        <strong><?php echo (!empty($row['vat_text'])) ? $row['vat_text'] :
                                Yii::t('common.view', 'VAT included'); ?></strong>
                    </td>
                    <td style="width:30%;border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;border-right:1px solid #000;padding:5px;">
                        <strong><?php echo Yii::t('common.view', 'Total to pay'); ?></strong>
                    </td>
                    <td style="width:15%;padding:5px;border-top:1px solid #000;border-bottom:1px solid #000;"
                        align="center" dir="ltr">
                        <?php if(in_array($power_factor_visibility,
                                          [Site::POWER_FACTOR_SHOW_ADD_FUNDS])): ?>
                            <?php echo Yii::$app->formatter->asPrice($row['total_pay'] + $row['fixed_payment'] +
                                                                     $row['vat'] + $row['power_factor_pay']); ?>
                        <?php else: ?>
                            <?php echo Yii::$app->formatter->asPrice($row['total_pay'] + $row['fixed_payment'] +
                                                                     $row['vat']); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <tr>
                    <td style="padding:5px;width:55%;">
                        <?php if($row['tenant']->getBillingContent() != null): ?>
                            <p><?php echo $row['tenant']->getBillingContent(); ?></p>
                        <?php endif; ?>
                        <?php if($row['site']->getComment() != null): ?>
                            <p><?php echo $row['site']->getComment(); ?></p>
                        <?php endif; ?>
                    </td>
                    <td style="width:30%;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
                        <strong><?php echo Yii::t('common.view', 'Fixed payment'); ?></strong>
                    </td>


                    <td style="width:15%;padding:5px;" align="center" dir="ltr">


                        <?php echo Yii::$app->formatter->asPrice($row['fixed_payment']); ?>
                    </td>


                </tr>
                <?php if(in_array($power_factor_visibility, [Site::POWER_FACTOR_SHOW_ADD_FUNDS,
                                                             Site::POWER_FACTOR_SHOW_DONT_ADD_FUNDS])): ?>
                    <tr>
                        <td style="padding:5px;width:55%;"></td>
                        <td style="width:30%;border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
                            <strong><?php echo Yii::t('common.view', 'Power factor'); ?></strong>
                        </td>
                        <td style="width:15%;border-top:1px solid #000;padding:5px;" align="center" dir="ltr">
                            <?php echo Yii::$app->formatter->asNumberFormat($row['power_factor_value'], 3); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php if(in_array($power_factor_visibility,
                                  [Site::POWER_FACTOR_SHOW_ADD_FUNDS])): ?>
                    <tr>
                        <td style="padding:5px;width:55%;"></td>
                        <td style="width:30%;border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
                            <strong><?php echo Yii::t('common.view', 'Power factor addition'); ?></strong>
                        </td>
                        <td style="width:15%;border-top:1px solid #000;padding:5px;" align="center" dir="ltr">
                            <?php echo Yii::$app->formatter->asPrice($row['power_factor_pay']); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding:5px;width:55%;border-top:1px solid #000;border-bottom:1px solid #000;">
                        <strong><?php echo (!empty($row['vat_text'])) ? $row['vat_text'] :
                                Yii::t('common.view', 'VAT not included'); ?></strong>
                    </td>
                    <td style="width:30%;border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;padding:5px;border-bottom:1px solid #000;">
                        <strong><?php echo Yii::t('common.view', 'Total to pay'); ?></strong>
                    </td>
                    <td style="width:15%;border-top:1px solid #000;padding:5px;border-bottom:1px solid #000;"
                        align="center" dir="ltr">
                        <?php if(in_array($power_factor_visibility,
                                          [Site::POWER_FACTOR_SHOW_ADD_FUNDS])): ?>
                            <?php echo Yii::$app->formatter->asPrice($row['total_pay'] + $row['fixed_payment'] +
                                                                     $row['power_factor_pay']); ?>
                        <?php else: ?>
                            <?php echo Yii::$app->formatter->asPrice($row['total_pay'] + $row['fixed_payment']); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Barcode -->
        <?php foreach($row['rules'] as $rule) : ?>
            <?php
            $_barcode = '';
            // data
            $client_code = '';
            $contract_id = '';
            $property_id = '';
            $formatting = '';
            $prefix = ($rule['model_tenant']['prefix']) ? ($rule['model_tenant']['prefix']) : '';
            $ending = ($rule['model_tenant']['ending']) ? ($rule['model_tenant']['ending']) : '';
            $client_code = $rule['model_tenant']['client_code'];
            $contract_id = $rule['model_tenant']['contract_id'];
            $property_id = $rule['model_tenant']['property_id'];
            $formatting = $rule['model_tenant']['formatting']; // @prefix@property@contract@code@date@total@ending
            $option_visible_barcode = $rule['model_tenant']['option_visible_barcode'];
            if($option_visible_barcode) {
                if(isset($client_code) and isset($contract_id) and isset($property_id) and isset($formatting)) {
                    $date = new DateTime();
                    $patterns = [
                        '/@prefix/',
                        '/@property/',
                        '/@contract/',
                        '/@code/',
                        '/@date/',
                        '/@total/',
                        '/@ending/',
                    ];
                    $replace = [
                        $prefix,
                        $property_id,
                        $contract_id,
                        $client_code,
                        $date->format('dmY'),
                        number_format($row['total_pay'], 2, '.', ''),
                        $ending
                    ];
                    $_barcode = preg_replace($patterns, $replace, $formatting);
                }
            }
            ?>
        <?php endforeach; ?>
        <?php if($option_visible_barcode and $_barcode) : ?>
            <table dir="<?php echo $direction; ?>"
                   style="margin-top:20px;margin-bottom:5px; width:100%;font-size:11px;color:#000;vertical-align:top;"
                   cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td style="width:70%; border:1px solid #000; padding:5px;font-size: 24px;font-weight: bold">
                        Barcode
                    </td>
                    <td style="width:30%; border:1px solid #000; padding:5px;" align="center"
                        dir="ltr">
                        <barcode code="<?php echo $_barcode; ?>" type="C39"/>
                    </td>
                </tr>
                </tbody>

            </table>
        <?php endif; ?>

        <!-- Graphics -->
        <?php if(Yii::$app->params['is_add_graph'] && !empty($yearly[$index])): ?>
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
                    <?php foreach($yearly[$index] as $date => $row): ?>
                        <?php
                        $graphKwhAxisLabel[] = Yii::t('common.graph', Yii::$app->formatter->asDate($date, 'MMM'));
                        $graphKwhDataSetPisga[] = ArrayHelper::getValue($row, 'pisga_consumption', 0);
                        $graphKwhDataSetGeva[] = ArrayHelper::getValue($row, 'geva_consumption', 0);
                        $graphKwhDataSetShefel[] = ArrayHelper::getValue($row, 'shefel_consumption', 0);
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
                                               $graphNisDataSetPisga,
                                               $graphNisDataSetGeva,
                                               $graphNisDataSetShefel,
                                           ], "ScoreA");
                        $MyData->addPoints([
                                               Yii::t('common.graph', 'Pisga ({value} NIS)', [
                                                   'value' => Yii::$app->formatter->asNumberFormat($graphNisDataSetPisga),
                                               ]),
                                               Yii::t('common.graph', 'Geva ({value} NIS)', [
                                                   'value' => Yii::$app->formatter->asNumberFormat($graphNisDataSetGeva),
                                               ]),
                                               Yii::t('common.graph', 'Shefel ({value} NIS)', [
                                                   'value' => Yii::$app->formatter->asNumberFormat($graphNisDataSetShefel),
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
        <?php endif; ?>
    <?php endif; ?>

<?php endforeach; ?>


<htmlpagefooter name="HTMLFooter" style="display:none">
    <div style="font-size: 10px; color: #000;">
        <?php echo Yii::t('common.view', 'Page - {PAGENO}'); ?>
    </div>
</htmlpagefooter>
