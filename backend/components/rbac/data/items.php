<?php
use yii\helpers\ArrayHelper;
use common\components\rbac\Item;
use common\components\rbac\Role;

use backend\components\rbac\rules\TaskCommentDeleteOwnRule;
use backend\components\rbac\rules\ClientListOwnRule;
use backend\components\rbac\rules\SiteOwnRule;
use backend\components\rbac\rules\TenantOwnRule;

$siteRole = include( __DIR__ . '/siteRole.php');
$tenantRole = include( __DIR__ . '/tenantRole.php');
$clientRole = include( __DIR__ . '/clientRole.php');

return ArrayHelper::merge($siteRole, $tenantRole, $clientRole, [
	/*
	 * PERMISSIONS
	 */
	
	// DashboardWidgets
	'DashboardWidget.actionIndex' => ['type' => Item::TYPE_PERMISSION],
	'DashboardWidget.SearchWidget' => ['type' => Item::TYPE_PERMISSION],
	'DashboardWidget.SiteScheduleWidget' => ['type' => Item::TYPE_PERMISSION],
	'DashboardWidget.TaskWidget' => ['type' => Item::TYPE_PERMISSION],
	'DashboardWidget.TaskWidgetFilter' => ['type' => Item::TYPE_PERMISSION],

	// DashboardController
	'DashboardController.actionSearch' => ['type' => Item::TYPE_PERMISSION],
	'DashboardController.actionFlushCache' => ['type' => Item::TYPE_PERMISSION],

	// UserController
	'UserController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'UserController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'UserController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'UserController.actionView' => ['type' => Item::TYPE_PERMISSION],
	'UserController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// UserAlertNotificationController
	'UserAlertNotificationController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'UserAlertNotificationController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// LogController
	'LogController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// ClientController
	'ClientController.actionView' => ['type' => Item::TYPE_PERMISSION],
	'ClientController.actionList' => ['type' => Item::TYPE_PERMISSION],
	'ClientController.actionSiteCreate' => ['type' => Item::TYPE_PERMISSION],
	'ClientController.actionSites' => ['type' => Item::TYPE_PERMISSION],
	'ClientController.actionTenantCreate' => ['type' => Item::TYPE_PERMISSION],
	'ClientController.actionTenants' => ['type' => Item::TYPE_PERMISSION],

	// ClientContactController
	'ClientContactController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'ClientContactController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'ClientContactController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'ClientContactController.actionView' => ['type' => Item::TYPE_PERMISSION],
	'ClientContactController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// SiteController
	'SiteController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionView' => ['type' => Item::TYPE_PERMISSION	],
	'SiteController.actionList' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionTenantCreate' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionTenants' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionTenantGroupCreate' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionTenantGroups' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionMeterChannelGroupCreate' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionMeterChannelGroups' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionReports' => ['type' => Item::TYPE_PERMISSION],
	'SiteController.actionIssueReports' => ['type' => Item::TYPE_PERMISSION],
	
	// SiteContactController
	'SiteContactController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'SiteContactController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'SiteContactController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'SiteContactController.actionView' => ['type' => Item::TYPE_PERMISSION],
	'SiteContactController.actionList' => ['type' => Item::TYPE_PERMISSION],
	
	// SiteMeterController
	'SiteMeterController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'SiteMeterController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'SiteMeterController.actionView' => ['type' => Item::TYPE_PERMISSION],
	'SiteMeterController.actionList' => ['type' => Item::TYPE_PERMISSION],
	
	// TenantController
	'TenantController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionView' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionList' => ['type' => Item::TYPE_PERMISSION],
	'TenantController.actionReports' => ['type' => Item::TYPE_PERMISSION],
	
	// TenantContactController
	'TenantContactController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'TenantContactController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'TenantContactController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'TenantContactController.actionView' => ['type' => Item::TYPE_PERMISSION],
	'TenantContactController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// TenantGroupController
	'TenantGroupController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'TenantGroupController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'TenantGroupController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'TenantGroupController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// MeterController
	'MeterController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'MeterController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'MeterController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'MeterController.actionView' => ['type' => Item::TYPE_PERMISSION],
	'MeterController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// MeterChannelController
	'MeterChannelController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'MeterChannelController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// MeterChannelMultiplierController
	'MeterChannelMultiplierController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'MeterChannelMultiplierController.actionDelete' => ['type' => Item::TYPE_PERMISSION],

	// MeterChannelGroupController
	'MeterChannelGroupController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'MeterChannelGroupController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'MeterChannelGroupController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'MeterChannelGroupController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// MeterTypeController
	'MeterTypeController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'MeterTypeController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'MeterTypeController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'MeterTypeController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// MeterRawDataController
	'MeterRawDataController.actionAvg' => ['type' => Item::TYPE_PERMISSION],
	'MeterRawDataController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// RuleSingleChannelController
	'RuleSingleChannelController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'RuleSingleChannelController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'RuleSingleChannelController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'RuleSingleChannelController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// RuleGroupLoadController
	'RuleGroupLoadController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'RuleGroupLoadController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'RuleGroupLoadController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'RuleGroupLoadController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// RuleFixedLoadController
	'RuleFixedLoadController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'RuleFixedLoadController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'RuleFixedLoadController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'RuleFixedLoadController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// RateController
	'RateController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'RateController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'RateController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'RateController.actionList' => ['type' => Item::TYPE_PERMISSION],

    //Holiday
    'HolidayController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
    'HolidayController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
    'HolidayController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
    'HolidayController.actionList' => ['type' => Item::TYPE_PERMISSION],


    // RateTypeController
	'RateTypeController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'RateTypeController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'RateTypeController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'RateTypeController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// ReportController
	'ReportController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'ReportController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'ReportController.actionList' => ['type' => Item::TYPE_PERMISSION],
	'ReportController.actionPublish' => ['type' => Item::TYPE_PERMISSION],
	'ReportController.actionUnpublish' => ['type' => Item::TYPE_PERMISSION],
	'ReportController.actionToggleLanguage' => ['type' => Item::TYPE_PERMISSION],
	
	// VatController
	'VatController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'VatController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'VatController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'VatController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// ApiKeyController
	'ApiKeyController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'ApiKeyController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'ApiKeyController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'ApiKeyController.actionList' => ['type' => Item::TYPE_PERMISSION],

	// TaskController
	'TaskController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'TaskController.actionEdit' => ['type' => Item::TYPE_PERMISSION],
	'TaskController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'TaskController.actionView' => ['type' => Item::TYPE_PERMISSION],
	'TaskController.actionList' => ['type' => Item::TYPE_PERMISSION],
	'TaskController.actionToggleAssignee' => ['type' => Item::TYPE_PERMISSION],

	// TaskCommentController
	'TaskCommentController.actionCreate' => ['type' => Item::TYPE_PERMISSION],
	'TaskCommentController.actionDelete' => ['type' => Item::TYPE_PERMISSION],
	'TaskCommentController.actionDeleteOwn' => [
		'type' => Item::TYPE_PERMISSION,
		'ruleName' => (new TaskCommentDeleteOwnRule)->name,
	],

	// FormDependentController
	'FormDependentController.actionRoleUsers' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionRuleSingleChannelMeterChannels' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionRuleGroupLoadMeterChannels' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionUserSites' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionSiteRates' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionSiteContacts' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionSiteToIssues' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionSiteMeters' => ['type' => Item::TYPE_PERMISSION],
	'FormDependentController.actionSiteMeterChannels' => ['type' => Item::TYPE_PERMISSION],
			
	// JsonSearchController
	'JsonSearchController.actionMeterChannels' => ['type' => Item::TYPE_PERMISSION],
	'JsonSearchController.actionMeterChannelInfo' => ['type' => Item::TYPE_PERMISSION],
	'JsonSearchController.actionRateFixedPayment' => ['type' => Item::TYPE_PERMISSION],
	'JsonSearchController.actionSiteFixedPayment' => ['type' => Item::TYPE_PERMISSION],
	'JsonSearchController.actionSiteTenants' => ['type' => Item::TYPE_PERMISSION],
	
	// AjaxGridViewController
	'AjaxGridViewController.actionTenantRules' => ['type' => Item::TYPE_PERMISSION],

	/**
	 * UserManager
	 */
	'UserViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'UserController.actionView',
			'UserController.actionList',

			'UserAlertNotificationController.actionList',
		],
	],
	'UserManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'UserViewManager',

			'UserController.actionCreate',
			'UserController.actionEdit',
			'UserController.actionDelete',
			
			'UserAlertNotificationController.actionDelete',
		],
	],

	/**
	 * LogManager
	 */
	'LogViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'LogController.actionList',
		],
	],
	'LogManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'LogViewManager',
		],
	],
	
	/**
	 * ClientManager
	 */
	'ClientViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'ClientController.actionView',
			'ClientController.actionList',
			'ClientController.actionSites',
			'ClientController.actionTenants',

			'ClientContactController.actionView',
			'ClientContactController.actionList',
		],
	],
	'ClientManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'ClientViewManager',
				
			'ClientController.actionSiteCreate',
			'ClientController.actionTenantCreate',

			'ClientContactController.actionCreate',
			'ClientContactController.actionEdit',
			'ClientContactController.actionDelete',

			'JsonSearchController.actionRateFixedPayment',
			'JsonSearchController.actionSiteFixedPayment',
		],
	],
	
	/**
	 * SiteManager
	 */
	'SiteViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'SiteController.actionView',
			'SiteController.actionList',
			'SiteController.actionTenants',
			'SiteController.actionTenantGroups',
			'SiteController.actionMeterChannelGroups',
			'SiteController.actionReports',
	
			'SiteContactController.actionView',
			'SiteContactController.actionList',

			'SiteMeterController.actionList',

			'AjaxGridViewController.actionTenantRules',
		],
	],
	'SiteManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'SiteViewManager',

			'SiteController.actionCreate',
			'SiteController.actionEdit',
			'SiteController.actionDelete',
			'SiteController.actionTenantCreate',
			'SiteController.actionTenantGroupCreate',
			'SiteController.actionMeterChannelGroupCreate',
			'SiteController.actionIssueReports',
	
			'SiteContactController.actionCreate',
			'SiteContactController.actionEdit',
			'SiteContactController.actionDelete',

			'SiteMeterController.actionCreate',
			'SiteMeterController.actionEdit',

			'JsonSearchController.actionRateFixedPayment',
			'JsonSearchController.actionSiteFixedPayment',

			'FormDependentController.actionSiteMeters',
			'FormDependentController.actionSiteMeterChannels',
		],
	],
	
	/**
	 * TenantManager
	 */
	'TenantViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [			
			'TenantController.actionView',
			'TenantController.actionList',
			'TenantController.actionReports',

			'TenantContactController.actionView',
			'TenantContactController.actionList',

			'RuleSingleChannelController.actionList',

			'RuleGroupLoadController.actionList',

			'RuleFixedLoadController.actionList',
		],
	],
	'TenantManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			
			'TenantViewManager',

			'TenantController.actionCreate',
			'TenantController.actionEdit',
			'TenantController.actionDelete',

			'TenantContactController.actionCreate',
			'TenantContactController.actionEdit',
			'TenantContactController.actionDelete',

			'RuleSingleChannelController.actionCreate',
			'RuleSingleChannelController.actionEdit',
			'RuleSingleChannelController.actionDelete',

			'RuleGroupLoadController.actionCreate',
			'RuleGroupLoadController.actionEdit',
			'RuleGroupLoadController.actionDelete',

			'RuleFixedLoadController.actionCreate',
			'RuleFixedLoadController.actionEdit',
			'RuleFixedLoadController.actionDelete',

			'ReportTenantBillController.actionCreate',
			'ReportTenantBillController.actionDelete',

			'FormDependentController.actionUserSites',
			'FormDependentController.actionSiteRates',
			'FormDependentController.actionSiteToIssues',
			'FormDependentController.actionRuleGroupLoadMeterChannels',

			'JsonSearchController.actionRateFixedPayment',
			'JsonSearchController.actionSiteFixedPayment',
			'JsonSearchController.actionSiteTenants',
			'JsonSearchController.actionMeterChannelInfo',
		],
	],

	/**
	 * TenantGroupManager
	 */
	'TenantGroupViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TenantGroupController.actionList',
			
			'AjaxGridViewController.actionTenantRules',
		],
	],
	'TenantGroupManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TenantGroupViewManager',

			'TenantGroupController.actionCreate',
			'TenantGroupController.actionEdit',
			'TenantGroupController.actionDelete',

			'JsonSearchController.actionSiteTenants',
		],
	],

	/**
	 * MeterManager
	 */
	'MeterViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'MeterController.actionView',
			'MeterController.actionList',

			'MeterChannelController.actionList',
		],
	],
	'MeterManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'MeterViewManager',

			'MeterController.actionCreate',
			'MeterController.actionEdit',
			'MeterController.actionDelete',

			'MeterChannelController.actionEdit',
			'MeterChannelController.actionDelete',

			'MeterChannelMultiplierController.actionEdit',
			'MeterChannelMultiplierController.actionDelete',
			
			'MeterRawDataController.actionAvg',
			'MeterRawDataController.actionList',
			
			'FormDependentController.actionRuleSingleChannelMeterChannels',

			'JsonSearchController.actionMeterChannels',
		],
	],

	/**
	 * MeterTypeManager
	 */
	'MeterTypeViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'MeterTypeController.actionList',
		],
	],
	'MeterTypeManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'MeterTypeViewManager',

			'MeterTypeController.actionCreate',
			'MeterTypeController.actionEdit',
			'MeterTypeController.actionDelete',
		],
	],

	/**
	 * MeterChannelGroupManager
	 */
	'MeterChannelGroupViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'MeterChannelGroupController.actionList',
		],
	],
	'MeterChannelGroupManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'MeterChannelGroupViewManager',

			'MeterChannelGroupController.actionCreate',
			'MeterChannelGroupController.actionEdit',
			'MeterChannelGroupController.actionDelete',

			'FormDependentController.actionUserSites',
			'JsonSearchController.actionMeterChannels',
		],
	],

	/**
	 * VatManager
	 */
	'VatViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'VatController.actionList',
		],
	],
	'VatManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'VatViewManager',

			'VatController.actionCreate',
			'VatController.actionEdit',
			'VatController.actionDelete',
		],
	],

	/**
	 * RateManager
	 */
	'RateViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'RateController.actionList',

			'RateTypeController.actionList',
		],
	],
	'RateManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'RateViewManager',

			'RateController.actionCreate',
			'RateController.actionEdit',
			'RateController.actionDelete',

			'RateTypeController.actionCreate',
			'RateTypeController.actionEdit',
			'RateTypeController.actionDelete',
		],
	],

    'HolidayViewManager' => [
        'type' => Item::TYPE_PERMISSION,
        'children' => [
            'HolidayController.actionList',
        ],
    ],
    'HolidayManager' => [
        'type' => Item::TYPE_PERMISSION,
        'children' => [
            'HolidayViewManager',

            'HolidayController.actionCreate',
            'HolidayController.actionEdit',
            'HolidayController.actionDelete',
        ],
    ],

	/**
	 * ApiKeyManager
	 */
	'ApiKeyViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'ApiKeyController.actionList',
		],
	],
	'ApiKeyManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'ApiKeyViewManager',

			'ApiKeyController.actionCreate',
			'ApiKeyController.actionEdit',
			'ApiKeyController.actionDelete',
		],
	],

	/**
	 * ReportManager
	 */
	'ReportManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'ReportController.actionCreate',
			'ReportController.actionDelete',
			'ReportController.actionList',
			'ReportController.actionPublish',
			'ReportController.actionUnpublish',
			'ReportController.actionToggleLanguage',
		],
	],

	/**
	 * TaskManager
	 */
	'TaskViewManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TaskController.actionView',
			'TaskController.actionList',

			'FormDependentController.actionSiteContacts',
			'FormDependentController.actionRoleUsers',
		],
	],
	'TaskManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TaskViewManager',

			'TaskController.actionCreate',
			'TaskController.actionEdit',
			'TaskController.actionDelete',
			'TaskController.actionToggleAssignee',
		],
	],

	/**
	 * TaskCommentManager
	 */
	'TaskCommentOwnManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TaskCommentController.actionCreate',
			'TaskCommentController.actionDeleteOwn',
		],
	],
	'TaskCommentManager' => [
		'type' => Item::TYPE_PERMISSION,
		'children' => [
			'TaskCommentViewManager',

			'TaskCommentController.actionDelete',
		],
	],

	/*
	 * ROLES
	 */
	Role::ROLE_TECHNICIAN => [
		'type' => Item::TYPE_ROLE,
		'children' => [
			'DashboardWidget.actionIndex',
			'DashboardWidget.SearchWidget',
			'DashboardController.actionSearch',
			'DashboardWidget.TaskWidget',
			
			'TaskViewManager',
			'TaskCommentOwnManager',
			'ClientViewManager',
			'SiteViewManager',
			'TenantViewManager',
			'MeterViewManager',
			'MeterTypeViewManager',
            'HolidayViewManager',
		],
	],
	
	Role::ROLE_ADMIN => [
		'type' => Item::TYPE_ROLE,
		'children' => [
			Role::ROLE_TECHNICIAN,
			Role::ROLE_CLIENT,
			Role::ROLE_TENANT,
			Role::ROLE_SITE,

			'DashboardWidget.actionIndex',
			'DashboardWidget.SiteScheduleWidget',
			'DashboardWidget.TaskWidgetFilter',
			'DashboardController.actionFlushCache',
			
			'UserManager',
			'LogManager',
			'ClientManager',
			'SiteManager',
			'TenantManager',
			'TenantGroupManager',
			'MeterManager',
			'MeterTypeManager',
			'MeterChannelGroupManager',
			'RateManager',
			'VatManager',
			'ApiKeyManager',
			'TaskManager',
			'TaskCommentManager',
			'HolidayManager',

			'ReportManager',
		],
	],
]);
