<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FlotAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'libs/flot/jquery.flot.js',
        'libs/flot/jquery.flot.spline.min.js',
        'libs/flot/jquery.flot.categories.js',
        'libs/flot/jquery.flot.tooltip.min.js',
        'libs/flot/jquery.flot.resize.js',
        'libs/flot/jquery.flot.pie.js',
    ];
}
