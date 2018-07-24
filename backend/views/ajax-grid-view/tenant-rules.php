<?php

use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\DataColumn;
use backend\models\searches\models\RuleSingleChannel;
use backend\models\searches\models\RuleGroupLoad;
use backend\models\searches\models\RuleFixedLoad;

?>
<h2><?php echo Yii::t('backend.view', 'Rules of tenant {name}', [
	'name' => $model->name,
]); ?></h2>
<ul class="nav nav-tabs" role="tablist">
	<li class="active">
		<a href="#tenant-single-channel-rules" aria-controls="tenant-single-channel-rules" role="tab" data-toggle="tab">
			<?php echo Yii::t('backend.view', 'Single channel association rules'); ?>
		</a>
	</li>
	<li>
		<a href="#tenant-group-load-rules" aria-controls="tenant-group-load-rules" role="tab" data-toggle="tab">
			<?php echo Yii::t('backend.view', 'Group load association rules'); ?>
		</a>
	</li>
	<li>
		<a href="#tenant-fixed-load-rules" aria-controls="tenant-fixed-load-rules" role="tab" data-toggle="tab">
			<?php echo Yii::t('backend.view', 'Fixed load association rules'); ?>
		</a>
	</li>
</ul>
<div class="tab-content">
	<div role="tabpanel" class="tab-pane active" id="tenant-single-channel-rules">
		<?php Pjax::begin([
			'enablePushState' => false,
			'options' => ['id' => 'tenant-single-channel-rules-pjax'],
		]); ?>
		<?php echo GridView::widget([
			'dataProvider' => $data_provider_single,
			'columns' => [
				'id',
				[
					'attribute' => 'total_bill_action',
					'value' => 'aliasTotalBillAction',
					'filter' => RuleSingleChannel::getListTotalBillActions(),
				],
				[
					'attribute' => 'meter_name',
					'format' => 'raw',
					'value' => function($model){
						if ($model->relationMeterChannel != null) {
							return $model->relationMeterChannel->relationMeter->name;
						}
					},
				],
				[
					'attribute' => 'channel_name',
					'format' => 'raw',
					'value' => function($model){
						if ($model->relationMeterChannel != null) {
							return $model->relationMeterChannel->getChannelName();
						}
					},
				],
				[
					'attribute' => 'usage_tenant_name',
					'format' => 'raw',
					'value' => function($model){
						if ($model->relationUsageTenant != null) {
							return $model->relationUsageTenant->name;
						}
					},
				],
				[
					'attribute' => 'use_type',
					'value' => 'aliasUseType',
					'filter' => RuleSingleChannel::getListUseTypes(),
				],
				[
					'attribute' => 'use_percent',
					'format' => 'raw',
					'filter' => RuleSingleChannel::getListUsePercents(),
					'value' => function($model){
						$name = $model->getAliasUsePercent();

						switch ($model->use_percent) {
							case RuleSingleChannel::USE_PERCENT_PARTIAL:
								$name .= " (" .Yii::$app->formatter->asPercentage($model->percent). ")";
								break;

							case RuleSingleChannel::USE_PERCENT_HOUR:
								$name .= " (" .Yii::$app->formatter->asTime($model->from_hours). " - " .Yii::$app->formatter->asTime($model->to_hours). ")";
								break;

							case RuleSingleChannel::USE_PERCENT_RELATIVE_TO_SQUARE_FOOTAGE:
							case RuleSingleChannel::USE_PERCENT_FULL:
							default:
								break;
						}
						
						return $name;
					},
				],
				[
					'attribute' => 'start_date',
					'format' => 'date',
					'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
				],
				[
					'attribute' => 'status',
					'value' => 'aliasStatus',
					'filter' => RuleSingleChannel::getListStatuses(),
				],
			],
		]); ?>
		<?php Pjax::end(); ?>
	</div>
	<div role="tabpanel" class="tab-pane" id="tenant-group-load-rules">
		<?php Pjax::begin([
			'enablePushState' => false,
			'options' => ['id' => 'tenant-group-load-rules-pjax'],
		]); ?>
		<?php echo GridView::widget([
			'dataProvider' => $data_provider_group,
			'columns' => [
				'id',
				'name',
				[
					'attribute' => 'total_bill_action',
					'value' => 'aliasTotalBillAction',
					'filter' => RuleGroupLoad::getListTotalBillActions(),
				],
				[
					'attribute' => 'meter_name',
					'format' => 'raw',
					'value' => function($model){
						if ($model->relationMeterChannel != null) {
							return $model->relationMeterChannel->relationMeter->name;
						}
					},
				],
				[
					'attribute' => 'channel_name',
					'format' => 'raw',
					'value' => function($model){
						if ($model->relationMeterChannel != null) {
							return $model->relationMeterChannel->getChannelName();
						}
					},
				],
				[
					'attribute' => 'group_name',
					'format' => 'raw',
					'value' => function($model){
						switch ($model->use_type) {
							case RuleGroupLoad::USE_TYPE_SINGLE_TENANT_GROUP_LOAD:
								return $model->relationTenantGroup->name;
								break;
							
							case RuleGroupLoad::USE_TYPE_SINGLE_METER_GROUP_LOAD:
								return $model->relationMeterChannelGroup->name;
								break;

							default:
								break;
						}
					},
				],
				[
					'attribute' => 'use_type',
					'value' => 'aliasUseType',
					'filter' => RuleGroupLoad::getListUseTypes(),
				],
				[
					'attribute' => 'use_percent',
					'value' => 'aliasUsePercent',
					'filter' => RuleGroupLoad::getListUsePercents(),
				],
				[
					'attribute' => 'status',
					'value' => 'aliasStatus',
					'filter' => RuleGroupLoad::getListStatuses(),
				],
			],
		]); ?>
		<?php Pjax::end(); ?>
	</div>
	<div role="tabpanel" class="tab-pane" id="tenant-fixed-load-rules">
		<?php Pjax::begin([
			'enablePushState' => false,
			'options' => ['id' => 'tenant-fixed-load-rules-pjax'],
		]); ?>
		<?php echo GridView::widget([
			'dataProvider' => $data_provider_fixed,
			'columns' => [
				'id',
				[
					'attribute' => 'use_type',
					'value' => 'aliasUseType',
					'filter' => RuleFixedLoad::getListUseTypes(),
				],
				[
					'attribute' => 'use_frequency',
					'value' => 'aliasUseFrequency',
					'filter' => RuleFixedLoad::getListUseFrequencies(),
				],
				'value:round',
				'pisga:round',
				'geva:round',
				'shefel:round',
				[
					'attribute' => 'status',
					'value' => 'aliasStatus',
					'filter' => RuleFixedLoad::getListStatuses(),
				],
			],
		]); ?>
		<?php Pjax::end(); ?>
	</div>
</div>