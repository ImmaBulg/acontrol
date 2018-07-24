<?php
return [
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=10.1.1.14;dbname=akokorev_qlc_prod',
			'username' => 'qlc',
			'password' => '12345',
			'charset' => 'utf8',
		],
		'urlManagerStatic' => [
			'baseUrl' => 'http://qlc.static.dev.entagy.com/',
		],
	],
];
