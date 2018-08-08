<?php
/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 25.07.2017
 * Time: 16:59
 * @var \common\components\calculators\data\SiteData $data
 *
 */
use Carbon\Carbon;
use common\components\i18n\LanguageSelector;

$direction = LanguageSelector::getAliasLanguageDirection();
$first_tenant_key = key($data->getTenantData());
?>

<? foreach($data->getTenantData() as $index => $tenant_data) : ?>
    <?php if($index != $first_tenant_key): ?>
        <pagebreak />
    <?php endif; ?>
    <table dir="<?php echo $direction; ?>"
           style="width:100%;font-size:12px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0"
           cellspacing="0">
        <tbody>
        <tr>
            <td style="padding:5px;width:55%;" rowspan="3">
                <p>
                    <strong><?= Yii::t('common.view', 'Tenant name'); ?>:</strong>
                    <?= $tenant_data->getTenant()->name; ?>
                </p>
                <p><strong><?php echo Yii::t('common.view', 'Site name'); ?>:</strong>
                    <?= $data->getSite()->name; ?>
                </p>
            </td>
            <td style="padding:5px;width:30%;">
                <?= Yii::t('common.view', 'Issue date'); ?>:
            </td>
            <td style="padding:5px;width:30%;">
                <?= Carbon::now()->format('m-d-Y') ?>
            </td>
        </tr>
        
        <?php if($exit_date = $tenant_data->getTenant()->getExitDateReport($data->getEndDate())): ?>
            <tr>
                <td style="padding:5px;width:30%;">
                    <?= Yii::t('common.view', 'Current reading date'); ?>:
                </td>
                <td style="padding:5px;width:30%;">
                    <?= $exit_date->format('m-d-Y') ?>
                </td>
            </tr>
        <? else: ?>
            <tr>
                <td></td>
            </tr>
        <? endif; ?>
        <?php if($entrance_date =
            $tenant_data->getTenant()->getEntranceDateReport($data->getStartDate())
        ) : ?>
            <tr>
                <td style="padding:5px;width:30%;">
                    <?= Yii::t('common.view', 'Previous reading date'); ?>:
                </td>
                <td style="padding:5px;width:30%;">
                    <?= $entrance_date->format('m-d-Y') ?>
                </td>
            </tr>
        <?php else : ?>
            <tr>
                <td></td>
            </tr>
        <?php endif ?>
        <tr>
            <td style="padding:20px 5px 5px;" colspan="2">
                <strong>
                    <?php echo Yii::t('common.view', 'Dear customer, here is the billing report for the period of'); ?>
                    <?= $tenant_data->getStartDate()->format('d-m-Y'); ?>
                    - <?= $tenant_data->getEndDate()->format('d-m-Y') ?>:
                </strong>
            </td>
        </tr>

        </tbody>
    </table>
    <?php foreach($tenant_data->getRuleData() as $rule_data) : ?>
        <?= $this->render('tenant-bills-rule-view', ['rule' => $rule_data]); ?>
    <? endforeach; ?>
    <?php if ($tenant_data->getTenant()->relationRateType->is_taoz): ?>
        <?= $this->render('tenant-bills-total-tenant-view',['tenant_data'=>$tenant_data]) ?>
    <?php else: ?>
        <?= $this->render('tenant-bills-total-tenant-view-single',['tenant_data'=>$tenant_data]) ?>
    <?php endif;?>
<?php endforeach; ?>
