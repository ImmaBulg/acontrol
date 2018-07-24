<?php
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use common\helpers\Html;
use common\widgets\Alert;
use common\components\i18n\LanguageSelector;
use frontend\assets\AppAsset;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::$app->language; ?>" dir="<?php echo LanguageSelector::getAliasLanguageDirection(); ?>">
<head>
	<meta charset="<?php echo Yii::$app->charset; ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo Url::to('@web/favicon.ico'); ?>">
	<?php echo Html::csrfMetaTags(); ?>
	<title><?php $this->title(); ?></title>
	<?php $this->head(); ?>
</head>
<body id="layout-auth">
	<?php $this->beginBody(); ?>
	
	<div class="wrap">
		<div id="main" class="container">
			<div class="text-vertical full-height">
				<div class="col-md-offset-4 col-md-4">
					<?php echo Alert::widget(); ?>
					<?php echo $content; ?>
				</div>
			</div>
		</div>
	</div>

	<?php $this->endBody(); ?>
	<?php if (LanguageSelector::getAliasLanguageDirection() == LanguageSelector::DIRECTION_RTL): ?>
		<?php $this->registerCssFile('@web/css/bootstrap-rtl.css'); ?>
	<?php endif; ?>
</body>
</html>
<?php $this->endPage(); ?>
