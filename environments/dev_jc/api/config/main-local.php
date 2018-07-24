<?php

$config = [
	'components' => [
		'request' => [
			'class' => '\yii\web\Request',
			'enableCookieValidation' => false,
			'parsers' => [
				'application/json' => 'yii\web\JsonParser',
			],
			'cookieValidationKey' => 'oik3gxyzW2HS5bFFdOc9oCd7peq5Wg6L',
		],
	],
];

if(YII_ENV_DEV)
{
	$config['modules']['swagger'] = 'api\modules\swagger\Module';
}

return $config;
