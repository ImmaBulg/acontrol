<?php
use common\components\i18n\LanguageSelector;
use dezmont765\yii2bundle\components\Alert;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->beginPage() ?>
<?php \backend\assets\AppAsset::register($this); ?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="<?php echo Yii::$app->language; ?>" dir="<?php echo LanguageSelector::getAliasLanguageDirection(); ?>> <!--<![endif]-->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?= Html::csrfMetaTags() ?>
    <meta name="keywords" content="">
    <title><?= $this->title ?></title>


    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700" rel="stylesheet">

    <?php $this->head(); ?>

</head>


<body class="page-session page-sound page-header-fixed page-sidebar-fixed page-footer-fixed">
<?php $this->beginBody() ?>
<!--[if lt IE 9]>
<p class="upgrade-browser">Upps!! You are using an <strong>outdated</strong> browser. Please <a
        href="http://browsehappy.com/" target="_blank">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<section id="wrapper">
    <?php echo $this->render("site/_header") ?>
    <div class="container-fluid" style="padding: 15px">
    <section id="page-content" style="padding: 60px">
        <?php echo Breadcrumbs::widget([
                                           'homeLink' => [
                                               'label' => Yii::t('backend.view', 'Dashboard'),
                                               'url' => Yii::$app->homeUrl,
                                           ],
                                           'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                                       ]); ?>
        <?= Alert::printAlert(); ?>
        <?= $content ?>
    </section>
    </div>


</section>

<div id="back-top" class="animated pulse circle">
    <i class="fa fa-angle-up"></i>
</div>


<?php $this->endBody() ?>
<?php if (LanguageSelector::getAliasLanguageDirection() == LanguageSelector::DIRECTION_RTL): ?>
    <?php $this->registerCssFile('@web/css/bootstrap-rtl.css'); ?>
<?php endif; ?>
<!-- START GOOGLE ANALYTICS -->
<script>
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-93235406-1', 'auto');
    ga('send', 'pageview');

</script>
<!--/ END GOOGLE ANALYTICS -->
</body>
</html>
<?php $this->endPage() ?>
