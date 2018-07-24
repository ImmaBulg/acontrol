<?php
namespace backend\assets;
use yii\web\AssetBundle;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 17.03.2017
 * Time: 13:34
 */

class SupportBowerAsset extends AssetBundle {
    public $sourcePath = '@bower';
    public $css = [
        'fontawesome/css/font-awesome.min.css',
        'animate.css/animate.min.css',
    ];

    public $js = [
        'jquery-cookie/jquery.cookie.js',
        'jquery-easing-original/jquery.easing.min.js',
        'jquery-nicescroll/jquery.nicescroll.min.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

}