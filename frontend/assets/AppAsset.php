<?php
namespace frontend\assets;

use common\components\View;

class AppAsset extends \yii\web\AssetBundle
{
	public $basePath = '@webroot';
	public $baseUrl = '@web';

	public $css = [
		'css/plugins/pickmeup.css',
		'theme/app.css',
	];

	public $js = [
		'js/plugins/pickmeup.js',
	];

	public $jsOptions = [
		'position' => View::POS_HEAD,
	];

	public $depends = [
		'yii\web\JqueryAsset',
		'yii\widgets\PjaxAsset',
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset',
	];
}
