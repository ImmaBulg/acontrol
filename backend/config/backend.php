<?php
use dezmont765\yii2bundle\views\MainView;

$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-backend',
	'basePath' => dirname(__DIR__),
	'defaultRoute' => 'dashboard/index',
	'controllerNamespace' => 'backend\controllers',
    'aliases' => [
        '@asset' => '@backend/assets'
    ],
//	'bootstrap' => ['log'],
    'bootstrap' => [
        'log',
        'userBootstrap' => 'backend\components\UserBootstrap',
    ],
	'modules' => [],
	'components' => [
        'view' => [
            'class' => MainView::className(),
        ],
		'user' => [
			'class' => 'common\components\User',
			'enableAutoLogin' => true,
			'loginUrl' => ['/user-auth/login'],
			'idParam' => '__idBackend',
			'authTimeoutParam' => '__expireBackend',
			'absoluteAuthTimeoutParam' => '__absoluteExpireBackend',
			'returnUrlParam' => '__returnUrlBackend',
			'identityCookie' => ['name' => '_identityBackend', 'httpOnly' => true],
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
                \yii\web\JqueryAsset::className() => [
                    'js' => [
                        "https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js",
                    ],
                    'jsOptions' =>
                        [
                            'position' => MainView::POS_HEAD,
                        ],
                ],
//				'yii\bootstrap\BootstrapAsset' => [
//					'sourcePath' => null,
//					'basePath' => '@webroot',
//					'baseUrl' => '@web',
//					'css' => ['css/bootstrap.css'],
//				],
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

];
