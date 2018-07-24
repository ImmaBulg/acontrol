<?php

namespace backend\assets\admin\dashboard;

use yii\web\AssetBundle;

/**
 * @author Djava UI <support@djavaui.com>
 */
class PageLevelAsset extends AssetBundle {

    public $sourcePath = '@bower';
    public $css = [
        'dropzone/downloads/css/dropzone.css',
        'jquery.gritter/css/jquery.gritter.css'
    ];
    public $js = [
        'bootstrap-session-timeout/dist/bootstrap-session-timeout.min.js',

        'jquery.gritter/js/jquery.gritter.min.js',
        'skycons-html5/skycons.js'
    ];

    public $depends = [
       'backend\assets\FlotAsset',
       'backend\assets\DropZoneAsset',
    ];

}
