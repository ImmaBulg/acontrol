<?php
namespace app\traits;

use dezmont765\yii2bundle\filters\LayoutFilter;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * User: Dezmont
 * Date: 26.02.2017
 * Time: 18:49
 * @method static place_top_buttons
 * @method static place_top_header
 * @method static place_top_breadcrumbs
 *
 * @method static listing()
 * @method static update()
 * @method static create()
 * @method static view()
 */
trait TTabbedLayout
{
    use TBaseLayout;


    /**
     * @param array $active
     * @return mixed
     */
    public static function layout(array $active = []) {
        static::$layout = 'tabbed-layout';
        $nav_bar = parent::layout(ArrayHelper::merge($active, self::getAdditionalActiveEntry()));
        $nav_bar[TTabbedLayout::place_top_header()] = self::getTopHeader($active);
        $nav_bar[TTabbedLayout::place_top_breadcrumbs()] = self::getTopBreadcrumbs($active);
        $nav_bar[TTabbedLayout::place_top_buttons()] = self::getTopButtons($active);
        return $nav_bar;
    }


    public static function getActiveMap() {
        return [
            'list' => [TTabbedLayout::listing()],
            'create' => [TTabbedLayout::create()],
            'update' => [TTabbedLayout::update()],
            'view' => [TTabbedLayout::view()],
        ];
    }



    public static function getTopHeader(array $active = []) {
        return [];
    }

    public static function getTopBreadcrumbs(array $active = []) {
        return [];
    }


    public static function getTopButtons(array $active = []) {
        return [];
    }


    public static function getAdditionalActiveEntry() {
        return [];
    }
}