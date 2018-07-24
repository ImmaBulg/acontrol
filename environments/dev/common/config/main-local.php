<?php
return [
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=database_name',
			'username' => 'root',
			'password' => '',
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
