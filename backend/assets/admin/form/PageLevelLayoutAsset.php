<?php

namespace backend\assets\admin\form;

use yii\web\AssetBundle;

/**
 * @author Djava UI <support@djavaui.com>
 */
class PageLevelLayoutAsset extends AssetBundle {

    public $sourcePath = '@bower';
    public $css = [
        'fontawesome/css/font-awesome.min.css',
        'animate.css/animate.min.css',
//        'bootstrap-fileupload/css/bootstrap-fileupload.min.css',
        'chosen_v1.2.0/chosen.min.css'
    ];
    public $js = [
//        'bootstrap-fileupload/js/bootstrap-fileupload.min.js',
//        'chosen_v1.2.0/chosen.jquery.min.js'
    ];

}
