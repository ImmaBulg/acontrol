<?php

use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\ActiveForm;
use common\widgets\chart\Chart;
use frontend\widgets\chart\GaugeChart;

$this->title = Yii::t('frontend.view', 'Real time');
?>
<?php echo Html::beginTag('div', ['class' => 'wrap', 'id' => ($realtime != null) ? 'realtime-enabled' : 'realtime-disabled']); ?>
	<div id="realtime-page" class="wrap">
		<?php echo $this->render('_header'); ?>
		<div id="main">
			<div class="container">
                <div>URL - <?php echo $metmon_url; ?></div>
                <div class="col-lg-12">
					<?php echo $this->render('_switch', [
						'action' => ['index'],
						'user' => $user,
						'form' => $form_switch,
						'show_clients' => true,
						'show_sites' => true,
						'show_tenants' => true,
						'show_meters' => true,
						'show_channels' => true,
					]); ?>
					<?php //if ($metmon_url): ?>
						<!--div class="alert alert-info">
							<?php //echo Html::a($metmon_url, $metmon_url, ['target' => '_blank', 'class' => 'alert-link']); ?>
						</div-->
					<?php //endif; ?>
					<h1 class="page-header"><?php echo Yii::t('frontend.view', 'Real time'); ?></h1>
					<div id="metmon-gauges">
						<div class="gauges-item row">
							<div class="col-md-1">
								<strong class="gauges-title"><?php echo Yii::t('frontend.view', 'Phase'); ?></strong>
							</div>
							<div class="col-md-3">
								<strong class="gauges-title gauges-title-md"><?php echo Yii::t('frontend.view', 'Current (A)'); ?></strong>
							</div>
							<div class="col-md-3">
								<strong class="gauges-title gauges-title-md"><?php echo Yii::t('frontend.view', 'Voltage (V)'); ?></strong>
							</div>
							<div class="col-md-3">
								<strong class="gauges-title gauges-title-md"><?php echo Yii::t('frontend.view', 'Power factor (Pf)'); ?></strong>
							</div>
							<div class="col-md-2">
								<strong class="gauges-title gauges-title-md"><?php echo Yii::t('frontend.view', 'Power (Pkw)'); ?></strong>
							</div>
						</div>
						<div class="gauges-item row">
							<div class="col-md-1 gauges-cell">
								<strong class="gauges-title gauges-title-md"><?php echo Yii::t('frontend.view', 'R'); ?></strong>
							</div>
							<div class="col-md-3">
								<?php echo GaugeChart::widget([
									'options' => ['class' => 'metmon-gauge', 'data' => ['name' => 'Ia']],
									'clientOptions' => [
										'yAxis' => [
											'min' => 0,
											'max' => ArrayHelper::getValue($metmon, 'IvLimit.value', 0),
											'stops' => [
												[0.1, '#14b77c'],
												[0.5, '#14b77c'],
												[0.9, '#14b77c'],
											],
										],
										'series' => [
											[
												'data' => [ArrayHelper::getValue($metmon, 'Ia.value', 0)],
												'dataLabels' => [
													'format' => new JsExpression("
														'<div style=\"text-align:center\"><span style=\"font-size:25px;color:' +
														((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '\">{y}</span><br/>' +
														'<span style=\"font-size:12px;color:silver\">" .Yii::t('frontend.view', 'A'). "</span></div>'
													"),
												],
												'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'A')],
											]
										],
									],
								]); ?>
							</div>
							<div class="col-md-3">
								<?php echo GaugeChart::widget([
									'options' => ['class' => 'metmon-gauge', 'data' => ['name' => 'Va']],
									'clientOptions' => [
										'yAxis' => [
											'min' => 0,
											'max' => 1000,
											'stops' => [
												[0.1, '#ff7518'],
												[0.5, '#ff7518'],
												[0.9, '#ff7518'],
											],
										],
										'series' => [
											[
												'data' => [ArrayHelper::getValue($metmon, 'Va.value', 0)],
												'dataLabels' => [
													'format' => new JsExpression("
														'<div style=\"text-align:center\"><span style=\"font-size:25px;color:' +
														((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '\">{y}</span><br/>' +
														'<span style=\"font-size:12px;color:silver\">" .Yii::t('frontend.view', 'V'). "</span></div>'
													"),
												],
												'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'V')],
											]
										],
									],
								]); ?>
							</div>
							<div class="col-md-3">
								<?php echo GaugeChart::widget([
									'options' => ['class' => 'metmon-gauge', 'data' => ['name' => 'PFa']],
									'clientOptions' => [
										'yAxis' => [
											'min' => 0,
											'max' => 1,
											'stops' => [
												[0.1, '#d81647'],
												[0.5, '#d81647'],
												[0.9, '#d81647'],
											],
										],
										'series' => [
											[
												'data' => [ArrayHelper::getValue($metmon, 'PFa.value', 0)],
												'dataLabels' => [
													'format' => new JsExpression("
														'<div style=\"text-align:center\"><span style=\"font-size:25px;color:' +
														((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '\">{y}</span><br/>' +
														'</div>'
													"),
												],
											]
										],
									],
								]); ?>
							</div>
							<div class="col-md-2 gauges-cell">
								<strong class="gauges-title metmon-value" data-name="KWa"><?php echo ArrayHelper::getValue($metmon, 'KWa.value', 0); ?></strong>
							</div>
						</div>
						<div class="gauges-item row">
							<div class="col-md-1 gauges-cell">
								<strong class="gauges-title gauges-title-md"><?php echo Yii::t('frontend.view', 'S'); ?></strong>
							</div>
							<div class="col-md-3">
								<?php echo GaugeChart::widget([
									'options' => ['class' => 'metmon-gauge', 'data' => ['name' => 'Ib']],
									'clientOptions' => [
										'yAxis' => [
											'min' => 0,
											'max' => ArrayHelper::getValue($metmon, 'IvLimit.value', 0),
											'stops' => [
												[0.1, '#14b77c'],
												[0.5, '#14b77c'],
												[0.9, '#14b77c'],
											],
										],
										'series' => [
											[
												'data' => [ArrayHelper::getValue($metmon, 'Ib.value', 0)],
												'dataLabels' => [
													'format' => new JsExpression("
														'<div style=\"text-align:center\"><span style=\"font-size:25px;color:' +
														((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '\">{y}</span><br/>' +
														'<span style=\"font-size:12px;color:silver\">" .Yii::t('frontend.view', 'A'). "</span></div>'
													"),
												],
												'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'A')],
											]
										],
									],
								]); ?>
							</div>
							<div class="col-md-3">
								<?php echo GaugeChart::widget([
									'options' => ['class' => 'metmon-gauge', 'data' => ['name' => 'Vb']],
									'clientOptions' => [
										'yAxis' => [
											'min' => 0,
											'max' => 1000,
											'stops' => [
												[0.1, '#ff7518'],
												[0.5, '#ff7518'],
												[0.9, '#ff7518'],
											],
										],
										'series' => [
											[
												'data' => [ArrayHelper::getValue($metmon, 'Vb.value', 0)],
												'dataLabels' => [
													'format' => new JsExpression("
														'<div style=\"text-align:center\"><span style=\"font-size:25px;color:' +
														((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '\">{y}</span><br/>' +
														'<span style=\"font-size:12px;color:silver\">" .Yii::t('frontend.view', 'V'). "</span></div>'
													"),
												],
												'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'V')],
											]
										],
									],
								]); ?>
							</div>
							<div class="col-md-3">
								<?php echo GaugeChart::widget([
									'options' => ['class' => 'metmon-gauge', 'data' => ['name' => 'PFb']],
									'clientOptions' => [
										'yAxis' => [
											'min' => 0,
											'max' => 1,
											'stops' => [
												[0.1, '#d81647'],
												[0.5, '#d81647'],
												[0.9, '#d81647'],
											],
										],
										'series' => [
											[
												'data' => [ArrayHelper::getValue($metmon, 'PFb.value', 0)],
												'dataLabels' => [
													'format' => new JsExpression("
														'<div style=\"text-align:center\"><span style=\"font-size:25px;color:' +
														((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '\">{y}</span><br/>' +
														'</div>'
													"),
												],
											]
										],
									],
								]); ?>
							</div>
							<div class="col-md-2 gauges-cell">
								<strong class="gauges-title metmon-value" data-name="KWb"><?php echo ArrayHelper::getValue($metmon, 'KWb.value', 0); ?></strong>
							</div>
						</div>
						<div class="gauges-item row">
							<div class="col-md-1 gauges-cell">
								<strong class="gauges-title gauges-title-md"><?php echo Yii::t('frontend.view', 'T'); ?></strong>
							</div>
							<div class="col-md-3">
								<?php echo GaugeChart::widget([
									'options' => ['class' => 'metmon-gauge', 'data' => ['name' => 'Ic']],
									'clientOptions' => [
										'yAxis' => [
											'min' => 0,
											'max' => ArrayHelper::getValue($metmon, 'IvLimit.value', 0),
											'stops' => [
												[0.1, '#14b77c'],
												[0.5, '#14b77c'],
												[0.9, '#14b77c'],
											],
										],
										'series' => [
											[
												'data' => [ArrayHelper::getValue($metmon, 'Ic.value', 0)],
												'dataLabels' => [
													'format' => new JsExpression("
														'<div style=\"text-align:center\"><span style=\"font-size:25px;color:' +
														((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '\">{y}</span><br/>' +
														'<span style=\"font-size:12px;color:silver\">" .Yii::t('frontend.view', 'A'). "</span></div>'
													"),
												],
												'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'A')],
											]
										],
									],
								]); ?>
							</div>
							<div class="col-md-3">
								<?php echo GaugeChart::widget([
									'options' => ['class' => 'metmon-gauge', 'data' => ['name' => 'Vc']],
									'clientOptions' => [
										'yAxis' => [
											'min' => 0,
											'max' => 1000,
											'stops' => [
												[0.1, '#ff7518'],
												[0.5, '#ff7518'],
												[0.9, '#ff7518'],
											],
										],
										'series' => [
											[
												'data' => [ArrayHelper::getValue($metmon, 'Vc.value', 0)],
												'dataLabels' => [
													'format' => new JsExpression("
														'<div style=\"text-align:center\"><span style=\"font-size:25px;color:' +
														((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '\">{y}</span><br/>' +
														'<span style=\"font-size:12px;color:silver\">" .Yii::t('frontend.view', 'V'). "</span></div>'
													"),
												],
												'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'V')],
											]
										],
									],
								]); ?>
							</div>
							<div class="col-md-3">
								<?php echo GaugeChart::widget([
									'options' => ['class' => 'metmon-gauge', 'data' => ['name' => 'PFc']],
									'clientOptions' => [
										'yAxis' => [
											'min' => 0,
											'max' => 1,
											'stops' => [
												[0.1, '#d81647'],
												[0.5, '#d81647'],
												[0.9, '#d81647'],
											],
										],
										'series' => [
											[
												'data' => [ArrayHelper::getValue($metmon, 'PFc.value', 0)],
												'dataLabels' => [
													'format' => new JsExpression("
														'<div style=\"text-align:center\"><span style=\"font-size:25px;color:' +
														((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '\">{y}</span><br/>' +
														'</div>'
													"),
												],
											]
										],
									],
								]); ?>
							</div>
							<div class="col-md-2 gauges-cell">
								<strong class="gauges-title metmon-value" data-name="KWc"><?php echo ArrayHelper::getValue($metmon, 'KWc.value', 0); ?></strong>
							</div>
						</div>
						<div class="gauges-item row">
							<div class="col-md-1"></div>
							<div class="col-md-3">
								<div class="gauges-title gauges-title-sm"><?php echo Yii::t('frontend.view', 'AVG'); ?></div>
								<strong class="gauges-title metmon-value" data-name="Iv"><?php echo ArrayHelper::getValue($metmon, 'Iv.value', 0); ?></strong>
							</div>
							<div class="col-md-3">
								<div class="gauges-title gauges-title-sm"><?php echo Yii::t('frontend.view', 'AVG'); ?></div>
								<strong class="gauges-title metmon-value" data-name="Vv"><?php echo ArrayHelper::getValue($metmon, 'Vv.value', 0); ?></strong>
							</div>
							<div class="col-md-3">
								<div class="gauges-title gauges-title-sm"><?php echo Yii::t('frontend.view', 'AVG'); ?></div>
								<strong class="gauges-title metmon-value" data-name="PF"><?php echo ArrayHelper::getValue($metmon, 'PF.value', 0); ?></strong>
							</div>
							<div class="col-md-2">
								<div class="gauges-title gauges-title-sm"><?php echo Yii::t('frontend.view', 'TOTAL'); ?></div>
								<strong class="gauges-title metmon-value" data-name="KW"><?php echo ArrayHelper::getValue($metmon, 'KW.value', 0); ?></strong>
							</div>
						</div>
					</div>
					<?php echo GridView::widget([
						'id' => 'metmon-table',
						'dataProvider' => $data_provider,
						'layout' => "{items}",
						'columns' => [
							[
								'attribute' => 'energy',
								'label' => Yii::t('frontend.view', 'Energy'),
							],
							[
								'attribute' => 'shefel',
								'format' => 'raw',
								'label' => Yii::t('frontend.view', 'Shefel'),
								'value' => function($value, $key, $index) {
									return Html::tag('span', $value['shefel'], [
										'class' => 'metmon-value',
										'data' => ['name' => (($value['type'] == 'TfTotImpKWh') ? 'Tf1TotImpKWh' : 'Tf1TotExpKWh')],
									]);
								},
							],
							[
								'attribute' => 'geva',
								'format' => 'raw',
								'label' => Yii::t('frontend.view', 'Geva'),
								'value' => function($value, $key, $index) {
									return Html::tag('span', $value['geva'], [
										'class' => 'metmon-value',
										'data' => ['name' => (($value['type'] == 'TfTotImpKWh') ? 'Tf2TotImpKWh' : 'Tf2TotExpKWh')],
									]);
								},
							],
							[
								'attribute' => 'pisga',
								'format' => 'raw',
								'label' => Yii::t('frontend.view', 'Pisga'),
								'value' => function($value, $key, $index) {
									return Html::tag('span', $value['pisga'], [
										'class' => 'metmon-value',
										'data' => ['name' => (($value['type'] == 'TfTotImpKWh') ? 'Tf3TotImpKWh' : 'Tf3TotExpKWh')],
									]);
								},
							],
							[
								'attribute' => 'total',
								'format' => 'raw',
								'label' => Yii::t('frontend.view', 'Total'),
								'value' => function($value, $key, $index) {
									return Html::tag('span', $value['total'], [
										'class' => 'metmon-value',
										'data' => ['name' => $value['type']],
									]);
								},
							],
						],
					]); ?>
					<?php $pointStartIa = ArrayHelper::getValue($realtime, 'Iv.0', []); ?>
					<?php echo Chart::widget([
						'id' => "metmon-area-iv",
						'clientOptions' => [
							'chart' => [
								'type' => 'area',
								'zoomType' => 'x',
							],
							'title' => ['text' => Yii::t('frontend.view', 'Real time consumption - Current (A)')],
							'xAxis' => [
								'type' => 'datetime',
							],
							'legend' => ['enabled' => false],
							'plotOptions' => [
								'area' => [
									'marker' => ['radius' => 2],
									'lineWidth' => 1,
									'threshold' => null,
									'states' => ['hover' => ['lineWidth' => 1]],
								],
							],
							'series' => [
								[
									'name' => Yii::t('frontend.view', 'Current (A)'),
									'color' => '#14b77c',
									'fillOpacity' => 0.2,
									'tooltip' => ['valueSuffix' => ' ' .Yii::t('frontend.view', 'A')],
									'pointStart' => new JsExpression('Date.UTC(' .implode(', ', [
										ArrayHelper::getValue($pointStartIa, 'date.y', 0),
										ArrayHelper::getValue($pointStartIa, 'date.m', 1) - 1,
										ArrayHelper::getValue($pointStartIa, 'date.d', 0),
										ArrayHelper::getValue($pointStartIa, 'date.h', 0),
										ArrayHelper::getValue($pointStartIa, 'date.i', 0),
										ArrayHelper::getValue($pointStartIa, 'date.s', 0),
									]). ')'),
									'data' => array_values(ArrayHelper::map(ArrayHelper::getValue($realtime, 'Iv', []), function($item) {
										return ArrayHelper::getValue($item, 'date.t');
									}, function($item) {
										return [new JsExpression('Date.UTC(' .implode(', ', [
											ArrayHelper::getValue($item, 'date.y', 0),
											ArrayHelper::getValue($item, 'date.m', 1) - 1,
											ArrayHelper::getValue($item, 'date.d', 0),
											ArrayHelper::getValue($item, 'date.h', 0),
											ArrayHelper::getValue($item, 'date.i', 0),
											ArrayHelper::getValue($item, 'date.s', 0),
										]). ')'), ArrayHelper::getValue($item, 'value', 0)];
									})),
								],
							],
						],
					]); ?>
					<br>
					<?php $pointStartKW = ArrayHelper::getValue($realtime, 'KW.0', []); ?>
					<?php echo Chart::widget([
						'id' => "metmon-area-kw",
						'clientOptions' => [
							'chart' => [
								'type' => 'area',
								'zoomType' => 'x',
							],
							'title' => ['text' => Yii::t('frontend.view', 'Real time consumption - Power (Pkw)')],
							'xAxis' => [
								'type' => 'datetime',
							],
							'legend' => ['enabled' => false],
							'plotOptions' => [
								'area' => [
									'marker' => ['radius' => 2],
									'lineWidth' => 1,
									'threshold' => null,
									'states' => ['hover' => ['lineWidth' => 1]],
								],
							],
							'series' => [
								[
									'name' => Yii::t('frontend.view', 'Power (Pkw)'),
									'color' => 'rgba(85, 57, 130, 1)',
									'fillOpacity' => 0.2,
									'pointStart' => new JsExpression('Date.UTC(' .implode(', ', [
										ArrayHelper::getValue($pointStartKW, 'date.y', 0),
										ArrayHelper::getValue($pointStartKW, 'date.m', 1) - 1,
										ArrayHelper::getValue($pointStartKW, 'date.d', 0),
										ArrayHelper::getValue($pointStartKW, 'date.h', 0),
										ArrayHelper::getValue($pointStartKW, 'date.i', 0),
										ArrayHelper::getValue($pointStartKW, 'date.s', 0),
									]). ')'),
									'data' => array_values(ArrayHelper::map(ArrayHelper::getValue($realtime, 'KW', []), function($item) {
										return ArrayHelper::getValue($item, 'date.t');
									}, function($item) {
										return [new JsExpression('Date.UTC(' .implode(', ', [
											ArrayHelper::getValue($item, 'date.y', 0),
											ArrayHelper::getValue($item, 'date.m', 1) - 1,
											ArrayHelper::getValue($item, 'date.d', 0),
											ArrayHelper::getValue($item, 'date.h', 0),
											ArrayHelper::getValue($item, 'date.i', 0),
											ArrayHelper::getValue($item, 'date.s', 0),
										]). ')'), ArrayHelper::getValue($item, 'value', 0)];
									})),
								],
							],
						],
					]); ?>
				</div>
			</div>
		</div>
	</div>
<?php echo Html::endTag('div'); ?>