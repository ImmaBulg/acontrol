<?php
use common\helpers\Html;
use common\models\ElectricityMeterRawData;
?>
<div class="<?php echo $model['class']; ?>">
	<div class="row">
		<div class="col-lg-2 list-view-column main-list-view-column">
			<div class="vertical-align-table">
				<div class="vertical-align-cell">
					<?php echo Yii::$app->formatter->asDatetime($model['date']); ?>
                    <?php echo $form_active->field($form, 'consumption[' .$model['date']. '][timestamp]')->hiddenInput(['value'=> $model['timestamp']])->label(false);; ?>
				</div>
			</div>
		</div>
		<div class="col-lg-3 main-list-view-sub-column">
			<div class="row">
				<div class="col-lg-4">
					<div class="row">
						<div class="col-lg-6">
							<?php echo $form_active->field($form, 'consumption[' .$model['date']. '][pisga]', [
								'options' => ['class' => 'list-view-column'],
							])->textInput(['allow_only' => Html::TYPE_NUMBER, 'class' => 'form-control ' .$model['input_class']])
							->label(false)->error(false); ?>
						</div>
						<?php if (isset($form->readings[$model['date']])): ?>
							<div class="col-lg-6 list-view-column">
								<div class="vertical-align-table">
									<div class="vertical-align-cell text-muted">
										<?php echo Yii::$app->formatter->asRound($form->readings[$model['date']]['pisga']); ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="col-lg-2">
					<?php echo $form_active->field($form, 'consumption[' .$model['date']. '][max_pisga]', [
						'options' => ['class' => 'list-view-column'],
					])->textInput(['allow_only' => Html::TYPE_NUMBER])
					->label(false)->error(false); ?>
				</div>
				<div class="col-lg-2">
					<?php echo $form_active->field($form, 'consumption[' .$model['date']. '][export_pisga]', [
						'options' => ['class' => 'list-view-column'],
					])->textInput(['allow_only' => Html::TYPE_NUMBER, 'class' => 'form-control ' .$model['input_class']])
					->label(false)->error(false); ?>
				</div>
                <div class="col-lg-2">
                    <?php echo $form_active->field($form, 'consumption[' .$model['date']. '][kvar_pisga]', [
                        'options' => ['class' => 'list-view-column'],
                    ])->textInput(['allow_only' => Html::TYPE_NUMBER, 'class' => 'form-control ' .$model['input_class']])
                                           ->label(false)->error(false); ?>
                </div>

			</div>
		</div>
		<div class="col-lg-3 main-list-view-sub-column">
			<div class="row">
				<div class="col-lg-4">
					<div class="row">
						<div class="col-lg-6">
							<?php echo $form_active->field($form, 'consumption[' .$model['date']. '][geva]', [
								'options' => ['class' => 'list-view-column'],
							])->textInput(['allow_only' => Html::TYPE_NUMBER, 'class' => 'form-control ' .$model['input_class']])
							->label(false)->error(false); ?>	
						</div>
						<?php if (isset($form->readings[$model['date']])): ?>
							<div class="col-lg-6 list-view-column">
								<div class="vertical-align-table">
									<div class="vertical-align-cell text-muted">
										<?php echo Yii::$app->formatter->asRound($form->readings[$model['date']]['geva']); ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="col-lg-2">
					<?php echo $form_active->field($form, 'consumption[' .$model['date']. '][max_geva]', [
						'options' => ['class' => 'list-view-column'],
					])->textInput(['allow_only' => Html::TYPE_NUMBER])
					->label(false)->error(false); ?>
				</div>
				<div class="col-lg-2">
					<?php echo $form_active->field($form, 'consumption[' .$model['date']. '][export_geva]', [
						'options' => ['class' => 'list-view-column'],
					])->textInput(['allow_only' => Html::TYPE_NUMBER, 'class' => 'form-control ' .$model['input_class']])
					->label(false)->error(false); ?>
				</div>
                <div class="col-lg-2">
                    <?php echo $form_active->field($form, 'consumption[' .$model['date']. '][kvar_geva]', [
                        'options' => ['class' => 'list-view-column'],
                    ])->textInput(['allow_only' => Html::TYPE_NUMBER, 'class' => 'form-control ' .$model['input_class']])
                                           ->label(false)->error(false); ?>
                </div>

			</div>
		</div>
		<div class="col-lg-3 main-list-view-sub-column">
			<div class="row">
				<div class="col-lg-4">
					<div class="row">
						<div class="col-lg-6">
							<?php echo $form_active->field($form, 'consumption[' .$model['date']. '][shefel]', [
								'options' => ['class' => 'list-view-column'],
							])->textInput(['allow_only' => Html::TYPE_NUMBER, 'class' => 'form-control ' .$model['input_class']])
							->label(false)->error(false); ?>	
						</div>
						<?php if (isset($form->readings[$model['date']])): ?>
							<div class="col-lg-6 list-view-column">
								<div class="vertical-align-table">
									<div class="vertical-align-cell text-muted">
										<?php echo Yii::$app->formatter->asRound($form->readings[$model['date']]['shefel']); ?>
									</div>
								</div>
							</div>
						<?php endif; ?>	
					</div>
				</div>
				<div class="col-lg-2">
					<?php echo $form_active->field($form, 'consumption[' .$model['date']. '][max_shefel]', [
						'options' => ['class' => 'list-view-column'],
					])->textInput(['allow_only' => Html::TYPE_NUMBER])
					->label(false)->error(false); ?>
				</div>
				<div class="col-lg-2">
					<?php echo $form_active->field($form, 'consumption[' .$model['date']. '][export_shefel]', [
						'options' => ['class' => 'list-view-column'],
					])->textInput(['allow_only' => Html::TYPE_NUMBER, 'class' => 'form-control ' .$model['input_class']])
					->label(false)->error(false); ?>
				</div>
                <div class="col-lg-2">
                    <?php echo $form_active->field($form, 'consumption[' .$model['date']. '][kvar_shefel]', [
                        'options' => ['class' => 'list-view-column'],
                    ])->textInput(['allow_only' => Html::TYPE_NUMBER, 'class' => 'form-control ' .$model['input_class']])
                                           ->label(false)->error(false); ?>
                </div>

			</div>
		</div>
		<?php if (isset($model['id'])): ?>
			<div class="col-lg-1 list-view-column">
				<div class="vertical-align-table">
					<div class="vertical-align-cell">
						<?php echo Html::a(Yii::t('backend.view', 'Delete'), ['/meter-raw-data/delete-row', 'id' => $model['id']], [
							'class' => 'btn btn-default btn-sm',
							'data' => [
								'toggle' => 'confirm',
								'confirm-post' => true,
								'confirm-text' => Yii::t('backend.view', 'Are you sure you want to delete these data?'),
								'confirm-button' => Yii::t('backend.view', 'Delete'),
							],
						]); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>