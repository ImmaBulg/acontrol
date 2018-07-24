<?php
use common\components\rbac\Item;
use common\components\rbac\Role;

use backend\components\rbac\rules\SiteOwnRule;
use backend\components\rbac\rules\TenantOwnRule;

return [
	
	// TenantContactController for Owner Tenant
	'TenantContactController.actionCreateTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionEditTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionDeleteTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionViewTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionListTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// RuleSingleChannelController for Tenant
	'RuleSingleChannelController.actionListTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// RuleGroupLoadController for Tenant
	'RuleGroupLoadController.actionListTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// RuleFixedLoadController for Tenant
	'RuleFixedLoadController.actionListTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// FormDependentController for Owner Tenant
	'FormDependentController.actionSiteToIssuesTenantOwner' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionSiteRatesTenantOwner' => ['type' => Item::TYPE_PERMISSION],
	
	// JsonSearchController for Owner Tenant
	'JsonSearchController.actionRateFixedPaymentTenantOwner' => ['type' => Item::TYPE_PERMISSION],
	'JsonSearchController.actionSiteFixedPaymentTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	
	/**
	 * TenantManager for Owner Tenant
	 */
	'TenantViewManagerTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TenantController.actionViewTenantOwner',
			'TenantController.actionListTenantOwner',
			'TenantController.actionReportsTenantOwner',
			
			'TenantContactController.actionViewTenantOwner',
			'TenantContactController.actionListTenantOwner',
			
			'RuleSingleChannelController.actionListTenantOwner',
			'RuleGroupLoadController.actionListTenantOwner',
			'RuleFixedLoadController.actionListTenantOwner',
		],
	],
	'TenantManagerTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TenantController.actionEditTenantOwner',
			
			'TenantContactController.actionCreateTenantOwner',
			'TenantContactController.actionEditTenantOwner',
			'TenantContactController.actionDeleteTenantOwner',
			
			'FormDependentController.actionSiteToIssuesTenantOwner',
			'FormDependentController.actionSiteRatesTenantOwner',
			
			'JsonSearchController.actionRateFixedPaymentTenantOwner',
			'JsonSearchController.actionSiteFixedPaymentTenantOwner',
		]
	],
	
    /**
	 * ReportManager
	 */
	'ReportManagerTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [],
	],
	
	Role::ROLE_TENANT => [
		'type' => Item::TYPE_ROLE,
		'children' => [
			'DashboardWidget.actionIndex',
			'TenantViewManagerTenantOwner',
			'TenantManagerTenantOwner',
            
            'ReportManagerTenantOwner',
		]
	],
];