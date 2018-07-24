<?php

namespace app\assets\admin\form;

use yii\web\AssetBundle;

/**
 * @author Djava UI <support@djavaui.com>
 */
class PageLevelWysiwygAsset extends AssetBundle {

    public $sourcePath = '@bower';
    public $css = [
        'fontawesome/css/font-awesome.min.css',
        'animate.css/animate.min.css',
        'bootstrap-wysihtml5/src/bootstrap-wysihtml5.css',
        'summernote/dist/summernote.css'
    ];
    public $js = [
        'bootstrap-wysihtml5/lib/js/wysihtml5-0.3.0.min.js',
        'bootstrap-wysihtml5/src/bootstrap-wysihtml5.js',
        'summernote/dist/summernote.min.js'
    ];

}
