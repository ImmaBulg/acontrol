<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;

use common\helpers\Html;

use api\modules\swagger\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::$app->language ?>">
<head>
	<meta charset="<?php echo Yii::$app->charset ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo Html::encode($this->title); ?></title>
	<?php $this->registerLinkTag([
		'sizes' => '16x16',
		'href' => Url::to('@web/images/swagger/favicon-16x16.png'),
		'type' => 'image/png',
		'rel' => 'icon',
	]); ?>
	<?php $this->registerLinkTag([
		'sizes' => '32x32',
		'href' => Url::to('@web/images/swagger/favicon-32x32.png'),
		'type' => 'image/png',
		'rel' => 'icon',
	]); ?>
	<?php $this->head() ?>
</head>
<body class="swagger-section">
	<?php $this->beginBody(); ?>

		<div id="header">
			<div class="swagger-ui-wrap">
				<?php echo Html::a('swagger', 'http://swagger.io', [
					'id' => 'logo',
					'target' => '_blank',
				]); ?>
				<?php $form_active = ActiveForm::begin([
					'id' => 'api_selector',
				]); ?>
					<div class="input">
						<?php echo Html::textInput('baseUrl', '', ['id' => 'input_baseUrl']); ?>
					</div>
					<div class="input">
						<?php echo Html::a(Yii::t('api.app', 'Explore'), '#', ['id' => 'explore']); ?>
					</div>
				<?php ActiveForm::end(); ?>
			</div>
		</div>
		<div class="swagger-ui-wrap message-success" id="message-bar"></div>
		<?php echo $content ?>

	<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage() ?>
