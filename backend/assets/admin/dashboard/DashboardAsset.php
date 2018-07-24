<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets\admin\dashboard;

use yii\web\AssetBundle;

/**
 * @author Djava UI <support@djavaui.com>
 */
class DashboardAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
	
   
    public $css = [
//        'admin/css/reset.css',
//        'admin/css/layout.css',
//        'admin/css/components.css',
//        'admin/css/plugins.css',
//        'admin/css/yii-custom.css',
//        'admin/css/themes/default.theme.css',
//        'admin/css/custom.css'
    ];
    public $js = [
        'admin/js/pages/blankon.dashboard.js',
       
    ];
    public $depends = [
//        'backend\assets\admin\CoreAsset',
        'backend\assets\admin\dashboard\PageLevelAsset',
        'backend\assets\AppAsset'
    ];

}
