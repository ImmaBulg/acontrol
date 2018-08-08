<?php
use common\components\rbac\Role;

$params = array_merge(
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return [
	'id' => 'app-common',
	'name' => 'QLC',
	'language' => 'en',
	'sourceLanguage' => 'en',
	'timeZone' => 'UTC',//'Asia/Jerusalem',
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'components' => [
		'cache' => [
			'class' => 'yii\caching\FileCache',
			'cachePath' => '@common/runtime/cache',
		],
		'formatter' => [
			'class' => 'common\components\i18n\Formatter',
		],
		'view' => [
			'class' => 'common\components\View',
		],
		'i18n' => [
			'translations' => [
				'common.*' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@common/messages',
				],
				'backend.*' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@backend/messages',
				],
				'frontend.*' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@frontend/messages',
				],
				'api.*' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@api/messages',
				],
				'console.*' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@console/messages',
				],
			],
		],
		'authManager' => [
			'class' => 'common\components\rbac\PhpManager',
			'defaultRoles' => array_keys(Role::getListDefaultRoles()),
		],
		'urlManagerBackend' => [
			'class' => 'common\components\UrlManagerBackend',
			'enablePrettyUrl' => true,
			'showScriptName' => false,
		],
		'urlManagerStatic' => [
			'class' => 'common\components\UrlManagerStatic',
			'enablePrettyUrl' => true,
			'showScriptName' => false,
		],
		'mailer' => [
			'class' => 'yii\swiftmailer\Mailer',
			'htmlLayout' => '@common/mail/layouts/html',
			'textLayout' => '@common/mail/layouts/text',
		],
	],
	'params' => $params,
	'bootstrap' => [
        'debug',
		[
			'class' => 'common\components\i18n\LanguageSelector',
		],
	],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
        ],
    ]
];
