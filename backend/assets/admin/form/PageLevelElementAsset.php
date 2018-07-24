<?php

namespace backend\assets\admin\form;

use yii\web\AssetBundle;

/**
 * @author Djava UI <support@djavaui.com>
 */
class PageLevelElementAsset extends AssetBundle {

    public $sourcePath = '@bower';
    public $css = [
        'fontawesome/css/font-awesome.min.css',
        'animate.css/animate.min.css',
        'bootstrap-tagsinput/dist/bootstrap-tagsinput.css',
        'jasny-bootstrap-fileinput/css/jasny-bootstrap-fileinput.min.css',
        'chosen_v1.2.0/chosen.min.css'
    ];
    public $js = [
        'bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js',
        'jasny-bootstrap-fileinput/js/jasny-bootstrap.fileinput.min.js',
        'holderjs/holder.js',
        'bootstrap-maxlength/bootstrap-maxlength.min.js',
        'jquery-autosize/jquery.autosize.min.js',
        'chosen_v1.2.0/chosen.jquery.min.js'
    ];

}
