<?php
use dezmont765\yii2bundle\components\SafeArray;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use common\helpers\Html;
use common\widgets\Dropdown;
use common\models\Report;
use backend\widgets\search\SearchWidget;
use backend\widgets\i18n\LanguageNav;
?>

    <div class="container-fluid">
<header id="header">
	<?php NavBar::begin([
        'brandLabel' => \yii\helpers\Html::img('/images/logo.png',['width' => '195']),
        'brandOptions' => ['class' => ['widget'=>'']],
        'renderInnerContainer' => false,
		'options' => [
			'id' => 'header-nav-bar',
			'class' => 'navbar navbar-toolbar',

		],
	]); ?>
		<?php
			echo Nav::widget([
				'options' => [
					'id' => 'settings-nav',
					'class' => 'navbar-nav navbar-right',
				],
				'items' => [
					[
						'label' => Yii::t('backend.view', 'Logout'),
						'url' => ['/user-auth/logout'],
						'linkOptions' => ['data-method' => 'post'],
					],
				],
			]);
			echo LanguageNav::widget([
				'options' => [
					'id' => 'language-nav',
					'class' => 'navbar-nav navbar-right',
				],
			]);

			if (Yii::$app->user->can('DashboardWidget.SearchWidget')) {
				echo SearchWidget::widget(['options' => ['class' => 'navbar-form navbar-right']]);
			}
        echo Nav::widget([
                             'options' => [
                                 'id' => 'main-nav',
                                 'class' => 'navbar-nav navbar-right',
                             ],
                             'items' => [
                                 [
                                     'label' => Yii::t('backend.view', 'Dashboard'),
                                     'url' => ['/dashboard/index'],
                                     'visible' => (Yii::$app->user->can('DashboardWidget.actionIndex')),
                                 ],
                                 [
                                     'label' => Yii::t('backend.view', 'Alerts/Helpdesk'),
                                     'url' =>['/task/list'],
                                     'visible' => (Yii::$app->user->can('TaskViewManager')),
                                 ],
                                 [
                                     'label' => Yii::t('backend.view', 'Clients'),
                                     'items' => [
                                         [
                                             'label' => Yii::t('backend.view', 'Clients'),
                                             'url' => ['/client/list'],
                                             'visible' => (Yii::$app->user->can('ClientViewManagerOwner')),
                                         ],
                                         [
                                             'label' => Yii::t('backend.view', 'Sites'),
                                             'url' =>['/site/list'],
                                             'visible' => (Yii::$app->user->can('SiteViewManagerOwner') || Yii::$app->user->can('SiteViewManagerSiteOwner')),
                                         ],
                                         [
                                             'label' => Yii::t('backend.view', 'Tenants'),
                                             'url' =>['/tenant/list'],
                                             'visible' => (Yii::$app->user->can('TenantViewManagerOwner') || Yii::$app->user->can('SiteViewManagerSiteOwner') || Yii::$app->user->can('TenantViewManagerTenantOwner')),
                                         ],
                                     ],
                                     'visible' => (Yii::$app->user->can('ClientViewManagerOwner') ||
                                                   Yii::$app->user->can('SiteViewManagerOwner') || Yii::$app->user->can('SiteViewManagerSiteOwner') ||
                                                   Yii::$app->user->can('TenantViewManagerOwner') || Yii::$app->user->can('TenantViewManagerSiteOwner') || Yii::$app->user->can('TenantViewManagerTenantOwner')),
                                 ],
                                 [
                                     'label' => Yii::t('backend.view', 'Settings'),
                                     'items' => [
                                         [
                                             'label' => Yii::t('backend.view', 'VAT'),
                                             'url' =>['/vat/list'],
                                             'visible' => (Yii::$app->user->can('VatViewManager')),
                                         ],
                                         /*[
                                             'label' => Yii::t('backend.view', 'Rates'),
                                             'url' => ['/rate/list'],
                                             'visible' => (Yii::$app->user->can('RateViewManager')),
                                         ],*/
                                         [
                                             'label' => Yii::t('backend.view', 'Air Rates'),
                                             'url' => ['/air-rates/list'],
                                             'visible' => (Yii::$app->user->can('RateViewManager')),
                                         ],
                                         [
                                             'label' => Yii::t('backend.view', 'Tenant groups'),
                                             'url' => ['/tenant-group/list'],
                                             'visible' => (Yii::$app->user->can('TenantGroupViewManager')),
                                         ],
                                         [
                                             'label' => Yii::t('backend.view', 'Channel groups'),
                                             'url' => ['/meter-channel-group/list'],
                                             'visible' => (Yii::$app->user->can('MeterChannelGroupViewManager')),
                                         ],
                                         [
                                             'label' => Yii::t('backend.view', 'API keys'),
                                             'url' =>['/api-key/list'],
                                             'visible' => (Yii::$app->user->can('ApiKeyManager')),
                                         ],
                                         [
                                             'label' => Yii::t('backend.view', 'Users'),
                                             'url' =>['/user/list'],
                                             'visible' => (Yii::$app->user->can('UserViewManager')),
                                         ],
                                         [
                                             'label' => Yii::t('backend.view', 'Soler costs'),
                                             'url' =>['/soler-cost/list'],
                                         ],
                                     ],
                                     'visible' => (Yii::$app->user->can('VatViewManager') || Yii::$app->user->can('RateViewManager') || Yii::$app->user->can('TenantGroupViewManager') || Yii::$app->user->can('MeterChannelGroupViewManager') || Yii::$app->user->can('ApiKeyManager') || Yii::$app->user->can('UserViewManager')),
                                 ],
                                 [
                                     'label' => Yii::t('backend.view', 'Meters'),
                                     'items' => [
                                         [
                                             'label' => Yii::t('backend.view', 'Meters'),
                                             'url' => ['/meter/list'],
                                             'visible' => (Yii::$app->user->can('MeterViewManager')),
                                         ],
                                         [
                                             'label' => Yii::t('backend.view', 'Meter types'),
                                             'url' => ['/meter-type/list'],
                                             'visible' => (Yii::$app->user->can('MeterTypeViewManager')),
                                         ],
                                     ],
                                     'visible' => (Yii::$app->user->can('MeterViewManager') || Yii::$app->user->can('MeterTypeViewManager')),
                                 ],
                                 [
                                     'label' => Yii::t('backend.view', 'Reports'),
                                     'items' => [
                                         [
                                             'label' => Yii::t('backend.view', 'Reports history'),
                                             'url' => ['/report/list', 'Report[level]' => Report::LEVEL_SITE],
                                             'visible' => (Yii::$app->user->can('ReportController.actionList') || Yii::$app->user->can('ReportController.actionListOwner')),
                                         ],
                                         [
                                             'label' => Yii::t('backend.view', 'Create new report'),
                                             'url' => ['/report/create'],
                                             'visible' => (Yii::$app->user->can('ReportController.actionCreate') || Yii::$app->user->can('ReportController.actionCreateOwner')),
                                         ],
                                     ],
                                     'visible' => (Yii::$app->user->can('ReportController.actionList') || Yii::$app->user->can('ReportController.actionCreate') || Yii::$app->user->can('ReportController.actionListOwner') || Yii::$app->user->can('ReportController.actionCreateOwner')),
                                 ],
                                 [
                                     'label' => Yii::t('backend.view', 'System log'),
                                     'url' =>['/log/list'],
                                     'visible' => (Yii::$app->user->can('LogViewManager')),
                                 ],
                             ],
                         ]);
		?>
	<?php NavBar::end(); ?>
</header>
    </div>
