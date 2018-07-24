<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Nav;
use yii\widgets\Pjax;
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
<body>
	<?php $this->beginBody(); ?>

	<header id="header">
		<div class="container clearfix">
			<?php echo Html::a(Html::img('@web/theme/images/logo.png', ['class' => 'logo-img', 'width' => 423, 'height' => 69]), Yii::$app->homeUrl, [
				'class' => 'logo',
			]); ?>
			<span class="decorated-text has-icon">
				<span class="text"><?php echo Yii::t('frontend.view', 'Electricity'); ?></span>
				<i class="icon icon-electricity"></i>
			</span>
		</div>
	</header>

	<?php Pjax::begin([
		'id' => 'pjax-dashboard',
		'timeout' => false,
		'linkSelector' => '#main-nav li a, .table th a',
	]); ?>
		<?php echo $content; ?>
	<?php Pjax::end(); ?>
	<?php $this->registerJs('
		var areaChartInterval;

		function initAreaChartInterval() {
			areaChartInterval = setInterval(function(){
				jQuery.getJSON("' .Url::to(['/dashboard/metmon']). '", function(data) {
					// Metmon area
					var IvArea = jQuery("#metmon-area-iv").highcharts();
					var KWArea = jQuery("#metmon-area-kw").highcharts();

					if (IvArea) {
						if (data["Iv"] && data["Iv"].date) {
							var IvAreaX = Date.UTC(data["Iv"].date["y"], data["Iv"].date["m"] - 1, data["Iv"].date["d"], data["Iv"].date["h"], data["Iv"].date["i"], data["Iv"].date["s"]);
							var IvAreaY = data["Iv"].value;
							IvArea.series[0].addPoint([IvAreaX, IvAreaY]);
						}
						if (data["KW"] && data["KW"].date) {
							var KWAreaX = Date.UTC(data["KW"].date["y"], data["KW"].date["m"] - 1, data["KW"].date["d"], data["KW"].date["h"], data["KW"].date["i"], data["KW"].date["s"]);
							var KWAreaY = data["KW"].value;
							KWArea.series[0].addPoint([KWAreaX, KWAreaY]);
						}
					}

					// Metmon gauges
					jQuery(".metmon-gauge").each(function() {
						var gauge = jQuery(this).highcharts();
						var name = jQuery(this).data("name");

						if (gauge && name && data[name]) {
							gauge.series[0].points[0].update(data[name].value);
						}
					});

					// Metmon values
					jQuery(".metmon-value").each(function() {
						var name = jQuery(this).data("name");

						if (name && data[name]) {
							jQuery(this).text(data[name].value);
						}
					});
				});
			}, 5000);
		}
		
		if (jQuery("#realtime-enabled").length) {
			initAreaChartInterval();
		}
	'); ?>
	<?php $this->registerJs('
		jQuery(document).on("pjax:send", function() {
			clearInterval(areaChartInterval);
			jQuery("body").append("<div id=\"report-overlay\"></div>");
			jQuery("body").append("<div id=\"report-spinner-holder\">' .Yii::t('frontend.view', 'Loading'). '<div id=\"report-spinner\"><div class=\"rect rect1\"></div><div class=\"rect rect2\"></div><div class=\"rect rect3\"></div><div class=\"rect rect4\"></div><div class=\"rect rect5\"></div></div></div>");
		});
		jQuery(document).on("pjax:complete", function() {

			if (jQuery("#realtime-enabled").length) {
				initAreaChartInterval();
			}

			jQuery("#report-overlay").remove();
			jQuery("#report-spinner-holder").remove();
		});
	'); ?>

	<footer id="footer">
		<div class="top-container clearfix">
			<span class="decorated-text">
				<span class="text"><?php echo Yii::t('frontend.view', 'Customer support'); ?>:</span>
			</span>
		</div>
		<div class="bottom-container">
			<?php echo Html::a(Html::img('@web/theme/images/logo-small.png', ['class' => 'logo-img', 'width' => 222, 'height' => 36]), Yii::$app->homeUrl, [
				'class' => 'logo',
			]); ?>
			<div class="contact-info">
				<i class="icon icon-envelope"></i>
				<div class="text-container">
					<p><?php echo Yii::t('frontend.view', 'Sderot HaDkalim, Industrial park, Kadima P.O.B 2550. Kadima 60920'); ?></p>
					<p>
						<?php echo Yii::t('frontend.view', 'Phone: 09-8911951. Fax: 09-8914555. Email: {link}', [
							'link' => Html::a(Yii::t('frontend.view', 'info@qlc.co.il'), 'mailto:info@qlc.co.il'),
						]); ?>
					</p>
				</div>
			</div>
		</div>
	</footer>

	<?php $this->endBody(); ?>
	<?php echo Alert::widget(); ?>
	<?php if (LanguageSelector::getAliasLanguageDirection() == LanguageSelector::DIRECTION_RTL): ?>
		<?php $this->registerCssFile('@web/css/bootstrap-rtl.css'); ?>
	<?php endif; ?>
</body>
</html>
<?php $this->endPage(); ?>
