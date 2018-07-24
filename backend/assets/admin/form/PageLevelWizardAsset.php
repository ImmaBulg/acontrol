<?php

namespace backend\assets\admin\form;

use yii\web\AssetBundle;

/**
 * @author Djava UI <support@djavaui.com>
 */
class PageLevelWizardAsset extends AssetBundle {

    public $sourcePath = '@bower';
    public $css = [
        'fontawesome/css/font-awesome.min.css',
        'animate.css/animate.min.css'
    ];
    public $js = [
        'jquery-validation/dist/jquery.validate.min.js',
        'twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js'
    ];

}
