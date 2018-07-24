<?php

use common\helpers\Html;
use common\widgets\Select2;
use common\widgets\ActiveForm;
use common\models\SiteIpAddress;

$this->title = Yii::t('backend.view', 'IP - {name}', ['name' => $ip_address->ip_address]);
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Clients'),
	'url' => ['/client/list'],
];
$this->params['breadcrumbs'][] = [
	'label' => $ip_address->relationSite->relationUser->name,
	'url' => ['/client/view', 'id' => $ip_address->relationSite->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'Sites'),
	'url' => ['/client/sites', 'id' => $ip_address->relationSite->user_id],
];
$this->params['breadcrumbs'][] = [
	'label' => $ip_address->relationSite->name,
	'url' => ['/site/view', 'id' => $ip_address->site_id],
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('backend.view', 'IPs'),
	'url' => ['/site-ip-address/list', 'id' => $ip_address->site_id],
];
$this->params['breadcrumbs'][] = $ip_address->ip_address;
?>
<?php echo $this->render('_site_ip_address_menu', ['model' => $ip_address]); ?>
<div class="well">
	<?php $form_active = ActiveForm::begin([
		'id' => 'form-site-ip-address-create',
		'enableOneProcessSubmit' => true,
	]); ?>
		<fieldset>
			<div class="row">
				<div class="col-lg-6">
					<?php echo $form_active->field($ip_address, 'ip_address')->textInput(); ?>
					<?php echo $form_active->field($ip_address, 'status')->widget(Select2::classname(), [
						'data' => SiteIpAddress::getListStatuses(),
					]); ?>
					<?php echo $form_active->field($ip_address, 'is_main')->checkbox(); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo Html::submitInput(Yii::t('backend.view', 'Update'), ['class' => 'btn btn-success']); ?>
			</div>
		</fieldset>
	<?php ActiveForm::end(); ?>
</div>