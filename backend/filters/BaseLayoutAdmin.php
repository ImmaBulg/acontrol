<?php
namespace backend\filters;

use app\traits\TBaseLayout;
use dezmont765\yii2bundle\filters\LayoutFilter;
use Yii;
use yii\helpers\Url;

/**
 * Class BaseLayoutAdmin
 * @package backend\filters
 * @method static users()
 * @method static dashboard()
 * @method static alerts()
 * @method static clients()
 * @method static sites()
 * @method static tenants()
 * @method static logs()
 * @method static VAT()
 * @method static rates()
 * @method static tenant_groups()
 * @method static channel_groups()
 * @method static api_keys()
 * @method static meters()
 * @method static meter_types()
 * @method static reports()
 * @method static create_report()
 * @method static soler_costs()
 */
class BaseLayoutAdmin extends LayoutFilter
{
    use TBaseLayout;


    public static function getTopLeftMenu(array $active = []) {
        $tabs = [
            ['label' => 'Dashboard', 'url' => Url::to(['/']),
             'active' => self::getActive($active, BaseLayoutAdmin::dashboard()),
            ],
            ['label' => 'Alerts/Helpdesk', 'url' => Url::to(['task/list']), 'active' => BaseLayoutAdmin::alerts()],
            ['label' => 'Clients', 'items' => [
                ['label' => 'Clients', 'url' => Url::to(['clients']),
                 'active' => self::getActive($active, BaseLayoutAdmin::clients())],
                ['label' => 'Sites', 'url' => Url::to(['sites']),
                 'active' => self::getActive($active, BaseLayoutAdmin::sites())],
                ['label' => 'Tenants', 'url' => Url::to(['Tenants']),
                 'active' => self::getActive($active, BaseLayoutAdmin::tenants())],
            ]],
            ['label' => 'Settings', 'items' => [
                ['label' => 'VAT', 'url' => Url::to(['clients']),
                 'active' => self::getActive($active, BaseLayoutAdmin::VAT())],
                ['label' => 'Rates', 'url' => Url::to(['sites']),
                 'active' => self::getActive($active, BaseLayoutAdmin::rates())],
                ['label' => 'Tenant groups', 'url' => Url::to(['tenants']),
                 'active' => self::getActive($active, BaseLayoutAdmin::tenant_groups())],
                ['label' => 'Channel groups', 'url' => Url::to(['clients']),
                 'active' => self::getActive($active, BaseLayoutAdmin::channel_groups())],
                ['label' => 'Api keys', 'url' => Url::to(['clients']),
                 'active' => self::getActive($active, BaseLayoutAdmin::api_keys())],
                ['label' => 'Users', 'url' => Url::to(['clients']),
                 'active' => self::getActive($active, BaseLayoutAdmin::users())],
                ['label' => 'Soler costs', 'url' => Url::to(['clients']),
                 'active' => self::getActive($active, BaseLayoutAdmin::soler_costs())],
            ]],
            ['label' => 'Meters', 'items' => [
                ['label' => 'Meters', 'url' => Url::to(['clients']),
                 'active' => self::getActive($active, BaseLayoutAdmin::meters())],
                ['label' => 'Meter types', 'url' => Url::to(['sites']),
                 'active' => self::getActive($active, BaseLayoutAdmin::meter_types())],
            ]],
            ['label' => 'Reports', 'items' => [
                ['label' => 'Report history', 'url' => Url::to(['report/list', 'Report[level]' => 1]),
                 'active' => self::getActive($active, BaseLayoutAdmin::reports())],
                ['label' => 'Create new report', 'url' => Url::to(['report/create']),
                 'active' => self::getActive($active, BaseLayoutAdmin::create_report())],
            ]],
            ['label' => 'System log', 'url' => Url::to(['log/list']), 'active' => BaseLayoutAdmin::logs()],
        ];
        return $tabs;
    }


    public static function getTopRightMenu(array $active = []) {
        $tabs = [
            ['label' => 'My profile',
             'url' => Url::to(['user/update', 'id' => Yii::$app->user->id]),
             'active' => self::getActive($active, BaseLayoutAdmin::users())],
            ['class' => 'divider'],
            ['label' => 'Logout', 'url' => Url::to(['site/logout']), 'icon' => 'fa fa-sign-out'],
        ];
        return $tabs;
    }
}