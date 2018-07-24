<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\Dropdown;
use common\widgets\ActiveForm;
use common\models\MeterChannel;
use backend\models\forms\FormMeterChannels;

$this->title = Yii::t('backend.view', 'Meter channels');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Meters'),
	'url' => ['/meter/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->name,
	'url' => ['/meter/edit', 'id' => $model->id],
];
$this->params['breadcrumbs'][] = Yii::t('backend.view', 'Meter channels');
?>
<?php echo $this->render('//meter/_meter_menu', ['model' => $model]); ?>

<?php $form_active = ActiveForm::begin([
	'id' => 'form-meter-channels-edit',
	'enableClientValidation' => false,
	'method' => 'GET',
	'action' => ['/meter-channel/list', 'id' => $model->id],
]); ?>
<fieldset>
	<?php if (Yii::$app->user->can('MeterManager')): ?>
		<?php echo $form_active->errorSummary($form_channels); ?>
		<div class="row">
			<div class="col-lg-3">
				<?php echo $form_active->field($form_channels, 'current_multiplier')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
			</div>
			<div class="col-lg-3">
				<?php echo $form_active->field($form_channels, 'voltage_multiplier')->textInput(['allow_only' => Html::TYPE_NUMBER]); ?>
			</div>
			<div class="col-lg-3">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-primary control-label-offset']); ?>
			</div>
			<div class="col-lg-3">
				<?php echo Html::a(Yii::t('backend.view', 'Delete all meter raw data'), ['/meter-raw-data/delete-all', 'meter_id' => $model->name], [
					'class' => 'btn btn-warning control-label-offset pull-right',
					'data' => [
						'toggle' => 'confirm',
						'confirm-post' => true,
						'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete all meter raw data?'),
						'confirm-button' => Yii::t('backend.view', 'Delete'),
					],
				]); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php echo GridView::widget([
		'dataProvider' => $data_provider,
		'filterModel' => $filter_model,
		'id' => 'table-meter-channels-list',
        'options' => [
            'class' => 'table table-striped table-primary',
        ],
		'columns' => [
			[
				'class' => 'common\widgets\CheckboxColumn',
				'name' => FormMeterChannels::METER_CHANNELS_FIELD_NAME,
				'visible' => (Yii::$app->user->can('MeterManager')),
			],
			'id',
			[
				'attribute' => 'channel',
				'format' => 'raw',
				'value' => 'channelName',
			],
			'current_multiplier:round',
			'voltage_multiplier:round',
			[
				'format' => 'raw',
				'value' => function ($model){				
					$btn = [];
					
					if (Yii::$app->user->can('MeterManager')) {
						$subchannel_items = [];

						foreach ($model->relationMeterSubchannels as $model_subchannel) {
							$items[] = [
								'label' => Yii::t('backend.view', 'Channel {channel}', ['channel' => $model_subchannel->channel]),
								'url' => ['/meter-raw-data/list', 'meter_id' => $model_subchannel->relationMeter->name, 'channel_id' => $model_subchannel->channel],
							];
						}

						$btn[] = '<div class="btn-group">'.
							Html::a(Yii::t('backend.view', 'Raw data'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-info btn-sm', 'data' => ['toggle' => 'dropdown']]).
							Dropdown::widget([
								'items' => $items,
							]).
						'</div>';
						$btn[] = '<div class="btn-group">'.
									Html::a(Yii::t('backend.view', 'Actions'). ' <span class="caret"></span>', '#', ['class' => 'dropdown-toggle btn btn-danger btn-sm', 'data' => ['toggle' => 'dropdown']]).
									Dropdown::widget([
										'items' => [
											[
												'label' => Yii::t('backend.view', 'Edit'),
												'url' => ArrayHelper::merge(Yii::$app->request->get(), ['/meter-channel/edit', 'id' => $model->id]),
											],
										],
									]).
								'</div>';
					}

					return '<div class="btn-toolbar pull-right">' .implode('', $btn). '</div>';
				}
			],
		],
	]); ?>
</fieldset>
<?php ActiveForm::end(); ?>