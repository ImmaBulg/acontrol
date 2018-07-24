<?php
return [
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=10.1.1.7;dbname=akokorev_qlc',
			'username' => 'akokorev',
			'password' => '8ace9df4',
			'charset' => 'utf8',
		],
		'dbLive' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'dblib:host=212.143.80.202:1433;dbname=Qlc_Prod',
			'username' => 'qlc_user',
			'password' => 'qlc_user',
			'charset' => 'utf8',
		],
		'urlManagerStatic' => [
			'baseUrl' => '/static',
		],
	],
];
