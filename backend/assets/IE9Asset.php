<?php

namespace backend\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author Djava UI <support@djavaui.com>
 */
class IE9Asset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@asset';
    public $jsOptions = ['condition' => 'lte IE9','position' => View::POS_HEAD];
    public $css = [
    ];
    public $js = [ 'html5shiv/dist/html5shiv.min.js',
        'respond-minmax/src/respond.js',
    ];

}
