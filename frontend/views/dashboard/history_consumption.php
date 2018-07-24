<?php

use yii\web\JsExpression;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\ActiveForm;
use common\widgets\chart\Chart;

$this->title = Yii::t('frontend.view', 'History consumption');
?>
<div class="wrap">
	<?php echo $this->render('_header'); ?>
	<div id="main">
		<div class="container">
			<div class="col-lg-12">
				<?php echo $this->render('_switch', [
					'action' => ['history-consumption'],
					'user' => $user,
					'form' => $form_switch,
					'show_clients' => true,
					'show_sites' => true,
					'show_tenants' => true,
					'show_meters' => true,
					'show_channels' => true,
				]); ?>
				<h1 class="page-header"><?php echo Yii::t('frontend.view', 'History consumption'); ?></h1>
				<?php $form_active = ActiveForm::begin([
					'method' => 'GET',
					'action' => ['history-consumption'],
					'options' => ['data' => ['pjax' => true]],
					'enableOneProcessSubmit' => true,
				]); ?>
					<div class="row">
						<div class="col-md-3">
							<?php echo $form_active->field($form, 'from_date')->dateInput(['placeholder' => $form->getAttributeLabel('from_date')], [
								'max' => Yii::$app->formatter->asDate(time(), 'dd-MM-yyyy'),
							])->label(false); ?>
							<?php echo $form_active->field($form, 'compare')->inline(true)->radioList($form::getListCompares()); ?>
						</div>
						<div class="col-md-3">
							<?php echo $form_active->field($form, 'to_date')->dateInput(['placeholder' => $form->getAttributeLabel('to_date')], [
								'max' => Yii::$app->formatter->asDate(time(), 'dd-MM-yyyy'),
							])->label(false); ?>
							<?php echo $form_active->field($form, 'drilldown')->inline(true)->radioList($form::getListDrilldowns()); ?>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<?php echo Html::submitButton(Yii::t('frontend.view', 'Calculate'), ['class' => 'btn btn-success btn-block']); ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<?php echo $form_active->field($form, 'compare_from_date', [
								'options' => ['class' => 'form-group' . (($form->compare == true) ? '' : ' hidden')],
							])->dateInput([
								'disabled' => !($form->compare == true),
								'placeholder' => $form->getAttributeLabel('compare_from_date'),
							], [
								'max' => Yii::$app->formatter->asDate(time(), 'dd-MM-yyyy'),
							])->label(false); ?>
						</div>
						<div class="col-md-3">
							<?php echo $form_active->field($form, 'compare_to_date', [
								'options' => ['class' => 'form-group' . (($form->compare == true) ? '' : ' hidden')],
							])->dateInput([
								'disabled' => !($form->compare == true),
								'placeholder' => $form->getAttributeLabel('compare_to_date'),
							], [
								'max' => Yii::$app->formatter->asDate(time(), 'dd-MM-yyyy'),
							])->label(false); ?>
						</div>
					</div>
					<?php $this->registerJs('jQuery("input[name=\"' .Html::getInputName($form, 'compare'). '\"").on("change", function(){
						if (this.value == 1) {
							jQuery("#' .Html::getInputId($form, 'compare_from_date'). '").attr("disabled", false);
							jQuery("#' .Html::getInputId($form, 'compare_to_date'). '").attr("disabled", false);
							jQuery("#' .Html::getInputId($form, 'compare_from_date'). '").parents(".form-group").removeClass("hidden");
							jQuery("#' .Html::getInputId($form, 'compare_to_date'). '").parents(".form-group").removeClass("hidden");
						} else {
							jQuery("#' .Html::getInputId($form, 'compare_from_date'). '").attr("disabled", true);
							jQuery("#' .Html::getInputId($form, 'compare_to_date'). '").attr("disabled", true);
							jQuery("#' .Html::getInputId($form, 'compare_from_date'). '").parents(".form-group").addClass("hidden");
							jQuery("#' .Html::getInputId($form, 'compare_to_date'). '").parents(".form-group").addClass("hidden");
						}
					});'); ?>
				<?php ActiveForm::end(); ?>

				<?php if ($data_provider->totalCount): ?>
					<div class="well text-center">
						<h3 style="margin: 0;">
							<?php echo Yii::t('frontend.view', 'Total consumption for period {from} to {to}', [
								'from' => $form->from_date,
								'to' => $form->to_date,
							]); ?>
						</h3>
					</div>

					<div class="row">
						<div class="col-md-4">
							<div class="box-total">
								<div class="inner">
									<span>
										<?php echo Yii::t('frontend.view', 'Pisga'); ?>
									</span>
									<strong>
										<?php echo Yii::t('frontend.view', '{value} Kwh', [
											'value' => Yii::$app->formatter->asNumberFormat(array_reduce($data_provider->getModels(), function($carry, $item) {
												$carry += ArrayHelper::getValue($item, 'pisga', 0);
												return $carry;
											})),
										]);  ?>
									</strong>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="box-total">
								<div class="inner">
									<span>
										<?php echo Yii::t('frontend.view', 'Geva'); ?>
									</span>
									<strong>
										<?php echo Yii::t('frontend.view', '{value} Kwh', [
											'value' => Yii::$app->formatter->asNumberFormat(array_reduce($data_provider->getModels(), function($carry, $item) {
												$carry += ArrayHelper::getValue($item, 'geva', 0);
												return $carry;
											})),
										]);  ?>
									</strong>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="box-total">
								<div class="inner">
									<span>
										<?php echo Yii::t('frontend.view', 'Shefel'); ?>
									</span>
									<strong>
										<?php echo Yii::t('frontend.view', '{value} Kwh', [
											'value' => Yii::$app->formatter->asNumberFormat(array_reduce($data_provider->getModels(), function($carry, $item) {
												$carry += ArrayHelper::getValue($item, 'shefel', 0);
												return $carry;
											})),
										]);  ?>
									</strong>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<?php $consumptionChartSeries = []; ?>

							<?php if ($compared_data_provider->totalCount): ?>
								<?php $consumptionChartSeries[] = [
									'name' => Yii::t('frontend.view', 'Pisga (old)'),
									'color' => 'rgba(216, 22, 71, 0.5)',
									'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'Kwh')],
									'stack' => 'old',
									'data' => ArrayHelper::map($compared_data_provider->getKeys(), function($key) {
										return $key;
									}, function($key) use ($compared_data_provider){
										$item = ArrayHelper::getValue($compared_data_provider->getModels(), $key);
										return [
											'y' => Yii::$app->formatter->asRound($item['pisga']),
											'name' => Yii::$app->formatter->asDate($item['timestamp']),
										];
									}),
								]; ?>
								<?php $consumptionChartSeries[] = [
									'name' => Yii::t('frontend.view', 'Geva (old)'),
									'color' => 'rgba(20, 183, 124, 0.5)',
									'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'Kwh')],
									'stack' => 'old',
									'data' => ArrayHelper::map($compared_data_provider->getKeys(), function($key) {
										return $key;
									}, function($key) use ($compared_data_provider){
										$item = ArrayHelper::getValue($compared_data_provider->getModels(), $key);
										return [
											'y' => Yii::$app->formatter->asRound($item['geva']),
											'name' => Yii::$app->formatter->asDate($item['timestamp']),
										];
									}),
								]; ?>
								<?php $consumptionChartSeries[] = [
									'name' => Yii::t('frontend.view', 'Shefel (old)'),
									'color' => 'rgba(20, 156, 211, 0.5)',
									'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'Kwh')],
									'stack' => 'old',
									'data' => ArrayHelper::map($compared_data_provider->getKeys(), function($key) {
										return $key;
									}, function($key) use ($compared_data_provider){
										$item = ArrayHelper::getValue($compared_data_provider->getModels(), $key);
										return [
											'y' => Yii::$app->formatter->asRound($item['shefel']),
											'name' => Yii::$app->formatter->asDate($item['timestamp']),
										];
									}),
								]; ?>
							<?php endif; ?>

							<?php $consumptionChartSeries[] = [
								'name' => Yii::t('frontend.view', 'Pisga'),
								'color' => 'rgba(216, 22, 71, 1)',
								'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'Kwh')],
								'stack' => 'current',
								'data' => ArrayHelper::map($data_provider->getKeys(), function($key) {
									return $key;
								}, function($key) use ($data_provider){
									$item = ArrayHelper::getValue($data_provider->getModels(), $key);
									return [
										'y' => Yii::$app->formatter->asRound($item['pisga']),
										'name' => Yii::$app->formatter->asDate($item['timestamp']),
									];
								}),
							]; ?>

							<?php $consumptionChartSeries[] = [
								'name' => Yii::t('frontend.view', 'Geva'),
								'color' => 'rgba(20, 183, 124, 1)',
								'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'Kwh')],
								'stack' => 'current',
								'data' => ArrayHelper::map($data_provider->getKeys(), function($key) {
									return $key;
								}, function($key) use ($data_provider){
									$item = ArrayHelper::getValue($data_provider->getModels(), $key);
									return [
										'y' => Yii::$app->formatter->asRound($item['geva']),
										'name' => Yii::$app->formatter->asDate($item['timestamp']),
									];
								}),
							]; ?>

							<?php $consumptionChartSeries[] = [
								'name' => Yii::t('frontend.view', 'Shefel'),
								'color' => 'rgba(20, 156, 211, 1)',
								'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'Kwh')],
								'stack' => 'current',
								'data' => ArrayHelper::map($data_provider->getKeys(), function($key) {
									return $key;
								}, function($key) use ($data_provider){
									$item = ArrayHelper::getValue($data_provider->getModels(), $key);
									return [
										'y' => Yii::$app->formatter->asRound($item['shefel']),
										'name' => Yii::$app->formatter->asDate($item['timestamp']),
									];
								}),
							]; ?>
							<?php echo Chart::widget([
								'clientOptions' => [
									'chart' => [
										'type' => 'column',
									],
									'plotOptions' => [
										'column' => [
											'stacking' => 'normal',
										],
									],
									'tooltip' => [
										'headerFormat' => '<span style="font-size: 13px">{point.key}</span><br/>',
									],
									'xAxis' => [
										'categories' => ArrayHelper::map($data_provider->getKeys(), function($key) {
											return $key;
										}, function($key) use ($data_provider){
											$item = ArrayHelper::getValue($data_provider->getModels(), $key);
											return $item['date'];
										}),
									],
									'yAxis' => [
										'title' => ['text' => Yii::t('frontend.view', 'Kwh')],
									],
									'series' => $consumptionChartSeries,
								],
							]); ?>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<?php $maxDemandChartSeries = []; ?>

							<?php if ($compared_data_provider->totalCount): ?>
								<?php $maxDemandChartSeries[] = [
									'name' => Yii::t('frontend.view', 'Max demand (old)'),
									'color' => 'rgba(255, 117, 24, 0.5)',
									'stack' => 'old',
									'data' => ArrayHelper::map($compared_data_provider->getKeys(), function($key) {
										return $key;
									}, function($key) use ($compared_data_provider){
										$item = ArrayHelper::getValue($compared_data_provider->getModels(), $key);
										return [
											'y' => Yii::$app->formatter->asRound($item['max_demand']),
											'name' => Yii::$app->formatter->asDate($item['timestamp']),
										];
									}),
								]; ?>
							<?php endif; ?>

							<?php $maxDemandChartSeries[] = [
								'name' => Yii::t('frontend.view', 'Max demand'),
								'color' => 'rgba(255, 117, 24, 1)',
								'stack' => 'current',
								'data' => ArrayHelper::map($data_provider->getKeys(), function($key) {
									return $key;
								}, function($key) use ($data_provider){
									$item = ArrayHelper::getValue($data_provider->getModels(), $key);
									return [
										'y' => Yii::$app->formatter->asRound($item['max_demand']),
										'name' => Yii::$app->formatter->asDate($item['timestamp']),
									];
								}),
							]; ?>

							<?php echo Chart::widget([
								'clientOptions' => [
									'chart' => [
										'type' => 'column',
									],
									'plotOptions' => [
										'column' => [
											'stacking' => 'normal',
										],
									],
									'tooltip' => [
										'headerFormat' => '<span style="font-size: 13px">{point.key}</span><br/>',
									],
									'xAxis' => [
										'categories' => ArrayHelper::map($data_provider->getKeys(), function($key) {
											return $key;
										}, function($key) use ($data_provider){
											$item = ArrayHelper::getValue($data_provider->getModels(), $key);
											return $item['date'];
										}),
									],
									'series' => $maxDemandChartSeries,
								],
							]); ?>
						</div>
						<div class="col-md-6">
							<?php $kvarChartSeries = []; ?>

							<?php if ($compared_data_provider->totalCount): ?>
								<?php $kvarChartSeries[] = [
									'name' => Yii::t('frontend.view', 'Kvar (old)'),
									'color' => 'rgba(85, 57, 130, 0.5)',
									'stack' => 'old',
									'data' => ArrayHelper::map($compared_data_provider->getKeys(), function($key) {
										return $key;
									}, function($key) use ($compared_data_provider){
										$item = ArrayHelper::getValue($compared_data_provider->getModels(), $key);
										return [
											'y' => Yii::$app->formatter->asRound($item['kvar']),
											'name' => Yii::$app->formatter->asDate($item['timestamp']),
										];
									}),
								]; ?>
							<?php endif; ?>

							<?php $kvarChartSeries[] = [
								'name' => Yii::t('frontend.view', 'Kvar'),
								'color' => 'rgba(85, 57, 130, 1)',
								'stack' => 'current',
								'data' => ArrayHelper::map($data_provider->getKeys(), function($key) {
									return $key;
								}, function($key) use ($data_provider){
									$item = ArrayHelper::getValue($data_provider->getModels(), $key);
									return [
										'y' => Yii::$app->formatter->asRound($item['kvar']),
										'name' => Yii::$app->formatter->asDate($item['timestamp']),
									];
								}),
							]; ?>

							<?php echo Chart::widget([
								'clientOptions' => [
									'chart' => [
										'type' => 'column',
									],
									'plotOptions' => [
										'column' => [
											'stacking' => 'normal',
										],
									],
									'tooltip' => [
										'headerFormat' => '<span style="font-size: 13px">{point.key}</span><br/>',
									],
									'xAxis' => [
										'categories' => ArrayHelper::map($data_provider->getKeys(), function($key) {
											return $key;
										}, function($key) use ($data_provider){
											$item = ArrayHelper::getValue($data_provider->getModels(), $key);
											return $item['date'];
										}),
									],
									'series' => $kvarChartSeries,
								],
							]); ?>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-md-12">
							<?php echo Html::a(Yii::t('frontend.view', 'Export to excel'), [
								'export-excel',
								'from_date' => $form->from_date,
								'to_date' => $form->to_date,
								'drilldown' => $form->drilldown,
							], ['class' => 'btn btn-info pull-right', 'target' => '_blank']); ?>
						</div>
					</div>
					<?php echo GridView::widget([
						'dataProvider' => $data_provider,
						'layout' => "{items}",
						'columns' => [
							[
								'attribute' => 'timestamp',
								'label' => Yii::t('frontend.view', 'Date'),
								'value' => function($value) {
									return $value['date'];
								},
							],
							[
								'attribute' => 'pisga',
								'format' => 'numberFormat',
								'label' => Yii::t('frontend.view', 'Pisga Kwh'),
							],
							[
								'attribute' => 'geva',
								'format' => 'numberFormat',
								'label' => Yii::t('frontend.view', 'Geva Kwh'),
							],
							[
								'attribute' => 'shefel',
								'format' => 'numberFormat',
								'label' => Yii::t('frontend.view', 'Shefel Kwh'),
							],
							[
								'attribute' => 'max_demand',
								'format' => 'numberFormat',
								'label' => Yii::t('frontend.view', 'Max demand'),
							],
							[
								'attribute' => 'kvar',
								'format' => 'numberFormat',
								'label' => Yii::t('frontend.view', 'Kvar/h'),
							],
						],
					]); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
