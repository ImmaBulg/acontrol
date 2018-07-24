<?php
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\models\Meter;
use common\models\Tenant;
use common\widgets\Select2;

$this->title = Yii::t('backend.view', 'Meter - {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Meters'),
	'url' => ['/meter/list'],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_meter_menu', ['model' => $model]); ?>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-meter-import-data',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'meter_id')->widget(Select2::classname(), [
						'data' => $form->getListMeters(),
						'options' => ['prompt' => Yii::t('backend.views', 'Select an option')],
					]); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Import data'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>