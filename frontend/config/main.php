<?php

$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-frontend',
	'basePath' => dirname(__DIR__),
	'defaultRoute' => 'dashboard/index',
	'controllerNamespace' => 'frontend\controllers',
	'bootstrap' => ['log', 'debug'],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
        ],
    ],
	'components' => [
		'user' => [
			'class' => 'common\components\User',
			'identityClass' => 'frontend\models\User',
			'enableAutoLogin' => true,
			'loginUrl' => ['/user-auth/login'],
			'idParam' => '__idFrontend',
			'authTimeoutParam' => '__expireFrontend',
			'absoluteAuthTimeoutParam' => '__absoluteExpireFrontend',
			'returnUrlParam' => '__returnUrlFrontend',
			'identityCookie' => ['name' => '_identityFrontend', 'httpOnly' => true],
		],
		'authManager' => [
			'itemFile' => '@app/components/rbac/data/items.php',
			'assignmentFile' => '@app/components/rbac/data/assignments.php',
			'ruleFile' => '@app/components/rbac/data/rules.php',
		],
		'urlManager' => [
			'class' => 'common\components\UrlManager',
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => require(__DIR__ . '/rules.php'),
		],
		'assetManager' => [
			'class' => 'yii\web\AssetManager',
			'appendTimestamp' => true,
			'bundles' => [
				'yii\web\JqueryAsset' => [
					'sourcePath' => null,
					'basePath' => '@webroot',
					'baseUrl' => '@web',
					'js' => [
						'js/jquery-2.1.3.min.js',
						'js/jquery-migrate-1.1.1.js',
                        'js/realtime.js',
					],
				],
				'yii\bootstrap\BootstrapAsset' => [
					'sourcePath' => null,
					'basePath' => '@webroot',
					'baseUrl' => '@web',
					'css' => ['css/bootstrap.css'],
				],
			],
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
			'errorAction' => 'dashboard/error',
		],
	],
	'params' => $params,
	'bootstrap' => [
		'userBootstrap' => 'frontend\components\UserBootstrap',
	],
];
