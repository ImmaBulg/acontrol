<?php

use common\components\i18n\LanguageSelector;
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
            <strong><?php echo Yii::t('common.view', 'Financial report'); ?></strong>
        </td>
    </tr>
    <tr>
        <td style="padding:5px;" colspan="2">
            <?php echo Yii::t('common.view', 'To'); ?>: <?php echo $data['site_owner']; ?>
        </td>
    </tr>
    <tr>
        <td style="padding:5px;width:60%;">
            <?php echo Yii::t('common.view', 'Summary report for site'); ?>: <?php echo $data['site']; ?>
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
            <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;width:6%;" align="center">
                <?php echo Yii::t('common.view', 'Row number'); ?>
            </th>
            <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Tenant ID'); ?>
            </th>
            <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Tenant name'); ?>
            </th>
            <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;width:12%;" align="center">
                <?php echo Yii::t('common.view', 'Meter ID'); ?>
            </th>
            <?php if ($data['params']['column_total_pay_single_channel_rules']) { ?>
                <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?php echo Yii::t('common.view', 'Total to pay based on Single rules'); ?>
                </th>
            <?php } ?>
            <?php if ($data['params']['column_total_pay_group_load_rules']) { ?>
                <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?php echo Yii::t('common.view', 'Total to pay based on Group load rules'); ?>
                </th>
            <?php } ?>
            <?php if ($data['params']['column_total_pay_fixed_load_rules']) { ?>
                <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?php echo Yii::t('common.view', 'Total to pay based on Fixed load rules'); ?>
                </th>
            <?php } ?>
            <?php if ($data['params']['column_fixed_payment']) { ?>
                <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?php echo Yii::t('common.view', 'Fixed payment'); ?>
                </th>
            <?php } ?>
            <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Total'); ?>
            </th>
            <?php if ($data['params']['is_vat_included']) { ?>
                <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?php echo Yii::t('common.view', 'VAT'); ?>
                </th>
                <th style="color:#fff;padding:5px;font-weight:normal;border:1px solid #000;" align="center">
                    <?php echo Yii::t('common.view', 'Total (including VAT)'); ?>
                </th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php $row_number = 1; ?>
        <?php foreach ($data['tenants'] ? $data['tenants'] : [] as $tenant) { ?>
            <tr>
                <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $row_number++; ?></td>
                <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $tenant['tenant_id']; ?></td>
                <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $tenant['tenant_name']; ?></td>
                <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $tenant['meter_id']; ?></td>
                <?php if ($data['params']['column_total_pay_single_channel_rules']) { ?>
                    <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $formatter->asPrice($tenant['total_single_rules']); ?></td>
                <?php } ?>
                <?php if ($data['params']['column_total_pay_group_load_rules']) { ?>
                    <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $formatter->asPrice($tenant['total_group_rules']); ?></td>
                <?php } ?>
                <?php if ($data['params']['column_total_pay_fixed_load_rules']) { ?>
                    <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $formatter->asPrice($tenant['total_fixed_rules']); ?></td>
                <?php } ?>
                <?php if ($data['params']['column_fixed_payment']) { ?>
                    <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $formatter->asPrice($tenant['fixed_payment']); ?></td>
                <?php } ?>
                <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $formatter->asPrice($tenant['total']); ?></td>
                <?php if ($data['params']['is_vat_included']) { ?>
                    <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $formatter->asPrice($tenant['vat']); ?></td>
                    <td style="padding:5px;border:1px solid #000;" align="center"><?php echo $formatter->asPrice($tenant['total_vat_incl']); ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
        <tr>
            <td style="padding-top: 20px"></td>
        </tr>
        <tr>
            <?php if ($data['params']['is_vat_included']) { ?>
                <td></td>
                <td></td>
            <?php } ?>
            <?php if ($data['params']['column_fixed_payment']) { ?>
                <td></td>
            <?php } ?>
            <?php if ($data['params']['column_total_pay_single_channel_rules']) { ?>
                <td></td>
            <?php } ?>
            <?php if ($data['params']['column_total_pay_group_load_rules']) { ?>
                <td></td>
            <?php } ?>
            <?php if ($data['params']['column_total_pay_fixed_load_rules']) { ?>
                <td></td>
            <?php } ?>
            <td></td>
            <td></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo Yii::t('common.view', 'Total'); ?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php echo $formatter->asPrice($data['site_total']); ?>
            </td>
        </tr>
        <?php if ($data['params']['is_vat_included']) { ?>
            <tr>
                <?php if ($data['params']['column_fixed_payment']) { ?>
                    <td></td>
                <?php } ?>
                <?php if ($data['params']['column_total_pay_single_channel_rules']) { ?>
                    <td></td>
                <?php } ?>
                <?php if ($data['params']['column_total_pay_group_load_rules']) { ?>
                    <td></td>
                <?php } ?>
                <?php if ($data['params']['column_total_pay_fixed_load_rules']) { ?>
                    <td></td>
                <?php } ?>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo Yii::t('common.view', 'VAT'); ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asPercent(\common\models\helpers\reports\ReportGeneratorNis::VAT * 1000); ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asPrice($data['site_total_vat']); ?>
                </td>
            </tr>
            <tr>
                <?php if ($data['params']['column_fixed_payment']) { ?>
                    <td></td>
                <?php } ?>
                <?php if ($data['params']['column_total_pay_single_channel_rules']) { ?>
                    <td></td>
                <?php } ?>
                <?php if ($data['params']['column_total_pay_group_load_rules']) { ?>
                    <td></td>
                <?php } ?>
                <?php if ($data['params']['column_total_pay_fixed_load_rules']) { ?>
                    <td></td>
                <?php } ?>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo Yii::t('common.view', 'Total (including VAT) '); ?>
                </td>
                <td style="padding:5px;border:1px solid #000;" align="center"></td>
                <td style="padding:5px;border:1px solid #000;" align="center">
                    <?php echo $formatter->asPrice($data['site_total_vat_incl']); ?>
                </td>
            </tr>
        <?php } ?>
<!--        <tr>
            <?php /*if ($data['params']['column_fixed_payment']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['column_total_pay_single_channel_rules']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['column_total_pay_group_load_rules']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['column_total_pay_fixed_load_rules']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['is_vat_included']) { */?>
                <td></td>
                <td></td>
            <?php /*} */?>
            <td></td>
            <td></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php /*echo Yii::t('common.view', 'Electric company bill'); */?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php /*echo $formatter->asPrice($data['site_electrical_company_price']); */?>
            </td>
        </tr>
        <tr>
            <?php /*if ($data['params']['column_fixed_payment']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['column_total_pay_single_channel_rules']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['column_total_pay_group_load_rules']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['column_total_pay_fixed_load_rules']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['is_vat_included']) { */?>
                <td></td>
                <td></td>
            <?php /*} */?>
            <td></td>
            <td></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php /*echo Yii::t('common.view', 'Diff in NIS'); */?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php /*echo $formatter->asPrice($data['diff']); */?>
            </td>
        </tr>
        <tr>
            <?php /*if ($data['params']['column_fixed_payment']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['column_total_pay_single_channel_rules']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['column_total_pay_group_load_rules']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['column_total_pay_fixed_load_rules']) { */?>
                <td></td>
            <?php /*} */?>
            <?php /*if ($data['params']['is_vat_included']) { */?>
                <td></td>
                <td></td>
            <?php /*} */?>
            <td></td>
            <td></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php /*echo Yii::t('common.view', 'Diff in %'); */?>
            </td>
            <td style="padding:5px;border:1px solid #000;" align="center"></td>
            <td style="padding:5px;border:1px solid #000;" align="center">
                <?php /*echo $formatter->asNumberFormat($data['diff_percent']).'%'; */?>
            </td>
        </tr>-->
    </tbody>
</table>
