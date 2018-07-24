<?php

namespace backend\assets\admin\form;

use yii\web\AssetBundle;

/**
 * @author Djava UI <support@djavaui.com>
 */
class PageLevelValidationAsset extends AssetBundle {

    public $sourcePath = '@bower';
    public $css = [
        'fontawesome/css/font-awesome.min.css',
        'animate.css/animate.min.css'
    ];
    public $js = [
        'chosen_v1.2.0/chosen.jquery.min.js',
        'jquery-mockjax/jquery.mockjax.js',
        'jquery-validation/dist/jquery.validate.min.js'
    ];

}
