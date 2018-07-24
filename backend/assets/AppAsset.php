<?php
namespace backend\assets;

use common\components\View;

class AppAsset extends \yii\web\AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'admin/css/reset.css',
        'admin/css/layout.css',
        'admin/css/components.css',
        'admin/css/plugins.css',
        'admin/css/yii-custom.css',
        'admin/css/themes/blue.theme.css',
        'admin/css/custom.css',
    ];
    public $js = [
        'admin/js/apps.js',
    ];
    public $depends = [
        'backend\assets\SupportBowerAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'backend\assets\IE9Asset',
    ];
}
