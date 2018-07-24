<?php

return [
	// AlertController
	'POST /alerts' => 'alert/create',

	// SiteIpAddressController
	'GET /site/<site_id:\d+>/ip-address' => 'site-ip-address/list',
	'POST /site/<site_id:\d+>/ip-address' => 'site-ip-address/create',
	'GET /site/<site_id:\d+>/ip-address/<id:\d+>' => 'site-ip-address/view',
	'PUT /site/<site_id:\d+>/ip-address/<id:\d+>' => 'site-ip-address/update',
	'DELETE /site/<site_id:\d+>/ip-address/<id:\d+>' => 'site-ip-address/delete',

	// MeterController
	'POST /meters' => 'meter/create',

	// MeterRawDataController
	'GET /meter-raw-data' => 'meter-raw-data/list',
	'GET /meter-raw-data/latest-reading' => 'meter-raw-data/latest-reading',
	'POST /meter-raw-data' => 'meter-raw-data/create',

    'GET /air-meter-raw-data' => 'air-meter-raw-data/list',
	'GET /air-meter-raw-data/latest-reading' => 'air-meter-raw-data/latest-reading',
	'POST /air-meter-raw-data' => 'air-meter-raw-data/create',
];