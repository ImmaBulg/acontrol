<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use dezmont765\yii2bundle\views\MainView;
use yii\web\AssetBundle;

/**
 * Asset bundle for the Twitter bootstrap css files.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Select2Asset extends AssetBundle
{
    public $sourcePath = '@bower/';
    public $js = [
        'select2/select2.min.js',
    ];
    public $css = [
        'select2/select2.css',
        'select2-skins/select2-skins.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'backend\assets\Select2HelperAsset'
    ];

    public $jsOptions = [
        'position' => MainView::POS_END
    ];
}
