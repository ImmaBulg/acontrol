<?php
/**
 * @var RuleData $rule
 * @var \common\components\i18n\Formatter $formatter
 */

use common\components\calculators\data\RuleData;
use common\helpers\Html;
use common\models\RateType;
use common\components\i18n\LanguageSelector;

$formatter = Yii::$app->formatter;
$direction = LanguageSelector::getAliasLanguageDirection();
?>
<table dir="<?= $direction; ?>">
    <thead>
    <tr>
        <td style="padding:5px;" colspan="7">
            <strong>
                <?php echo implode(' - ', array_filter([
                                                           $rule->getRule()->name,
                                                           Html::tag('span', $rule->getRule()->getMeterName() . ' - ' .
                                                                             $rule->getRule()->getChannelName()),
                                                       ])); ?>
            </strong>
        </td>
    </tr>
    </thead>
</table>
<?php foreach($rule->getData() as $type => $data) : ?>

    <? if(!empty($data)): ?>
    <?
    if($type == 'irregular_data') {
      if ( $data[0]->getMultipliedData()[0]->getPisgaConsumption() == 0
           and
           $data[0]->getMultipliedData()[0]->getGevaConsumption() == 0
           and
           $data[0]->getMultipliedData()[0]->getShefelConsumption() == 0 ) {
        // Skip empty Irregular hours
        continue;
      }
    }
    ?>
        <table dir="<?= $direction; ?>"
               style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:15px;"
               cellpadding="0" cellspacing="0">
            <thead>
            <?php if(array_key_exists('irregular_data', $rule->getData())): ?>
                <?php if ($rule->getData()['irregular_data'][0]->getMultipliedData()[0]->getPisgaConsumption() != 0 || $rule->getData()['irregular_data'][0]->getMultipliedData()[0]->getGevaConsumption() != 0 || $rule->getData()['irregular_data'][0]->getMultipliedData()[0]->getShefelConsumption() != 0): ?>
                    <tr>
                        <td style="padding:5px;" colspan="7">
                            <strong>
                                <?= RuleData::getDataLabel($type) . ' ' . $rule->getTimeRange($type) . '' ?>
                            </strong>
                        </td>
                    </tr>
                 <?php endif; ?>
            <?php endif; ?>
            <tr bgcolor="#7e7e7e">
                <th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?= Yii::t('common.view', 'Previous reading date'); ?>
                </th>
                <th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?= Yii::t('common.view', 'Current reading date'); ?>
                </th>
                <th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?= Yii::t('common.view', 'Consumption type'); ?>
                </th>
                <th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?= Yii::t('common.view', 'Total air consumption'); ?>

                </th>
                <th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?= Yii::t('common.view', 'Total consumption in Kwh'); ?>
                </th>
                <th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?= Yii::t('common.view', 'Group load'); ?>
                </th>
                <th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?= Yii::t('common.view', 'Money addition'); ?>
                </th>
                <th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?= Yii::t('common.view', 'Price per 1 Kwh in Agorot'); ?>
                </th>
                <th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?= Yii::t('common.view', 'Total to pay'); ?>
                </th>
                <th style="width:15%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <!--                    --><?//= Yii::t('common.view', 'COP'); ?>
                    <?= Yii::t('common.view', 'COP'); ?>
                </th>
            </tr>
            </thead>
            <tbody>

            <?php
            /**
             * @var \common\components\calculators\data\RatedData[] $data
             */
            foreach($data as $data_block): ?>
                <?php foreach($data_block->getMultipliedData() as $multiplied_data) : ?>

                    <tr>
                        <td style="padding:10px 5px 5px;border:1px solid #000;" align="center">
                            <?= $multiplied_data->getStartDate()->format('d-m-Y') ?>
                        </td>
                        <td style="padding:10px 5px 5px;border:1px solid #000;" align="center">
                            <?= $multiplied_data->getEndDate()->format('d-m-Y') ?>
                        </td>
                        <td style="padding:5px;border:1px solid #000;" colspan="3"></td>
                    </tr>

                    <?php if($data_block->getRate()->rateType->type == RateType::TYPE_TAOZ): ?>
                        <tr>
                            <td style="padding:5px;border:1px solid #000;vertical-align: middle" align="center"
                                rowspan="3">
                                <?= $formatter->asNumberFormat($multiplied_data->getReadingFrom()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;vertical-align: middle" rowspan="3" align="center">
                                <?= $formatter->asNumberFormat($multiplied_data->getReadingTo()) ?>
                            </td>
                             <td style="padding:5px;border:1px solid #000;" align="center">
                                <?php echo Yii::t('common.view', 'Pisga'); ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($multiplied_data->getPisgaConsumption()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($multiplied_data->getAirPisgaConsumption()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($data[0]->getPisgaFixedRule()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($data_block->getPisgaPrice()) ?>
                            </td>

                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asPrice($multiplied_data->getPisgaPay()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;vertical-align: middle" align="center">
                                <?= $formatter->asNumberFormat($rule->cop_pisga); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?php echo Yii::t('common.view', 'Geva'); ?>
                            </td>

                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($multiplied_data->getGevaConsumption()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($multiplied_data->getAirGevaConsumption()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($data_block->getGevaFixedRule()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($data_block->getGevaPrice()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asPrice($multiplied_data->getGevaPay()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($rule->cop_geva); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?php echo Yii::t('common.view', 'Shefel'); ?>
                            </td>

                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($multiplied_data->getShefelConsumption()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($multiplied_data->getAirShefelConsumption()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($data_block->getShefelFixedRule()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($data_block->getShefelPrice()) ?>
                            </td>

                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asPrice($multiplied_data->getShefelPay()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($rule->cop_shefel); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px;border:1px solid #000;" align="center" colspan="3">
                                <?php echo Yii::t('common.view', 'Total'); ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($multiplied_data->getPisgaConsumption() + $multiplied_data->getGevaConsumption() + $multiplied_data->getShefelConsumption()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($multiplied_data->getAirPisgaConsumption() + $multiplied_data->getAirGevaConsumption() + $multiplied_data->getAirShefelConsumption()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">

                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asNumberFormat($data[0]->getFixedRule()) ?>
                            </td>
                            <td style="padding:5px;border:1px solid #000;" align="center">
                            </td>

                            <td style="padding:5px;border:1px solid #000;" align="center">
                                <?= $formatter->asPrice($multiplied_data->getPisgaPay() + $multiplied_data->getGevaPay() + $multiplied_data->getShefelPay()) ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endforeach; ?>
