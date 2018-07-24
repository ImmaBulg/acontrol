<?php
use common\components\rbac\Role;

$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-api',
	'name' => 'QLC',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'api\controllers',
	'bootstrap' => ['log'],
	'modules' => [],
	'components' => [
		'user' => [
			'class' => 'common\components\User',
		],
		'urlManager' => [
			'class' => 'common\components\UrlManager',
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => require(__DIR__ . '/rules.php'),
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'errorHandler' => [
			'class' => 'api\components\ErrorHandler',
			'errorAction' => 'default/error',
		],
	],
	'params' => $params,
];