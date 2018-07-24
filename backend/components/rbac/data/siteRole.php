<?php
use common\components\rbac\Item;
use common\components\rbac\Role;

use backend\components\rbac\rules\SiteOwnRule;
use backend\components\rbac\rules\TenantOwnRule;

return [
	
	// SiteController for Owner Site
	'SiteController.actionEditSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionViewSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionListSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionTenantCreateSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionTenantsSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionTenantGroupCreateSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionTenantGroupsSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionMeterChannelGroupCreateSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionMeterChannelGroupsSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionReportsSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionReportsSiteOwner' => ['type' => Item::TYPE_PERMISSION	],
	'SiteController.actionReportsBillSiteOwner' => ['type' => Item::TYPE_PERMISSION	],
	
	// SiteContactController for Owner Site
	'SiteContactController.actionCreateSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteContactController.actionEditSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteContactController.actionDeleteSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteContactController.actionViewSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteContactController.actionListSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	
	// SiteMeterController for Owner Site
	'SiteMeterController.actionCreateSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'SiteMeterController.actionEditSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteMeterController.actionViewSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteMeterController.actionListSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	
	// TenantController for Owner Site
	'TenantController.actionCreateSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionEditSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionDeleteSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionViewSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantController.actionListSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionReportsSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// TenantController for Owner Tenant
	'TenantController.actionEditTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantController.actionViewTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantController.actionListTenantOwner' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionReportsTenantOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// TenantContactController for Owner Site
	'TenantContactController.actionCreateSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionEditSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionDeleteSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionViewSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionListSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// TenantGroupController for Owner Site
	'TenantGroupController.actionEditSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'TenantGroupController.actionDeleteSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	
	// RuleSingleChannelController for Owner Site
	'RuleSingleChannelController.actionListSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// RuleGroupLoadController for Owner Site
	'RuleGroupLoadController.actionListSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// RuleFixedLoadController for Owner Site
	'RuleFixedLoadController.actionListSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// FormDependentController for Owner Site
	'FormDependentController.actionSiteToIssuesSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionSiteRatesSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionSiteMetersSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionUserSitesSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	
	// JsonSearchController for Owner Site
	'JsonSearchController.actionSiteTenantsSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'JsonSearchController.actionRateFixedPaymentSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'JsonSearchController.actionSiteFixedPaymentSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	'JsonSearchController.actionMeterChannelsSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	
	// AjaxGridViewController for Owner Site
	'AjaxGridViewController.actionTenantRulesSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	/**
	 * ReportController for owner site
	 */
//	'ReportController.actionCreateSiteOwner' => ['type' => Item::TYPE_PERMISSION],
	
	/**
	 * SiteManager for Owner Site
	 */
	'SiteViewManagerSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'SiteController.actionViewSiteOwner',
			'SiteController.actionListSiteOwner',
			'SiteController.actionTenantsSiteOwner',
			'SiteController.actionTenantGroupsSiteOwner',
			'SiteController.actionMeterChannelGroupsSiteOwner',
			'SiteController.actionReportsSiteOwner',
			'SiteController.actionReportsBillSiteOwner',
			
			'SiteContactController.actionListSiteOwner',
			'SiteContactController.actionViewSiteOwner',
			
			'SiteMeterController.actionListSiteOwner',
		],
	],
	'SiteManagerSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'SiteController.actionEditSiteOwner',
			
			'SiteContactController.actionCreateSiteOwner',
			'SiteContactController.actionEditSiteOwner',
			'SiteContactController.actionDeleteSiteOwner',
			
			'SiteController.actionTenantCreateSiteOwner',
			//'SiteController.actionTenantEditSiteOwner',
			//'SiteController.actionTenantDeleteSiteOwner',
			'SiteController.actionTenantGroupCreateSiteOwner',
			'SiteController.actionMeterChannelGroupCreateSiteOwner',
			//'SiteMeterController.actionCreateSiteOwner',
			//'SiteMeterController.actionEditSiteOwner',
		],
	],
	
	/**
	 * TenantManager for Owner Site
	 */
	'TenantViewManagerSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TenantController.actionViewSiteOwner',
			'TenantController.actionListSiteOwner',
			'TenantController.actionReportsSiteOwner',
			
			'TenantContactController.actionViewSiteOwner',
			'TenantContactController.actionListSiteOwner',
			
			'RuleSingleChannelController.actionListSiteOwner',
			'RuleGroupLoadController.actionListSiteOwner',
			'RuleFixedLoadController.actionListSiteOwner',
		],
	],
	'TenantManagerSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TenantController.actionCreateSiteOwner',
			'TenantController.actionEditSiteOwner',
			'TenantController.actionDeleteSiteOwner',

			'TenantContactController.actionCreateSiteOwner',
			'TenantContactController.actionEditSiteOwner',
			'TenantContactController.actionDeleteSiteOwner',
			
			'FormDependentController.actionUserSitesSiteOwner',
			'FormDependentController.actionSiteRatesSiteOwner',
			'FormDependentController.actionSiteToIssuesSiteOwner',
//			'FormDependentController.actionRuleGroupLoadMeterChannelsOwner',
			'FormDependentController.actionSiteMetersSiteOwner',
			
			'JsonSearchController.actionRateFixedPaymentSiteOwner',
			'JsonSearchController.actionSiteFixedPaymentSiteOwner',
			'JsonSearchController.actionMeterChannelsSiteOwner',
		],
	],
	
	/**
	 * TenantGroupManager for Owner Site
	 */
	'TenantGroupViewManagerSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [	
			'AjaxGridViewController.actionTenantRulesSiteOwner',
		],
	],
	'TenantGroupManagerSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TenantGroupController.actionEditSiteOwner',
			'TenantGroupController.actionDeleteSiteOwner',

			'JsonSearchController.actionSiteTenantsSiteOwner',
		],
	],

	/**
	 * ReportManager for owner site
	 */
	'ReportManagerSiteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
//			'ReportController.actionCreateSiteOwner',
		],
	],
	
	Role::ROLE_SITE => [
		'type' => Item::TYPE_ROLE,
		'children' => [
			'DashboardWidget.actionIndex',
			'SiteViewManagerSiteOwner',
			'SiteManagerSiteOwner',

			'TenantViewManagerSiteOwner',
			'TenantManagerSiteOwner',
			
			'TenantGroupViewManagerSiteOwner',
			'TenantGroupManagerSiteOwner',
			
			'DashboardWidget.SiteScheduleWidget',
			
			'ReportManagerSiteOwner',
		]
	],
];