<?php
use common\helpers\CalculationHelper;
use common\models\Tenant;
use yii\helpers\ArrayHelper;

use common\models\Site;
use common\models\Rate;
use common\helpers\Html;
use common\components\i18n\LanguageSelector;
use common\models\helpers\reports\ReportGenerator;

$direction = LanguageSelector::getAliasLanguageDirection();
?>
<table dir="<?php echo $direction; ?>" style="width:100%;font-size:12px;color:#000;vertical-align:top;margin-bottom:20px;" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td style="padding:5px;width:60%;">
				<strong style="border-bottom:1px solid #000;"><?php echo Yii::t('common.view', 'Tenants readings summary report'); ?>:</strong>
			</td>
			<td style="padding:5px;width:30%;">
				<strong><?php echo Yii::t('common.view', 'Issue date'); ?>:</strong>
			</td>
			<td style="padding:5px;">
				<?php echo Yii::$app->formatter->asDate($report->created_at, 'dd/MM/yy'); ?>
			</td>
		</tr>
		<tr>
			<td style="padding:5px;width:60%;">
				<?php echo Yii::t('common.view', 'To'); ?>
			</td>
			<td style="padding:5px;width:30%;">
				<strong><?php echo Yii::t('common.view', 'Current reading date'); ?>:</strong>
			</td>
			<td style="padding:5px;">
				<?php echo Yii::$app->formatter->asDate($report->to_date, 'dd/MM/yy'); ?>
			</td>
		</tr>
		<tr>
			<td style="padding:5px;width:60%;">
				<strong style="border-bottom:1px solid #000;"><?php echo $site->name; ?></strong>
			</td>
			<td style="padding:5px;width:30%;">
				<strong><?php echo Yii::t('common.view', 'Previous reading date'); ?>:</strong>
			</td>
			<td style="padding:5px;">
				<?php echo Yii::$app->formatter->asDate($report->from_date, 'dd/MM/yy'); ?>
			</td>
		</tr>
	</tbody>
</table>
<?php if(!empty($data)): ?>
	<table dir="<?php echo $direction; ?>" style="border-collapse:collapse;width:100%;font-size:10px;color:#000;vertical-align:top;margin-bottom:15px;" cellpadding="0" cellspacing="0">
		<thead>
			<tr bgcolor="#7e7e7e">
				<th style="color:#fff;width:6%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Row number'); ?>
				</th>
				<th style="color:#fff;width:5%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Tenant ID'); ?>
				</th>
				<th style="color:#fff;width:5%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'To Issue'); ?>
				</th>
				<th style="color:#fff;width:14%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" colspan="2" align="center">
					<?php echo Yii::t('common.view', 'Tenant name'); ?>
				</th>
				<th style="color:#fff;width:7%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Previous reading'); ?>
				</th>
				<th style="color:#fff;width:7%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Current reading'); ?>
				</th>
				<th style="color:#fff;width:7%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Consumption (Kwh)'); ?>
				</th>
				<th style="color:#fff;width:7%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Relative load in Kwh'); ?>
				</th>
				<th style="color:#fff;width:7%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Additions in Kwh'); ?>
				</th>
				<th style="color:#fff;width:7%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Total consumption'); ?>
				</th>
				<th style="color:#fff;width:7%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Total to pay'); ?>
				</th>
				<th style="color:#fff;width:7%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Money addition'); ?>
				</th>
				<th style="color:#fff;width:7%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Fixed payment'); ?>
				</th>
				<th style="color:#fff;width:7%;padding:5px 5px 20px;border-top:1px solid #000;border:1px solid #000;font-size:10px;" align="center">
					<?php echo Yii::t('common.view', 'Total to pay'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$index = 0;
				$name_single = ReportGenerator::RULE_SINGLE_CHANNEL;
				$name_group = ReportGenerator::RULE_GROUP_LOAD;
				$name_fixed = ReportGenerator::RULE_FIXED_LOAD;

				$total_shefel_single_diff = 0;
				$total_shefel_group_diff = 0;
				$total_shefel_fixed_diff = 0;
				$total_shefel_pay = 0;

				$total_geva_single_diff = 0;
				$total_geva_group_diff = 0;
				$total_geva_fixed_diff = 0;
				$total_geva_pay = 0;

				$total_pisga_single_diff = 0;
				$total_pisga_group_diff = 0;
				$total_pisga_fixed_diff = 0;
				$total_pisga_pay = 0;

				$total_fixed_single_diff = 0;
				$total_fixed_group_diff = 0;
				$total_fixed_fixed_diff = 0;
				$total_fixed_pay = 0;

				$fixed_pay = 0;
				$fixed_payment = 0;
				$new_data = [];
				$rates = [];


                /**
                 * REPLACE fixed_payment
                 * on priority `So Rate->Tenant->Site in order of priority`
                 */
                $_fixed_payment = CalculationHelper::isCorrectFixedPayment($data['rates']['fixed_payment']) ? ($data['rates']['fixed_payment']) : 0;
                $related_fixed_payment = CalculationHelper::isCorrectFixedPayment($site->relationSiteBillingSetting['fixed_payment']) ? ($site->relationSiteBillingSetting['fixed_payment']) : 0;
                foreach ($data['rates'] as $rate_id => $tenants)
                {
                    if (is_array($tenants))
                    {
                        foreach($tenants as $tenant_id => $tenant)
                        {

                            if (CalculationHelper::isCorrectFixedPayment($tenant['fixed_payment'])) // tenant
                                $data['rates'][$rate_id][$tenant_id]['fixed_payment'] = $tenant['fixed_payment'];
                            elseif ($related_fixed_payment) // site
                                $data['rates'][$rate_id][$tenant_id]['fixed_payment'] = $related_fixed_payment;
                            elseif ($_fixed_payment) // rates
                                $data['rates'][$rate_id][$tenant_id]['fixed_payment'] = $_fixed_payment;
                        }
                    }
                }
                /* end replace */


				foreach($data['rates'] as $rate_id => $tenants)
                {
                    if (is_array($tenants))
                    {
                        foreach($tenants as $tenant_id => $tenant)
                        {
                            $new_data[$tenant_id][$rate_id] = $tenant;
                            $rate = Rate::findOne($rate_id);
                            $rates[$rate_id]['rate'] = $rate;
                            $rates[$rate_id]['start_date'] = ($rate->start_date < $report->from_date ? $report->from_date : $rate->start_date);
                            $rates[$rate_id]['end_date'] = ($rate->end_date > $report->to_date ? $report->to_date : $rate->end_date);
                        }
                    }
                }
			?>
			<?php foreach($new_data as $tenant_id => $tenants): ?>
                <tr>
                    <?php
                        /** @var Tenant $tenant */
                    $tenant = Tenant::findOne($tenant_id);
                    ?>
                    <td style="padding:5px;border:1px solid #000;" align="center">
                        <?php $index++; ?>
                        <?php echo $index; ?>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" align="center">
                        <?php echo $tenant->id; ?>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" align="center">
                        <?php switch ($tenant->to_issue) {
                            case Site::TO_ISSUE_AUTOMATIC:
                            case Site::TO_ISSUE_MANUAL:
                                echo Yii::t('common.view', 'To issue');
                                break;

                            default:
                                echo Yii::t('common.view', 'Not to issue');
                                break;
                        } ?>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" dir="<?=$direction?>"  colspan="12">
                        <?php echo $tenant->name; ?>
                        <p>
                            <?php if ($entrance_date = $tenant->getEntranceDateReport($report->from_date, $report->to_date)): ?>
                                <?php echo Yii::t('common.view', 'Entry date: {date}', [
                                    'date' => Yii::$app->formatter->asDate($entrance_date, 'dd/MM/yy'),
                                ]); ?>
                            <?php endif; ?>

                            <?php if ($exit_date = $tenant->getExitDateReport($report->from_date, $report->to_date)): ?>
                                <?php echo Yii::t('common.view', 'Exit date: {date}', [
                                    'date' => Yii::$app->formatter->asDate($exit_date, 'dd/MM/yy'),
                                ]); ?>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
                <?php
                $tenant_fixed_payment = 0;
                $tenant_fixed_pay = 0;
                $tenant_total_pay = 0;
                $tenant_total_diff = 0;
                $tenant_fixed_reading_diff = 0;
                $tenant_group_reading_diff = 0;
                $tenant_single_reading_diff = 0;
                $fixed_pay = [];
                $fixed_payment = [];
                ?>
				<?php foreach($tenants as $rate_id => $tenant): ?>
                        <?php $rate = $rates[$rate_id];?>
					<tr>
						<td style="padding:5px;border:1px solid #000;" colspan="15">
                            <?php echo date('d/m/Y',$rates[$rate_id]['start_date']); ?> - <?php echo date('d/m/Y',$rates[$rate_id]['end_date']) ?>
						</td>
					</tr>
<!--					--><?php //foreach($tenant as $tenant_id => $tenant): ?>
						<?php
                            $tenant_single_reading_diff += ArrayHelper::getValue($tenant, "rules.$name_single.reading_diff", 0);
                            $tenant_group_reading_diff += ArrayHelper::getValue($tenant, "rules.$name_group.reading_diff", 0);
                            $tenant_fixed_reading_diff += ArrayHelper::getValue($tenant, "rules.$name_fixed.reading_diff", 0);
                            $tenant_total_diff += ArrayHelper::getValue($tenant, "total_diff", 0);
                            $tenant_total_pay += ArrayHelper::getValue($tenant, "total_pay", 0);
                            $tenant_fixed_pay = ArrayHelper::getValue($tenant, "fixed_pay", 0);
                            $tenant_fixed_payment = ArrayHelper::getValue($tenant, "fixed_payment", 0);

							$total_shefel_single_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.shefel.reading_diff", 0));
							$total_shefel_group_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_group.shefel.reading_diff", 0));
							$total_shefel_fixed_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_fixed.shefel.reading_diff", 0));
							$total_shefel_pay += ArrayHelper::getValue($tenant, "shefel.total_pay", 0);

							$total_geva_single_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.geva.reading_diff", 0));
							$total_geva_group_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_group.geva.reading_diff", 0));
							$total_geva_fixed_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_fixed.geva.reading_diff", 0));
							$total_geva_pay += ArrayHelper::getValue($tenant, "geva.total_pay", 0);

							$total_pisga_single_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.pisga.reading_diff", 0));
							$total_pisga_group_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_group.pisga.reading_diff", 0));
							$total_pisga_fixed_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_fixed.pisga.reading_diff", 0));
							$total_pisga_pay += ArrayHelper::getValue($tenant, "pisga.total_pay", 0);

							$total_fixed_single_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.fixed.reading_diff", 0));
							$total_fixed_group_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_group.fixed.reading_diff", 0));
							$total_fixed_fixed_diff += Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_fixed.fixed.reading_diff", 0));
							$total_fixed_pay += ArrayHelper::getValue($tenant, "fixed.total_pay", 0);

							$fixed_pay[$tenant_id] = ArrayHelper::getValue($tenant, "fixed_pay", 0);
							$fixed_payment[$tenant_id] = ArrayHelper::getValue($tenant, "fixed_payment", 0);
						?>
						<tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
							<td style="padding:5px;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Pisga'); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.pisga.reading_from", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.pisga.reading_to", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.pisga.reading_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_group.pisga.reading_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_fixed.pisga.reading_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "pisga.total_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asPrice(ArrayHelper::getValue($tenant, "pisga.total_pay", 0)); ?>
							</td>
							<td style="border:1px solid #000;" colspan="3"></td>
						</tr>
						<tr>
							<td colspan="4"></td>
							<td style="padding:5px;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Geva'); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.geva.reading_from", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.geva.reading_to", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.geva.reading_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_group.geva.reading_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_fixed.geva.reading_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "geva.total_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asPrice(ArrayHelper::getValue($tenant, "geva.total_pay", 0)); ?>
							</td>
							<td style="border:1px solid #000;" colspan="3"></td>
						</tr>
						<tr>
							<td colspan="4"></td>
							<td style="padding:5px;border:1px solid #000;" align="center">
								<?php echo Yii::t('common.view', 'Shefel'); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.shefel.reading_from", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.shefel.reading_to", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_single.shefel.reading_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_group.shefel.reading_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "rules.$name_fixed.shefel.reading_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asNumberFormat(ArrayHelper::getValue($tenant, "shefel.total_diff", 0)); ?>
							</td>
							<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
								<?php echo Yii::$app->formatter->asPrice(ArrayHelper::getValue($tenant, "shefel.total_pay", 0)); ?>
							</td>
							<td style="border:1px solid #000;" colspan="3"></td>
						</tr>
				<?php endforeach; ?>
                <tr>
                    <td colspan="6"></td>
                    <td style="padding:5px;border:1px solid #000;" align="center">
                        <strong><?php echo Yii::t('common.view', 'Total'); ?>:</strong>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <strong><?php echo Yii::$app->formatter->asNumberFormat($tenant_single_reading_diff); ?></strong>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <strong><?php echo Yii::$app->formatter->asNumberFormat($tenant_group_reading_diff); ?></strong>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <strong><?php echo Yii::$app->formatter->asNumberFormat($tenant_fixed_reading_diff); ?></strong>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <strong><?php echo Yii::$app->formatter->asNumberFormat($tenant_total_diff); ?></strong>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <strong><?php echo Yii::$app->formatter->asPrice($tenant_total_pay); ?></strong>
                    </td>
                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <strong><?php echo Yii::$app->formatter->asPrice($tenant_fixed_pay); ?></strong>
                    </td>


                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <strong>
                            <?php
                                // show on priority `So Rate->Tenant->Site in order of priority`

                                // tenant is by default
                                if (CalculationHelper::isCorrectFixedPayment($site->relationSiteBillingSetting['fixed_payment'])) //site
                                    $tenant_fixed_payment = $site->relationSiteBillingSetting['fixed_payment'];
                                elseif (CalculationHelper::isCorrectFixedPayment($additional_parameters['rates_fixed_payments'])) //rate
                                     $tenant_fixed_payment = $additional_parameters['rates_fixed_payments'];
                            ?>

                            <?php
                                echo Yii::$app->formatter->asPrice($tenant_fixed_payment);
                            ?>
                        </strong>
                    </td>



                    <td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
                        <strong><?php echo Yii::$app->formatter->asPrice($tenant_total_pay + $tenant_fixed_pay + $tenant_fixed_payment); ?></strong>
                    </td>
                </tr>
			<?php endforeach; ?>
			<tr>
				<td style="padding:10px;" colspan="15"></td>
			</tr>
			<tr>
				<td colspan="6"></td>
				<td style="padding:5px;border:1px solid #000;" align="center">
					<strong><?php echo Yii::t('common.view', 'Pisga'); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_pisga_single_diff); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_pisga_group_diff); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_pisga_fixed_diff); ?></strong>
				</td>
				<td style="border:1px solid #000;"></td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asPrice($total_pisga_pay); ?></strong>
				</td>
				<td style="border:1px solid #000;" colspan="3"></td>
			</tr>
			<tr>
				<td colspan="6"></td>
				<td style="padding:5px;border:1px solid #000;" align="center">
					<strong><?php echo Yii::t('common.view', 'Geva'); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_geva_single_diff); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_geva_group_diff); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_geva_fixed_diff); ?></strong>
				</td>
				<td style="border:1px solid #000;"></td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asPrice($total_geva_pay); ?></strong>
				</td>
				<td style="border:1px solid #000;" colspan="3"></td>
			</tr>
			<tr>
				<td colspan="6"></td>
				<td style="padding:5px;border:1px solid #000;" align="center">
					<strong><?php echo Yii::t('common.view', 'Shefel'); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_shefel_single_diff); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_shefel_group_diff); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_shefel_fixed_diff); ?></strong>
				</td>
				<td style="border:1px solid #000;"></td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asPrice($total_shefel_pay); ?></strong>
				</td>
				<td style="border:1px solid #000;" colspan="3"></td>
			</tr>
			<tr>
				<td colspan="6"></td>
				<td style="padding:5px;border:1px solid #000;" align="center">
					<strong><?php echo Yii::t('common.view', 'Total'); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_shefel_single_diff + $total_geva_single_diff + $total_pisga_single_diff); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_shefel_group_diff + $total_geva_group_diff + $total_pisga_group_diff); ?></strong>
				</td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asNumberFormat($total_shefel_fixed_diff + $total_geva_fixed_diff + $total_pisga_fixed_diff + $total_fixed_fixed_diff); ?></strong>
				</td>
				<td style="border:1px solid #000;"></td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asPrice($total_shefel_pay + $total_geva_pay + $total_pisga_pay + $total_fixed_pay); ?></strong>
				</td>
				<td style="border:1px solid #000;" colspan="2"></td>
				<td style="padding:5px;border:1px solid #000;" align="center" dir="ltr">
					<strong><?php echo Yii::$app->formatter->asPrice($total_shefel_pay + $total_geva_pay + $total_pisga_pay + $total_fixed_pay + array_sum($fixed_pay) + array_sum($fixed_payment)); ?></strong>
				</td>
			</tr>
		</tbody>
	</table>
<?php endif; ?>
<htmlpagefooter name="HTMLFooter" style="display:none">
	<div style="font-size: 10px; color: #000;">
		<?php echo Yii::t('common.view', 'Page - {PAGENO}'); ?>
	</div>
</htmlpagefooter>