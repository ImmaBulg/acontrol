<?php

use common\components\i18n\LanguageSelector;
use Carbon\Carbon;

/**
 * @var \common\components\i18n\Formatter $formatter
 */
$formatter = Yii::$app->formatter;
$direction = LanguageSelector::getAliasLanguageDirection();

?>
<table dir="<?php echo $direction; ?>" style="width:100%;font-size:12px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td style="padding:5px;font-size:14px;" colspan="2">
            <strong><?php echo Yii::t('common.view', 'NIS + Kwh report'); ?></strong>
        </td>
    </tr>
    <tr>
        <td style="padding:5px;" colspan="2">
            <?php echo Yii::t('common.view', 'To'); ?>: <?php echo $data['site_owner']; ?>
        </td>
    </tr>
    <tr>
        <td style="padding:5px;width:60%">
            <?php echo $data['site']; ?>
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
    <tr>
        <td style="padding:5px;width:60%">
            <?php echo $data['electric_company_id']; ?>
        </td>
        <td style="padding:5px;"></td>
    </tr>
    </tbody>
</table>
<table dir="<?= $direction; ?>"
       style="border-collapse:collapse;width:100%;font-size:11px;color:#000;vertical-align:top;margin-bottom:15px;"
       cellpadding="0" cellspacing="0">
    <thead>
    <tr bgcolor="#7e7e7e">
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?= Yii::t('common.view', '№'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?= Yii::t('common.view', 'Tenant ID'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?= Yii::t('common.view', 'Tenant name'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?= Yii::t('common.view', 'Meter number'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" colspan="3">
            <?= Yii::t('common.view', 'Pisga Kwh'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" colspan="3">
            <?= Yii::t('common.view', 'Geva Kwh'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" colspan="3">
            <?= Yii::t('common.view', 'Shefel Kwh'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?= Yii::t('common.view', 'Total consumption in Kwh'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?= Yii::t('common.view', 'Group loads Kwh'); ?>
        </th>
        <!--<th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?/*= Yii::t('common.view', 'Loads Kwh'); */?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?/*= Yii::t('common.view', 'Added money'); */?>
        </th>-->

        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?= Yii::t('common.view', 'Money addition'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?= Yii::t('common.view', 'Fixed payment'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?= Yii::t('common.view', 'VAT 17%'); ?>
        </th>
        <th style="vertical-align:bottom; width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center" rowspan="2">
            <?= Yii::t('common.view', 'Total to pay'); ?>
        </th>
    </tr>
    <tr bgcolor="#7e7e7e">
        <th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
            <?= Yii::t('common.view', 'Air consumption'); ?>
        </th>
        <th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
            <?= Yii::t('common.view', 'Electricity consumption'); ?>
        </th>
        <th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
            <?= Yii::t('common.view', 'Total pay before VAT'); ?>
        </th>
        <th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
            <?= Yii::t('common.view', 'Air consumption'); ?>
        </th>
        <th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
            <?= Yii::t('common.view', 'Electricity consumption'); ?>
        </th>
        <th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
            <?= Yii::t('common.view', 'Total pay before VAT'); ?>
        </th>
        <th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
            <?= Yii::t('common.view', 'Air consumption'); ?>
        </th>
        <th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
            <?= Yii::t('common.view', 'Electricity consumption'); ?>
        </th>
        <th style="width:10%;color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
            <?= Yii::t('common.view', 'Total pay before VAT'); ?>
        </th>
    </tr>
    </thead>
    <tbody>
        <?php $number = 1; ?>
        <?php foreach ($data['tenants'] as $key => $tenant) { ?>
            <?php $data_types = ['regular', 'irregular']; ?>
            <?php $firs_rule_key = key($tenant['rules']);?>
            <?php foreach ($tenant['rules'] as $rule_id => $rule) { ?>
                <?php foreach ($data_types as $data_type) { ?>
                    <?php if (isset($rule[$data_type])) { ?>
                        <tr>
                            <?php if ($firs_rule_key == $rule_id) { ?>
                                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $number++; ?></td>
                                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $tenant['tenant_id']; ?></td>
                                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $tenant['tenant_name']; ?></td>
                            <?php } else {?>
                                <td></td>
                                <td></td>
                                <td></td>
                            <?php } ?>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $rule['meter']; ?></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asNumberFormat($rule[$data_type]['pisga_consumption']); ?></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice($rule[$data_type]['pisga_pay']); ?></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asNumberFormat($rule[$data_type]['geva_consumption']); ?></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice($rule[$data_type]['geva_pay']); ?></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asNumberFormat($rule[$data_type]['shefel_consumption']); ?></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice($rule[$data_type]['shefel_pay']); ?></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                            <!--<td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>-->
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo Yii::t('common.view', 'Total'); ?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asNumberFormat($tenant['total']['pisga_consumption']); ?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice($tenant['total']['pisga_pay']); ?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asNumberFormat($tenant['total']['geva_consumption']); ?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice($tenant['total']['geva_pay']); ?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asNumberFormat($tenant['total']['shefel_consumption']); ?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice($tenant['total']['shefel_pay']); ?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice($data['site_total']['total_air_pisga'] + $data['site_total']['total_air_geva'] + $data['site_total']['total_air_shefel']); ?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>

                <!--<td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>-->
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice($tenant['total']['total_fixed_rules']); ?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice($tenant['fixed_payment']); ?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice(($tenant['fixed_payment'] + $tenant['total']['total_pay']) * \common\models\helpers\reports\ReportGeneratorNisKwh::VAT);?></td>
                <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice(
                        (($tenant['fixed_payment'] + $tenant['total']['total_pay']) * \common\models\helpers\reports\ReportGeneratorNisKwh::VAT) + ($tenant['fixed_payment'] + $tenant['total']['total_pay'])
                ); ?></td>
            </tr>
        <?php } ?>
        <tr>
           <td style="padding-top: 40px" colspan="18"></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?= Yii::t('common.view', 'Total'); ?></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['site_total']['total_air_pisga']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['site_total']['total_electricity_pisga']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asPrice($data['site_total']['total_pay_pisga']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['site_total']['total_air_geva']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['site_total']['total_electricity_geva']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asPrice($data['site_total']['total_pay_geva']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['site_total']['total_air_shefel']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['site_total']['total_electricity_shefel']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asPrice($data['site_total']['total_pay_shefel']); ?>
            </td>
             <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asPrice($data['site_total']['total_air_pisga'] + $data['site_total']['total_air_geva'] + $data['site_total']['total_air_shefel']); ?></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['site_total']['total_electricity_consumption']); ?>
            </td>
            <!--<td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>-->
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asPrice($data['site_total']['fixed_payment']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asPrice($data['site_total']['total_payment_without_tax']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asPrice($data['site_total']['tax']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asPrice($data['site_total']['total_to_pay']);?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?= Yii::t('common.view', 'Diff'); ?></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['diff']['pisga']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['diff']['geva']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asNumberFormat($data['diff']['shefel']); ?></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asPrice($data['diff']['price']);?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?= Yii::t('common.view', 'Diff'); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['electric_company_pisga']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['electric_company_geva']); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"><?php echo $formatter->asNumberFormat($data['electric_company_shefel']); ?></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asPrice($data['electric_company_price']);?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?= Yii::t('common.view', 'Diff precent'); ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['diff_percent']['pisga']).'%'; ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['diff_percent']['geva']).'%'; ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['diff_percent']['shefel']).'%'; ?>
            </td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;"></td>
            <td align="center" style="color:black;padding:5px;font-weight:normal;border:1px solid #000;">
                <?php echo $formatter->asNumberFormat($data['diff_percent']['price']).'%';?>
            </td>
        </tr>
    </tbody>
</table>
<table>
    <tbody>

    </tbody>
</table>