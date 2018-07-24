<?php

namespace backend\assets\admin\form;

use yii\web\AssetBundle;

/**
 * @author Djava UI <support@djavaui.com>
 */
class PageLevelAdvancedAsset extends AssetBundle {

    public $sourcePath = '@bower';
    public $css = [
        'fontawesome/css/font-awesome.min.css',
        'animate.css/animate.min.css',
        'dropzone/downloads/css/dropzone.css',
        'bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css',
        'bootstrap-datepicker-vitalets/css/datepicker.css'
    ];
    public $js = [
        'dropzone/downloads/dropzone.min.js',
        'bootstrap-switch/dist/js/bootstrap-switch.min.js',
        'jquery.inputmask/dist/jquery.inputmask.bundle.min.js',
        'bootstrap-datepicker-vitalets/js/bootstrap-datepicker.js'
    ];

}
