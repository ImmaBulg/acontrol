<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 25.07.2017
 * Time: 23:20
 * @var $tenant_data \common\components\calculators\data\TenantData
 * @var $formatter  \common\components\i18n\Formatter
 * @var \common\components\View $this
 */

use common\components\i18n\LanguageSelector;
use common\models\RateType;

$formatter = Yii::$app->formatter;
$direction = LanguageSelector::getAliasLanguageDirection();
?>

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
        <th style="padding:5px;" colspan="4"></th>

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
            <th style="padding:5px;" colspan="4"></th>

            <td style="padding:5px;border:1px solid #000;" align="center">
                <?= $formatter->asNumberFormat($tenant_data->getConsumptionInKwh()); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?= $formatter->asPrice($tenant_data->getPay() + $tenant_data->getMoneyAddition()); ?>
            </td>
    </tr>
    </tbody>
</table>
<table dir="<?php echo $direction; ?>"
       style="border-left:1px solid #000;border-right:1px solid #000;border-top:1px solid #000;width:100%;font-size:11px;color:#000;vertical-align:top;"
       cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td style="padding:5px;width:55%;" rowspan="3">

        </td>
        <td style="width:30%;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
            <strong><?php echo Yii::t('common.view', 'Fixed payment'); ?></strong>
        </td>
        <td style="width:15%;padding:5px;" align="center" dir="ltr">
            <?php echo $formatter->asPrice($tenant_data->getFixedPrice()); ?>
        </td>
    </tr>
    <tr>
        <td style="width:30%;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
            <strong><?php echo Yii::t('common.view', 'Total'); ?></strong>
        </td>
        <td style="width:15%;padding:5px;" align="center" dir="ltr">
            <?php echo $formatter->asPrice($tenant_data->getTotalPayWithFixed() + $tenant_data->getMoneyAddition()); ?>
        </td>
    </tr>
    <tr>
        <td style="width:30%;border-left:1px solid #000;border-right:1px solid #000;padding:5px;">
            <strong><?php echo Yii::t('common.view', 'VAT'); ?> <span
                        dir="ltr"><?php echo $formatter->asPercentage(\common\components\calculators\data\TenantData::VAT_PERCENT); ?></span></strong>
        </td>
        <td style="width:15%;padding:5px;" align="center" dir="ltr">
            <?php echo $formatter->asPrice($tenant_data->getVat()); ?>
        </td>
    </tr>
    <tr>
        <td style="padding:5px;width:55%;border-top:1px solid #000;border-bottom:1px solid #000;">
            <strong><?= Yii::t('common.view', 'Includes VAT'); ?></strong>
        </td>
        <td style="width:30%;padding:5px;border: 1px solid #000;">
            <strong><?php echo Yii::t('common.view', 'Total to pay'); ?></strong>
        </td>
        <td style="width:15%;border-top:1px solid #000;padding:5px;border-bottom:1px solid #000;"
            align="center" dir="ltr">
            <?php echo $formatter->asPrice($tenant_data->getTotalPayWithVat() + $tenant_data->getMoneyAddition()); ?>
        </td>
    </tr>
    </tbody>
</table>


