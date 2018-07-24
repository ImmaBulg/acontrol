<?php

use yii\helpers\Url;
use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\Select2;
use common\models\Tenant;
use common\models\Site;

$this->title = Yii::t('backend.view', 'Meter - {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationSite->relationUser->name,
	'url' => ['/client/view', 'id' => $model->relationSite->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Sites'),
	'url' => ['/client/sites', 'id' => $model->relationSite->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => $model->relationSite->name,
	'url' => ['/site/view', 'id' => $model->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Energy tree'),
	'url' => ['/site-meter/list', 'id' => $model->site_id],
];
$this->params['breadcrumbs'][] = $model->name;
?>
<?php echo $this->render('_site_meter_menu', ['model' => $model]); ?>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-site-meter-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($form, 'meter_id')->widget(Select2::classname(), [
						'data' => Site::getListMeters($model->site_id),
					]); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>