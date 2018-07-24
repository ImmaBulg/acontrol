<?php

return [
	// DashboardController
//	'/' => 'dashboard/index',
	'search' => 'dashboard/search',

	// UserAuthController
	'/login' => 'user-auth/login',
	'/logout' => 'user-auth/logout',

	// UserController
	'users' => 'user/list',

	// ClientController
	'clients' => 'client/list',

	// ClientContactController
	'client/contacts' => 'client-contact/list',

	// SiteController
	'sites' => 'site/list',

	// SiteContactController
	'site/contacts' => 'site-contact/list',

	// SiteIpAddressController
	'site/ip-addresses' => 'site-ip-address/list',

	// TenantController
	'tenants' => 'tenant/list',

	// TenantContactController
	'tenant/contacts' => 'tenant-contact/list',

	// TenantGroupController
	'tenant-groups' => 'tenant-group/list',

	// MeterController
	'meters' => 'meter/list',

	// MeterChannelController
	'meter/channels' => 'meter-channel/list',

	// MeterChannelGroupController
	'meter-channel-groups' => 'meter-channel-group/list',

	// MeterRawDataController
	'meter-raw-data' => 'meter-raw-data/list',

	// MeterTypeController
	'meter-types' => 'meter-type/list',

	// RateController
	'rates' => 'rate/list',

	// RateController
	'rate-types' => 'rate-type/list',

	// VatController
	'vat' => 'vat/list',

	// ApiKeyController
	'api-keys' => 'api-key/list',

	// RuleSingleChannelController
	'rules/single-channel' => 'rule-single-channel/list',
	'rule/single-channel/<action>' => 'rule-single-channel/<action>',

	// RuleGroupLoadController
	'rules/group-load' => 'rule-group-load/list',
	'rule/group-load/<action>' => 'rule-group-load/<action>',

	// RuleFixedLoadController
	'rules/fixed-load' => 'rule-fixed-load/list',
	'rule/fixed-load/<action>' => 'rule-fixed-load/<action>',
];