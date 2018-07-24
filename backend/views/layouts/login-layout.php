<?php
\backend\assets\AppAsset::register($this); ?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
    <!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
    <!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

                                        <!-- START @HEAD -->
<head>
    <!-- START @META SECTION -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <title><?= $this->title ?></title>
    <!--/ END META SECTION -->



    <!-- START @FONT STYLES -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700" rel="stylesheet">
    <!--/ END FONT STYLES -->

    <!-- START @IE SUPPORT -->
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="/global/plugins/bower_components/html5shiv/dist/html5shiv.min.js"></script>
    <script src="/global/plugins/bower_components/respond-minmax/dest/respond.min.js"></script>
    <![endif]-->
    <!--/ END IE SUPPORT -->

    <?php $this->head();  ?>
</head>
                                        <!--/ END HEAD -->

<body class="page-sound yii2">
<?php $this->beginBody() ?>
<!--[if lt IE 9]>
<p class="upgrade-browser">Upps!! You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/" target="_blank">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<!-- START @SIGN WRAPPER -->
<?php echo $content ?>
<!--/ END SIGN WRAPPER -->


<?php $this->endBody() ?>

<!-- START GOOGLE ANALYTICS -->
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-93235406-1', 'auto');
    ga('send', 'pageview');

</script>
<!--/ END GOOGLE ANALYTICS -->
</body>
                                        <!-- END BODY -->

</html>
<?php $this->endPage() ;?>