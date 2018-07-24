<?php
namespace api\modules\swagger\assets;

use Yii;
use yii\web\View;

class AppAsset extends \yii\web\AssetBundle
{
	public $sourcePath = '@api/modules/swagger/web';

	public $css = [
		'css/typography.css',
		'css/reset.css',
		'css/screen.css',
	];

	public $js = [
		'lib/shred.bundle.js',
		'lib/jquery-1.8.0.min.js',
		'lib/jquery.slideto.min.js',
		'lib/jquery.wiggle.min.js',
		'lib/jquery.ba-bbq.min.js',
		'lib/handlebars-2.0.0.js',
		'lib/underscore-min.js',
		'lib/backbone-min.js',
		'lib/swagger-client.js',
		'lib/swagger-ui.js',
		'lib/highlight.7.3.pack.js',
		'lib/marked.js',
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];
}
