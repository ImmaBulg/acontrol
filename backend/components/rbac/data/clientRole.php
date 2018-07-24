<?php
use common\components\rbac\Item;
use common\components\rbac\Role;

use backend\components\rbac\rules\SiteOwnRule;
use backend\components\rbac\rules\TenantOwnRule;
use backend\components\rbac\rules\ClientOwnRule;

return [
	
	// ClientController for owner
	'ClientController.actionViewOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new ClientOwnRule)->name,
	],
	'ClientController.actionListOwner' => ['type' => Item::TYPE_PERMISSION],
	'ClientController.actionSiteCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new ClientOwnRule)->name,
	],
	'ClientController.actionSitesOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new ClientOwnRule())->name,
	],
	'ClientController.actionTenantCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new ClientOwnRule)->name,
	],
	'ClientController.actionTenantsOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new ClientOwnRule)->name,
	],
    
    // ClientContactController for Owner Client
    'ClientContactController.actionCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new ClientOwnRule)->name,
	],
    'ClientContactController.actionEditOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new ClientOwnRule)->name,
	],
    'ClientContactController.actionDeleteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new ClientOwnRule)->name,
	],
    'ClientContactController.actionViewOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new ClientOwnRule)->name,
	],
    'ClientContactController.actionListOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new ClientOwnRule)->name,
	],
    
	// SiteController for Owner Client
	'SiteController.actionCreateOwner' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionEditOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionDeleteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionViewOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionListOwner' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionTenantCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionTenantsOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionTenantGroupCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionTenantGroupsOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionMeterChannelGroupCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionMeterChannelGroupsOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionReportsOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteController.actionReportsBillOwner' => ['type' => Item::TYPE_PERMISSION	],

	
	// SiteContactController for Owner Client
	'SiteContactController.actionCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteContactController.actionEditOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteContactController.actionDeleteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteContactController.actionViewOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteContactController.actionListOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	
	// SiteMeterController for Owner Client
	'SiteMeterController.actionCreateOwner' => ['type' => Item::TYPE_PERMISSION],
	'SiteMeterController.actionEditOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteMeterController.actionViewOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'SiteMeterController.actionListOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	
	// TenantController for Owner Client
	'TenantController.actionCreateOwner' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionEditOwner' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionDeleteOwner' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionViewOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantController.actionListOwner' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionReportsOwner' => [
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
	
	// TenantContactController for Owner Client
	'TenantContactController.actionCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionEditOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionDeleteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionViewOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'TenantContactController.actionListOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// TenantGroupController for Owner Client
	'TenantGroupController.actionEditOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'TenantGroupController.actionDeleteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	
	// RuleSingleChannelController for Owner Client
	'RuleSingleChannelController.actionListOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'RuleSingleChannelController.actionCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'RuleSingleChannelController.actionEditOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'RuleSingleChannelController.actionDeleteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// RuleGroupLoadController for Owner Client
	'RuleGroupLoadController.actionListOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
    'RuleGroupLoadController.actionCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'RuleGroupLoadController.actionEditOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'RuleGroupLoadController.actionDeleteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// RuleFixedLoadController for Owner Client
	'RuleFixedLoadController.actionListOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
    'RuleFixedLoadController.actionCreateOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'RuleFixedLoadController.actionEditOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	'RuleFixedLoadController.actionDeleteOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	// FormDependentController for Owner Client
	'FormDependentController.actionSiteToIssuesOwner' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionSiteRatesOwner' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionSiteMetersOwner' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionUserSitesOwner' => ['type' => Item::TYPE_PERMISSION],
	
	// JsonSearchController for Owner Client
	'JsonSearchController.actionSiteTenantsOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new SiteOwnRule)->name,
	],
	'JsonSearchController.actionRateFixedPaymentOwner' => ['type' => Item::TYPE_PERMISSION],
	'JsonSearchController.actionSiteFixedPaymentOwner' => ['type' => Item::TYPE_PERMISSION],
	'JsonSearchController.actionMeterChannelsOwner' => ['type' => Item::TYPE_PERMISSION],
	
	// AjaxGridViewController for Owner Client
	'AjaxGridViewController.actionTenantRulesOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TenantOwnRule)->name,
	],
	
	/**
	 * ReportController for owner
	 */
	'ReportController.actionCreateOwner' => ['type' => Item::TYPE_PERMISSION],
	'ReportController.actionDeleteOwner' => ['type' => Item::TYPE_PERMISSION],
	'ReportController.actionListOwner' => ['type' => Item::TYPE_PERMISSION],
	'ReportController.actionPublishOwner' => ['type' => Item::TYPE_PERMISSION],
	'ReportController.actionUnpublishOwner' => ['type' => Item::TYPE_PERMISSION],
	'ReportController.actionToggleLanguageOwner' => ['type' => Item::TYPE_PERMISSION],
	
	/**
	 * ClientManager for owner
	 */
	'ClientViewManagerOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'ClientController.actionViewOwner',
			'ClientController.actionListOwner',
			'ClientController.actionSitesOwner',
			'ClientController.actionTenantsOwner',

			'ClientContactController.actionViewOwner',
			'ClientContactController.actionListOwner',
		],
	],
	'ClientManagerOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'ClientController.actionSiteCreateOwner',
			'ClientController.actionTenantCreateOwner',

			'ClientContactController.actionCreateOwner',
			'ClientContactController.actionEditOwner',
			'ClientContactController.actionDeleteOwner',

			//'JsonSearchController.actionRateFixedPaymentOwner',
			//'JsonSearchController.actionSiteFixedPaymentOwner',
		],
	],
	
	/**
	 * SiteManager for Owner Client
	 */
	'SiteViewManagerOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'SiteController.actionViewOwner',
			'SiteController.actionListOwner',
			'SiteController.actionTenantsOwner',
			'SiteController.actionTenantGroupsOwner',
			'SiteController.actionMeterChannelGroupsOwner',
			'SiteController.actionReportsOwner',
			'SiteController.actionReportsBillOwner',
			
			'SiteContactController.actionListOwner',
			'SiteContactController.actionViewOwner',
			
			'SiteMeterController.actionListOwner',
		],
	],
	'SiteManagerOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			
			'SiteContactController.actionCreateOwner',
			'SiteContactController.actionEditOwner',
			'SiteContactController.actionDeleteOwner',
			
			'SiteController.actionCreateOwner',
			'SiteController.actionEditOwner',
			'SiteController.actionDeleteOwner',
			'SiteController.actionTenantCreateOwner',
			//'SiteController.actionTenantEditOwner',
			//'SiteController.actionTenantDeleteOwner',
			'SiteController.actionTenantGroupCreateOwner',
			'SiteController.actionMeterChannelGroupCreateOwner',
			'SiteMeterController.actionCreateOwner',
			//'SiteMeterController.actionEditOwner',
		],
	],
	
	/**
	 * TenantManager for Owner Client
	 */
	'TenantViewManagerOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TenantController.actionViewOwner',
			'TenantController.actionListOwner',
			'TenantController.actionReportsOwner',
			
			'TenantContactController.actionViewOwner',
			'TenantContactController.actionListOwner',
			
			'RuleSingleChannelController.actionListOwner',
			'RuleGroupLoadController.actionListOwner',
			'RuleFixedLoadController.actionListOwner',
		],
	],
	'TenantManagerOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TenantController.actionCreateOwner',
			'TenantController.actionEditOwner',
			'TenantController.actionDeleteOwner',

			'TenantContactController.actionCreateOwner',
			'TenantContactController.actionEditOwner',
			'TenantContactController.actionDeleteOwner',
			
            'RuleSingleChannelController.actionCreateOwner',
            'RuleSingleChannelController.actionEditOwner',
            'RuleSingleChannelController.actionDeleteOwner',
            
            'RuleGroupLoadController.actionCreateOwner',
            'RuleGroupLoadController.actionEditOwner',
            'RuleGroupLoadController.actionDeleteOwner',
            
            'RuleFixedLoadController.actionCreateOwner',
            'RuleFixedLoadController.actionEditOwner',
            'RuleFixedLoadController.actionDeleteOwner',
            
			'FormDependentController.actionUserSitesOwner',
			'FormDependentController.actionSiteRatesOwner',
			'FormDependentController.actionSiteToIssuesOwner',
//			'FormDependentController.actionRuleGroupLoadMeterChannelsOwner',
			'FormDependentController.actionSiteMetersOwner',
			
			'JsonSearchController.actionRateFixedPaymentOwner',
			'JsonSearchController.actionSiteFixedPaymentOwner',
			'JsonSearchController.actionMeterChannelsOwner',
		],
	],
	
	/**
	 * TenantGroupManager for Owner Client
	 */
	'TenantGroupViewManagerOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [	
			'AjaxGridViewController.actionTenantRulesOwner',
		],
	],
	'TenantGroupManagerOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TenantGroupController.actionEditOwner',
			'TenantGroupController.actionDeleteOwner',

			'JsonSearchController.actionSiteTenantsOwner',
		],
	],
	
	
	
	/**
	 * ReportManager
	 */
	'ReportManagerOwner' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'ReportController.actionCreateOwner',
			'ReportController.actionDeleteOwner',
			'ReportController.actionListOwner',
			'ReportController.actionPublishOwner',
			'ReportController.actionUnpublishOwner',
			'ReportController.actionToggleLanguageOwner',
		],
	],
	
	
	
	
	
	
	Role::ROLE_CLIENT => [
		'type' => Item::TYPE_ROLE,
		'children' => [
			'DashboardWidget.actionIndex',
			'ClientViewManagerOwner',
			'ClientManagerOwner',
			
			'SiteViewManagerOwner',
			'SiteManagerOwner',

			'TenantViewManagerOwner',
			'TenantManagerOwner',
			
			'TenantGroupViewManagerOwner',
			'TenantGroupManagerOwner',
            
			'ReportManagerOwner',
            
			'DashboardWidget.SiteScheduleWidget',
			'FormDependentController.actionUserSites',
		]
	],
];