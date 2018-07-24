<?php
return [
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=qlcbcontrol',
			'username' => 'qlcbcontrol',
			'password' => 'B5qhyN0X6wE',
			'charset' => 'utf8',
		],
		'urlManagerStatic' => [
			'baseUrl' => 'http://bcontrol.static.qlc.co.il/',
		],
	],
];
