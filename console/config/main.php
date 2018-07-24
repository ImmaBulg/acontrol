<?php

$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php')//,
	// require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-console',
	'language' => 'he',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'controllerNamespace' => 'console\controllers',
	'controllerMap' => [
		'migrate' => [
			'class' => 'console\controllers\MigrateController',
		],
	],
	'components' => [
		'log' => [
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['info','error', 'warning'],
				],
			],
		],
	],
	'params' => $params,
];
