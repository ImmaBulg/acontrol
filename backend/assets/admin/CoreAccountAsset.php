<?php

namespace app\assets\admin;

use yii\web\AssetBundle;

/**
 * @author Djava UI <support@djavaui.com>
 */
class CoreAccountAsset extends AssetBundle {

    public $sourcePath = '@bower';
    public $css = [
        'bootstrap/dist/css/bootstrap.min.css',
    ];
    public $js = [ 'jquery/dist/jquery.min.js',
        'jquery-cookie/jquery.cookie.js',
        'bootstrap/dist/js/bootstrap.min.js',
        'jpreloader/js/jpreloader.min.js',
        'jquery-easing-original/jquery.easing.min.js',
//        'ionsound/js/ion.sound.min.js',
        'retina.js/dist/retina.min.js'
    ];

}
